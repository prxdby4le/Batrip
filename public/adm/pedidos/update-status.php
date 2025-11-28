<?php
/**
 * Atualiza status de pedido (endpoint simples para index-adm.php)
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';

// Verificar se é admin
require_admin();

// Verificar CSRF
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Token de segurança inválido.';
    header('Location: /adm/index-adm.php');
    exit;
}

$orderId = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? '';

if (!$orderId || !$status) {
    $_SESSION['error'] = 'Dados inválidos.';
    header('Location: /adm/index-adm.php');
    exit;
}

$allowedStatuses = ['pending', 'processing', 'production_complete', 'shipped', 'delivered', 'cancelled'];

if (!in_array($status, $allowedStatuses)) {
    $_SESSION['error'] = 'Status inválido.';
    header('Location: /adm/index-adm.php');
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    
    // Log
    error_log("Status do pedido #{$orderId} atualizado para '{$status}' por admin ID " . ($_SESSION['user_id'] ?? 'unknown'));
    
    $_SESSION['success'] = 'Status do pedido atualizado com sucesso!';
} catch (PDOException $e) {
    error_log("Erro ao atualizar status do pedido: " . $e->getMessage());
    $_SESSION['error'] = 'Erro ao atualizar status do pedido.';
}

header('Location: /adm/index-adm.php');
exit;

