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
    $stmtCheck = $pdo->prepare('SELECT id FROM product_images WHERE id = ? AND product_id = ?');
    $stmtCheck->execute([$imageId, $productId]);
    if (!$stmtCheck->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Imagem não encontrada']);
        exit;
    }
    
    // Remover flag is_primary de todas as imagens do produto
    $stmtUnset = $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?');
    $stmtUnset->execute([$productId]);
    
    // Definir a imagem escolhida como principal
    $stmtSet = $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ?');
    $stmtSet->execute([$imageId]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Imagem principal definida']);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erro ao definir imagem principal: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
}
