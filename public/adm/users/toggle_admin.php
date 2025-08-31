<?php
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) { http_response_code(400); echo 'CSRF token inválido.'; exit; }

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

// Evita que um admin remova seu próprio admin e fique sem admin
if ((int)$id === (int)$_SESSION['user_id']) {
  header('Location: index.php');
  exit;
}

$pdo->prepare('UPDATE users SET is_admin = 1 - IFNULL(is_admin,0) WHERE id = ?')->execute([$id]);
header('Location: index.php');
exit;
