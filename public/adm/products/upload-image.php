<?php
// Admin image upload endpoint
// Accepts multipart/form-data with files[]; returns JSON { success: bool, files: ["assets/img/uploads/xxx.jpg"], errors: [] }

// Harden errors
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/auth.php';
require_admin();
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!verify_csrf_token($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'CSRF inválido']);
    exit;
}

if (empty($_FILES['files'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
    exit;
}

$uploadBase = realpath(__DIR__ . '/../../assets/img/uploads');
if (!$uploadBase) {
    @mkdir(__DIR__ . '/../../assets/img/uploads', 0775, true);
    $uploadBase = realpath(__DIR__ . '/../../assets/img/uploads');
}
if (!$uploadBase) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Diretório de upload indisponível']);
    exit;
}

$allowedExt = ['jpg','jpeg','png','gif','webp','svg'];
$allowedMime = [
    'image/jpeg','image/png','image/gif','image/webp','image/svg+xml'
];
$maxSize = (defined('IMAGE_MAX_UPLOAD_MB') ? IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;
// Dimensões máximas aceitáveis para original (acima disso recusamos)
$maxWidthAccept = defined('IMAGE_ACCEPT_MAX_DIM') ? IMAGE_ACCEPT_MAX_DIM : 6000; 
$maxHeightAccept = $maxWidthAccept;
// Se a imagem for muito grande, faremos downscale para até este tamanho (se possível)
$downscaleMax = defined('IMAGE_DOWNSCALE_MAX') ? IMAGE_DOWNSCALE_MAX : 3000;
// Tamanhos derivados
$thumbSize = defined('IMAGE_THUMB_SIZE') ? IMAGE_THUMB_SIZE : 160; // 160x160 crop/center
$mediumMax = defined('IMAGE_MEDIUM_MAX') ? IMAGE_MEDIUM_MAX : 1024; // máx largura/altura
$largeMax = defined('IMAGE_LARGE_MAX') ? IMAGE_LARGE_MAX : 1600;
// Cota por produto (máximo de imagens extras)
$maxPerProduct = defined('IMAGES_PER_PRODUCT_MAX') ? IMAGES_PER_PRODUCT_MAX : 12;

// Produto alvo (se houver) para cotas
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
if ($productId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
        $stmt->execute([$productId]);
        $existingCount = (int)$stmt->fetchColumn();
        // Nota: não contamos as ainda não gravadas; validação suave por upload
    } catch (Throwable $e) {
        $existingCount = 0;
    }
}

$files = $_FILES['files'];
$out = [];
$errors = [];

$cnt = is_array($files['name']) ? count($files['name']) : 0;
for ($i = 0; $i < $cnt; $i++) {
    $name = $files['name'][$i];
    $tmp  = $files['tmp_name'][$i];
    $err  = $files['error'][$i];
    $size = (int)$files['size'][$i];

    if ($err !== UPLOAD_ERR_OK) { $errors[] = "$name: erro $err"; continue; }
    if ($size <= 0 || $size > $maxSize) { $errors[] = "$name: tamanho inválido"; continue; }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) { $errors[] = "$name: extensão não permitida"; continue; }

    // Validação por getimagesize (mais robusta)
    $imgInfo = @getimagesize($tmp);
    $mime = $imgInfo && isset($imgInfo['mime']) ? $imgInfo['mime'] : @mime_content_type($tmp);
    if ($mime && !in_array($mime, $allowedMime, true)) {
        // Allow svg with text/plain fallback sometimes
        if (!($ext === 'svg' && (strpos($mime, 'svg') !== false || $mime === 'text/plain'))) {
            $errors[] = "$name: tipo de arquivo não permitido ($mime)"; 
            continue;
        }
    }
    if ($ext !== 'svg' && is_array($imgInfo)) {
        $w = (int)$imgInfo[0]; $h = (int)$imgInfo[1];
        if ($w <= 0 || $h <= 0) { $errors[] = "$name: dimensões inválidas"; continue; }
        if ($w > $maxWidthAccept || $h > $maxHeightAccept) { $errors[] = "$name: dimensões muito grandes (máx ${maxWidthAccept}x${maxHeightAccept})"; continue; }
        // Verifica cota por produto (aproximação): bloqueia se atingido
        if ($productId > 0 && isset($existingCount)) {
            if (($existingCount + count($out)) >= $maxPerProduct) {
                $errors[] = "$name: cota de imagens por produto atingida";
                continue;
            }
        }
    }

    // Generate unique file name
    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower(pathinfo($name, PATHINFO_FILENAME)));
    $uniq = bin2hex(random_bytes(4));
    $finalName = $base ? ("{$base}-{$uniq}.{$ext}") : ("img-{$uniq}.{$ext}");

    $dest = $uploadBase . DIRECTORY_SEPARATOR . $finalName;
    if (!move_uploaded_file($tmp, $dest)) { $errors[] = "$name: falha ao mover arquivo"; continue; }

    // Redimensionar original se for muito grande (não para SVG)
    if ($ext !== 'svg' && is_array($imgInfo)) {
        $w = (int)$imgInfo[0]; $h = (int)$imgInfo[1];
        if (max($w,$h) > $downscaleMax) {
            try {
                resizeImageInPlace($dest, $ext, $downscaleMax);
            } catch (Throwable $e) {
                // falha no downscale não é fatal
            }
        }
    }

    // Build relative URL for frontend usage
    $rel = 'assets/img/uploads/' . $finalName;
    $out[] = $rel;

    // Gerar derivados (thumb e medium) quando aplicável
    if ($ext !== 'svg' && is_array($imgInfo)) {
        try {
            $baseNoExt = substr($dest, 0, strrpos($dest, '.'));
            $thumbPath = $baseNoExt . '--thumb.' . $ext;
            $mediumPath = $baseNoExt . '--medium.' . $ext;
            $largePath = $baseNoExt . '--large.' . $ext;
            createThumb($dest, $thumbPath, $ext, $thumbSize);
            createResized($dest, $mediumPath, $ext, $mediumMax);
            createResized($dest, $largePath, $ext, $largeMax);
        } catch (Throwable $e) {
            // gerar derivados é opcional
        }
    }
}

// Se houver product_id, persistir imediatamente em product_images
if (!empty($out) && $productId > 0) {
    try {
        // posição inicial após o maior existente
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(position), -1) FROM product_images WHERE product_id = ?');
        $stmt->execute([$productId]);
        $startPos = (int)$stmt->fetchColumn();
        $pos = $startPos + 1;

        // Verifica se já existe primária
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1');
        $stmt->execute([$productId]);
        $hasPrimary = ((int)$stmt->fetchColumn() > 0);

        $pdo->beginTransaction();
        $ins = $pdo->prepare('INSERT INTO product_images (product_id, url, position, is_primary) VALUES (?, ?, ?, ?)');
        foreach ($out as $idx => $url) {
            // manter cota (defensivo): parar se exceder
            if (isset($existingCount) && ($existingCount + $idx) >= $maxPerProduct) break;
            $isPrimary = 0;
            if (!$hasPrimary && $idx === 0 && ($startPos < 0)) { // primeira absoluta
                $isPrimary = 1;
                $hasPrimary = true;
            }
            $ins->execute([$productId, $url, $pos++, $isPrimary]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        // Não falhar o upload por erro de persistência; retornar em errors
        $errors[] = 'Erro ao registrar imagens no banco: ' . $e->getMessage();
    }
}

$success = !empty($out);
echo json_encode([
    'success' => $success,
    'files' => $out,
    'errors' => $errors
]);

// Helpers GD
function createImageFromExt($path, $ext) {
    $ext = strtolower($ext);
    if (!extension_loaded('gd')) return false;
    if ($ext === 'jpg' || $ext === 'jpeg') {
        if (!function_exists('imagecreatefromjpeg')) return false;
        return @imagecreatefromjpeg($path);
    }
    if ($ext === 'png') {
        if (!function_exists('imagecreatefrompng')) return false;
        return @imagecreatefrompng($path);
    }
    if ($ext === 'gif') {
        if (!function_exists('imagecreatefromgif')) return false;
        return @imagecreatefromgif($path);
    }
    if ($ext === 'webp') {
        if (function_exists('imagecreatefromwebp')) return @imagecreatefromwebp($path);
        return false;
    }
    return false;
}
function saveImageToExt($img, $path, $ext, $quality = 85) {
    $ext = strtolower($ext);
    if (!extension_loaded('gd')) return false;
    if ($ext === 'jpg' || $ext === 'jpeg') {
        if (!function_exists('imagejpeg')) return false;
        return @imagejpeg($img, $path, $quality);
    }
    if ($ext === 'png') {
        if (!function_exists('imagepng')) return false;
        return @imagepng($img, $path, 6);
    }
    if ($ext === 'gif') {
        if (!function_exists('imagegif')) return false;
        return @imagegif($img, $path);
    }
    if ($ext === 'webp') {
        if (function_exists('imagewebp')) return @imagewebp($img, $path, $quality);
        return false;
    }
    return false;
}
function createResized($srcPath, $dstPath, $ext, $max) {
    if (!extension_loaded('gd')) { @copy($srcPath, $dstPath); return; }
    $info = @getimagesize($srcPath); if (!$info) return;
    $w = (int)$info[0]; $h = (int)$info[1];
    $scale = min(1.0, $max / max($w,$h));
    $nw = max(1, (int)floor($w * $scale));
    $nh = max(1, (int)floor($h * $scale));
    if ($nw === $w && $nh === $h) { @copy($srcPath, $dstPath); return; }
    $src = createImageFromExt($srcPath, $ext); if (!$src) { @copy($srcPath, $dstPath); return; }
    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0,0,0,0, $nw,$nh, $w,$h);
    saveImageToExt($dst, $dstPath, $ext);
    imagedestroy($src); imagedestroy($dst);
}
function createThumb($srcPath, $dstPath, $ext, $size) {
    if (!extension_loaded('gd')) { @copy($srcPath, $dstPath); return; }
    $info = @getimagesize($srcPath); if (!$info) return;
    $w = (int)$info[0]; $h = (int)$info[1];
    $side = (int)$size;
    // Crop central quadrado
    $minSide = min($w, $h);
    $sx = max(0, (int)(($w - $minSide)/2));
    $sy = max(0, (int)(($h - $minSide)/2));
    $src = createImageFromExt($srcPath, $ext); if (!$src) { @copy($srcPath, $dstPath); return; }
    $dst = imagecreatetruecolor($side, $side);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0,0,$sx,$sy, $side,$side, $minSide,$minSide);
    saveImageToExt($dst, $dstPath, $ext);
    imagedestroy($src); imagedestroy($dst);
}
function resizeImageInPlace($path, $ext, $max) {
    if (!extension_loaded('gd')) return;
    $info = @getimagesize($path); if (!$info) return;
    $w = (int)$info[0]; $h = (int)$info[1];
    $scale = min(1.0, $max / max($w,$h));
    if ($scale >= 1.0) return; // nada a fazer
    $nw = max(1, (int)floor($w * $scale));
    $nh = max(1, (int)floor($h * $scale));
    $src = createImageFromExt($path, $ext); if (!$src) return;
    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0,0,0,0, $nw,$nh, $w,$h);
    saveImageToExt($dst, $path, $ext);
    imagedestroy($src); imagedestroy($dst);
}
