<?php
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
function send_json_error($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}
set_exception_handler(function($e) use ($isAjax) {
    if ($isAjax) {
        send_json_error($e->getMessage(), 500);
    } else {
        http_response_code(500);
        echo '<pre>Erro: ' . htmlspecialchars($e->getMessage()) . '</pre>';
        exit;
    }
});
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($isAjax) {
    if (!(error_reporting() & $errno)) return false;
    if ($isAjax) {
        send_json_error("$errstr in $errfile:$errline", 500);
    } else {
        http_response_code(500);
        echo '<pre>Erro: ' . htmlspecialchars($errstr) . "\nArquivo: $errfile:$errline" . '</pre>';
        exit;
    }
    return true;
});
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) send_json_error('Método não permitido', 405);
    header('Location: index.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    send_json_error('CSRF token inválido.', 400);
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
    send_json_error('Preencha todos os campos obrigatórios.', 400);
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
