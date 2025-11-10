<?php
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(400);
    echo 'CSRF token inválido.';
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$image = trim($_POST['image'] ?? '');
$sizes = trim($_POST['sizes'] ?? 'P,M,G,GG');
$sizeChartJson = (string)($_POST['size_chart'] ?? '');
$description = trim($_POST['description'] ?? '');
$active = (int)($_POST['active'] ?? 1);
// Imagens extras (uma por linha)
$imagesExtraRaw = (string)($_POST['images_extra'] ?? '');

// Se o campo 'image' não for fornecido no formulário, derive da primeira imagem extra
if ($image === '' && $imagesExtraRaw !== '') {
    $lines = preg_split('/\r\n|\r|\n/', $imagesExtraRaw);
    foreach ($lines as $line) {
        $candidate = trim((string)$line);
        if ($candidate !== '') { $image = $candidate; break; }
    }
}
// Enforce max images per product to avoid explosion
$maxPerProduct = 12;

if ($title === '' || $price <= 0 || $image === '') {
    header('Location: form.php?id=' . $id);
    exit;
}

// Detecta se a coluna size_chart existe para evitar erro em bancos antigos
$hasSizeChart = false;
try {
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $chk = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $chk->execute([$dbName, 'products', 'size_chart']);
    $hasSizeChart = ((int)$chk->fetchColumn() > 0);
} catch (Throwable $e) { $hasSizeChart = false; }

if ($id > 0) {
    if ($hasSizeChart) {
        $stmt = $pdo->prepare('UPDATE products SET title=?, description=?, price=?, image=?, sizes=?, size_chart=?, active=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$title, $description, $price, $image, $sizes, $sizeChartJson, $active, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE products SET title=?, description=?, price=?, image=?, sizes=?, active=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$title, $description, $price, $image, $sizes, $active, $id]);
    }
} else {
    if ($hasSizeChart) {
        $stmt = $pdo->prepare('INSERT INTO products (title, description, price, image, sizes, size_chart, active) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $price, $image, $sizes, $sizeChartJson, $active]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO products (title, description, price, image, sizes, active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $price, $image, $sizes, $active]);
    }
    $id = (int)$pdo->lastInsertId();
}

// Persistir imagens extras (substitui conjunto atual)
try {
    $pdo->beginTransaction();
    $del = $pdo->prepare('DELETE FROM product_images WHERE product_id = ?');
    $del->execute([$id]);

    $lines = preg_split('/\r\n|\r|\n/', $imagesExtraRaw);
    $pos = 0;
    $ins = $pdo->prepare('INSERT INTO product_images (product_id, url, position, is_primary) VALUES (?, ?, ?, ?)');
    foreach ($lines as $line) {
        $url = trim($line);
        if ($url === '') continue;
        if ($pos >= $maxPerProduct) break; // quota hard
        // Normaliza barras
        $url = str_replace('\\\\', '/', $url);
        $isPrimary = ($pos === 0) ? 1 : 0;
        $ins->execute([$id, $url, $pos, $isPrimary]);
        $pos++;
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log('Falha ao salvar imagens extras: ' . $e->getMessage());
}

header('Location: index.php');
exit;
