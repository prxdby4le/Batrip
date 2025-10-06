<?php
// Serve imagens do banco de dados (área administrativa)
require_once '../../../includes/db.php';

// Suporta dois modos: por ID do produto (pega a principal) ou por ID da imagem específica
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$imageId = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;

try {
    if ($imageId > 0) {
        // Buscar imagem específica pelo ID
        $stmt = $pdo->prepare('SELECT image, image_type FROM product_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $imageData = $stmt->fetch();
    } elseif ($productId > 0) {
        // Buscar imagem principal do produto
        $stmt = $pdo->prepare('SELECT image, image_type FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1');
        $stmt->execute([$productId]);
        $imageData = $stmt->fetch();
        
        // Se não tiver imagem principal, pega a primeira
        if (!$imageData) {
            $stmt = $pdo->prepare('SELECT image, image_type FROM product_images WHERE product_id = ? ORDER BY display_order ASC LIMIT 1');
            $stmt->execute([$productId]);
            $imageData = $stmt->fetch();
        }
    } else {
        http_response_code(400);
        exit('ID inválido');
    }
    
    if (!$imageData || empty($imageData['image'])) {
        // Retornar placeholder SVG em vez de erro 404
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=300'); // Cache menor para placeholder
        echo file_get_contents(__DIR__ . '/../../../assets/img/placeholder.svg');
        exit;
    }
    
    // Define o tipo de conteúdo
    $imageType = $imageData['image_type'] ?: 'image/jpeg';
    header('Content-Type: ' . $imageType);
    
    // Cache por 1 hora
    header('Cache-Control: public, max-age=3600');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    
    // Envia a imagem
    echo $imageData['image'];
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro ao buscar imagem: " . $e->getMessage());
    // Retornar placeholder SVG em vez de erro
    header('Content-Type: image/svg+xml');
    echo file_get_contents(__DIR__ . '/../../../assets/img/placeholder.svg');
    exit;
}
