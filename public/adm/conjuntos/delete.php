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
    // Obter imagem para excluir arquivo físico (opcional)
    $stmt = $pdo->prepare('SELECT image FROM sets WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image'])) {
        $path = realpath(__DIR__ . '/../../../' . ltrim($row['image'], '/'));
        if ($path && strpos($path, realpath(__DIR__ . '/../../../assets/img/sets')) === 0 && file_exists($path)) {
            @unlink($path);
        }
    }
    $stmt = $pdo->prepare('DELETE FROM sets WHERE id = ?');
    $stmt->execute([$id]);
} catch (PDOException $e) {
    error_log('Erro ao excluir conjunto: ' . $e->getMessage());
}

header('Location: /adm/conjuntos/index.php');
exit;
