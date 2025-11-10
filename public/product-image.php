<?php
// Serve a product image based on product ID by looking up the image path in the database.
// Falls back to a local placeholder if missing.

// Hardening: do not output notices/warnings
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$idx = isset($_GET['idx']) ? max(0, (int)$_GET['idx']) : null; // índice opcional da imagem na galeria
$size = isset($_GET['size']) ? strtolower(trim((string)$_GET['size'])) : null; // 'thumb' | 'medium' | 'large'
if ($id <= 0) {
    http_response_code(404);
    exit('Not Found');
}

// Base pública e fallback
$publicBase = realpath(__DIR__);
$rel = 'assets/img/placeholder.svg';

// 1) Tenta obter a lista de imagens extras da tabela product_images
$images = [];
try {
    $stmt = $pdo->prepare('SELECT url FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, position ASC, id ASC');
    $stmt->execute([$id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    // Ignora erro; passará para fallback do produto
}

// Se houver imagens extras, seleciona conforme idx; senão, usa campo products.image
if (!empty($images)) {
    $chosen = $images[0];
    if ($idx !== null && isset($images[$idx])) {
        $chosen = $images[$idx];
    }
    $pi = (string)$chosen;
    $pi = str_replace('\\', '/', $pi);
    if (strpos($pi, 'public/') === 0) { $pi = substr($pi, 7); }
    if (!filter_var($pi, FILTER_VALIDATE_URL)) {
        // Suporta variantes geradas para uploads: --thumb / --medium
        if (in_array($size, ['thumb','medium','large'], true) && strpos($pi, 'assets/img/uploads/') === 0) {
            $dot = strrpos($pi, '.');
            if ($dot !== false) {
                $candidate = substr($pi, 0, $dot) . '--' . $size . substr($pi, $dot);
                $rel = $candidate;
            } else {
                $rel = $pi;
            }
        } else if (strpos($pi, 'assets/') === 0 || strpos($pi, 'images/') === 0) {
            $rel = $pi;
        } else if (strpos($pi, '/') === false) {
            $rel = 'assets/img/' . $pi;
        } else {
            $rel = 'assets/img/' . basename($pi);
        }
    }
} else {
    // Fallback para imagem única do produto
    try {
        $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ? AND active = 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
    } catch (Throwable $e) {
        $row = false;
    }
    if ($row && !empty($row['image'])) {
        $pi = (string)$row['image'];
        $pi = str_replace('\\', '/', $pi);
        if (strpos($pi, 'public/') === 0) { $pi = substr($pi, 7); }
        if (!filter_var($pi, FILTER_VALIDATE_URL)) {
            if (in_array($size, ['thumb','medium','large'], true) && strpos($pi, 'assets/img/uploads/') === 0) {
                $dot = strrpos($pi, '.');
                if ($dot !== false) {
                    $candidate = substr($pi, 0, $dot) . '--' . $size . substr($pi, $dot);
                    $rel = $candidate;
                } else {
                    $rel = $pi;
                }
            } else if (strpos($pi, 'assets/') === 0 || strpos($pi, 'images/') === 0) {
                $rel = $pi;
            } else if (strpos($pi, '/') === false) {
                $rel = 'assets/img/' . $pi;
            } else {
                $rel = 'assets/img/' . basename($pi);
            }
        }
    }
}

$abs = realpath($publicBase . DIRECTORY_SEPARATOR . $rel);
if (!$abs || strpos($abs, $publicBase) !== 0 || !is_file($abs)) {
    // Final fallback
    $abs = realpath($publicBase . DIRECTORY_SEPARATOR . 'assets/img/placeholder.svg');
}

// Detect content type
$mime = 'application/octet-stream';
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$map = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'svg' => 'image/svg+xml',
    'bmp' => 'image/bmp'
];
if (isset($map[$ext])) {
    $mime = $map[$ext];
}

// Caching headers (cache 7 dias; ajuste conforme necessidade)
$expires = 60 * 60 * 24 * 7;
if (!headers_sent()) {
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=' . $expires);
}

// Output file
$fp = fopen($abs, 'rb');
if ($fp) {
    fpassthru($fp);
    fclose($fp);
} else {
    http_response_code(404);
    echo 'Not Found';
}
