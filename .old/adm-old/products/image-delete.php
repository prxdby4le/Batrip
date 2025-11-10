<?php
ob_start();
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    echo json_encode(['success' => false, 'message' => 'CSRF token inválido']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$imageId = (int)($_POST['image_id'] ?? 0);

if ($productId <= 0 || $imageId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Verificar se a imagem pertence ao produto
    $stmtCheck = $pdo->prepare('SELECT is_primary FROM product_images WHERE id = ? AND product_id = ?');
    $stmtCheck->execute([$imageId, $productId]);
    $image = $stmtCheck->fetch();
    
    if (!$image) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Imagem não encontrada']);
        exit;
    }
    
    $wasPrimary = (bool)$image['is_primary'];
    
    // Verificar se é a única imagem
    $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
    $stmtCount->execute([$productId]);
    $imageCount = (int)$stmtCount->fetchColumn();
    
    if ($imageCount <= 1) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'O produto deve ter ao menos uma imagem']);
        exit;
    }
    
    // Deletar a imagem
    $stmtDelete = $pdo->prepare('DELETE FROM product_images WHERE id = ?');
    $stmtDelete->execute([$imageId]);
    
    // Se era a imagem principal, definir a primeira restante como principal
    if ($wasPrimary) {
        $stmtSetNew = $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE product_id = ? ORDER BY display_order ASC LIMIT 1');
        $stmtSetNew->execute([$productId]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Imagem removida']);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erro ao remover imagem: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
}
