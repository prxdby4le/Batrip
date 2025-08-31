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
$description = trim($_POST['description'] ?? '');
$active = (int)($_POST['active'] ?? 1);

if ($title === '' || $price <= 0 || $image === '') {
    header('Location: form.php?id=' . $id);
    exit;
}

if ($id > 0) {
    $stmt = $pdo->prepare('UPDATE products SET title=?, description=?, price=?, image=?, sizes=?, active=?, updated_at=NOW() WHERE id=?');
    $stmt->execute([$title, $description, $price, $image, $sizes, $active, $id]);
} else {
    $stmt = $pdo->prepare('INSERT INTO products (title, description, price, image, sizes, active) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$title, $description, $price, $image, $sizes, $active]);
}

header('Location: index.php');
exit;
