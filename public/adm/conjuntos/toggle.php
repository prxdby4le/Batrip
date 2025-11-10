<?php
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
if (!$id) { header('Location: /adm/conjuntos/index.php'); exit; }

try {
    $stmt = $pdo->prepare('UPDATE sets SET active = IF(active=1, 0, 1), updated_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);
} catch (PDOException $e) {
    error_log('Erro ao alternar conjunto: ' . $e->getMessage());
}

header('Location: /adm/conjuntos/index.php');
exit;
