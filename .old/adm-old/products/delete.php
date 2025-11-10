<?php
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index-adm.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(400);
    echo 'CSRF token inválido.';
    exit;
}
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
}
header('Location: ../index-adm.php');
exit;
