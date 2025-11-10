<?php
// Salvar conjunto (create/update)
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /adm/conjuntos/index.php');
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    http_response_code(400);
    echo 'CSRF inválido';
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = trim((string)($_POST['title'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$price = (float)($_POST['price'] ?? 0);
$active = isset($_POST['active']) ? (int)$_POST['active'] : 1;

if ($title === '') {
    $_SESSION['error_message'] = 'Título é obrigatório';
    header('Location: ' . ($id ? '/adm/conjuntos/form.php?id='.$id : '/adm/conjuntos/form.php'));
    exit;
}

$imagePath = null;
// Upload de imagem simples
if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $info = @getimagesize($_FILES['image']['tmp_name']);
    if ($info && isset($allowed[$info['mime']])) {
        $ext = $allowed[$info['mime']];
        $dir = __DIR__ . '/../../../assets/img/sets';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $filename = 'set_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $target = $dir . '/' . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = '/assets/img/sets/' . $filename;
            // Gerar derivados (thumb/medium/large) se GD estiver disponível
            if (function_exists('imagecreatetruecolor')) {
                $sizes = [
                    'thumb' => 300,
                    'medium' => 800,
                    'large' => 1280,
                ];
                foreach ($sizes as $label => $max) {
                    $subdir = $dir . '/' . $label;
                    if (!is_dir($subdir)) { @mkdir($subdir, 0775, true); }
                    $dest = $subdir . '/' . $filename;
                    try {
                        if ($label === 'thumb') {
                            resize_image_square_crop($target, $dest, $max);
                        } else {
                            resize_image_constrain($target, $dest, $max, $max);
                        }
                    } catch (Throwable $e) { /* ignore */ }
                }
            }
        }
    }
}

try {
    if ($id) {
        if ($imagePath) {
            $stmt = $pdo->prepare('UPDATE sets SET title = ?, description = ?, price = ?, image = ?, active = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$title, $description, $price, $imagePath, $active, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE sets SET title = ?, description = ?, price = ?, active = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$title, $description, $price, $active, $id]);
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO sets (title, description, price, image, active) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $price, $imagePath ?? '', $active]);
        $id = (int)$pdo->lastInsertId();
    }
    // Persistir composição do conjunto (set_items)
    if ($id > 0) {
        // Limpar itens existentes
        $pdo->prepare('DELETE FROM set_items WHERE set_id = ?')->execute([$id]);
        $items = $_POST['items'] ?? [];
        if (is_array($items)) {
            $ins = $pdo->prepare('INSERT INTO set_items (set_id, product_id, quantity) VALUES (?, ?, ?)');
            foreach ($items as $productId => $data) {
                $checked = isset($data['checked']) && (string)$data['checked'] === '1';
                $qty = isset($data['qty']) ? (int)$data['qty'] : 0;
                $productId = (int)$productId;
                if ($checked && $productId > 0 && $qty > 0) {
                    $ins->execute([$id, $productId, $qty]);
                }
            }
        }
    }
    $_SESSION['success_message'] = 'Conjunto salvo com sucesso!';
    header('Location: /adm/conjuntos/index.php');
    exit;
} catch (PDOException $e) {
    error_log('Erro ao salvar conjunto: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Erro ao salvar conjunto.';
    header('Location: ' . ($id ? '/adm/conjuntos/form.php?id='.$id : '/adm/conjuntos/form.php'));
    exit;
}

// Utilitário simples para redimensionar mantendo proporção
function resize_image_constrain($srcPath, $dstPath, $maxW, $maxH) {
    $info = getimagesize($srcPath);
    if (!$info) return;
    list($w, $h) = $info;
    $mime = $info['mime'];

    $ratio = min($maxW / $w, $maxH / $h, 1.0);
    $newW = (int)round($w * $ratio);
    $newH = (int)round($h * $ratio);

    if ($mime === 'image/jpeg') { $src = imagecreatefromjpeg($srcPath); }
    elseif ($mime === 'image/png') { $src = imagecreatefrompng($srcPath); }
    elseif ($mime === 'image/webp') { $src = imagecreatefromwebp($srcPath); }
    else { return; }

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0,0,0,0, $newW,$newH,$w,$h);

    $ext = strtolower(pathinfo($dstPath, PATHINFO_EXTENSION));
    if ($ext === 'png') { imagepng($dst, $dstPath, 6); }
    elseif ($ext === 'webp') { imagewebp($dst, $dstPath, 85); }
    else { imagejpeg($dst, $dstPath, 85); }

    imagedestroy($src); imagedestroy($dst);
}

// Redimensiona com crop quadrado central
function resize_image_square_crop($srcPath, $dstPath, $size) {
    $info = getimagesize($srcPath);
    if (!$info) return;
    list($w, $h) = $info;
    $mime = $info['mime'];

    if ($mime === 'image/jpeg') { $src = imagecreatefromjpeg($srcPath); }
    elseif ($mime === 'image/png') { $src = imagecreatefrompng($srcPath); }
    elseif ($mime === 'image/webp') { $src = imagecreatefromwebp($srcPath); }
    else { return; }

    // Determinar quadrado central
    $side = min($w, $h);
    $srcX = (int)(($w - $side) / 2);
    $srcY = (int)(($h - $side) / 2);

    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $side, $side);

    $ext = strtolower(pathinfo($dstPath, PATHINFO_EXTENSION));
    if ($ext === 'png') { imagepng($dst, $dstPath, 6); }
    elseif ($ext === 'webp') { imagewebp($dst, $dstPath, 85); }
    else { imagejpeg($dst, $dstPath, 85); }

    imagedestroy($src); imagedestroy($dst);
}
