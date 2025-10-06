<?php
// Serve imagens dos produtos (área pública)
require_once '../includes/db.php';

// Suporta buscar por ID do produto ou ID específico da imagem
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$imageId = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
$getAll = isset($_GET['all']) && $_GET['all'] === '1'; // Para retornar todas as imagens

try {
    if ($getAll && $productId > 0) {
        // Retornar JSON com todas as imagens do produto
        header('Content-Type: application/json');
        $stmt = $pdo->prepare('
            SELECT pi.id, pi.display_order, pi.is_primary 
            FROM product_images pi
            JOIN products p ON pi.product_id = p.id
            WHERE pi.product_id = ? AND p.active = 1
            ORDER BY pi.is_primary DESC, pi.display_order ASC
        ');
        $stmt->execute([$productId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar URLs
        foreach ($images as &$img) {
            $img['url'] = 'product-image.php?img_id=' . $img['id'];
        }
        
        echo json_encode(['success' => true, 'images' => $images]);
        exit;
    }
    
    if ($imageId > 0) {
        // Buscar imagem específica (verificar se produto está ativo)
        $stmt = $pdo->prepare('
            SELECT pi.image, pi.image_type 
            FROM product_images pi
            JOIN products p ON pi.product_id = p.id
            WHERE pi.id = ? AND p.active = 1
        ');
        $stmt->execute([$imageId]);
        $imageData = $stmt->fetch();
    } elseif ($productId > 0) {
        // Buscar imagem principal do produto ativo
        $stmt = $pdo->prepare('
            SELECT pi.image, pi.image_type 
            FROM product_images pi
            JOIN products p ON pi.product_id = p.id
            WHERE pi.product_id = ? AND p.active = 1 AND pi.is_primary = 1
            LIMIT 1
        ');
        $stmt->execute([$productId]);
        $imageData = $stmt->fetch();
        
        // Se não tiver imagem principal, pega a primeira
        if (!$imageData) {
            $stmt = $pdo->prepare('
                SELECT pi.image, pi.image_type 
                FROM product_images pi
                JOIN products p ON pi.product_id = p.id
                WHERE pi.product_id = ? AND p.active = 1
                ORDER BY pi.display_order ASC
                LIMIT 1
            ');
            $stmt->execute([$productId]);
            $imageData = $stmt->fetch();
        }
    } else {
        // Retornar placeholder SVG direto
        header('Content-Type: image/svg+xml');
        echo file_get_contents(__DIR__ . '/../assets/img/placeholder.svg');
        exit;
    }
    
    if (!$imageData || empty($imageData['image'])) {
        // Retornar um placeholder SVG direto
        header('Content-Type: image/svg+xml');
        echo file_get_contents(__DIR__ . '/../assets/img/placeholder.svg');
        exit;
    }
    
    // Define o tipo de conteúdo
    $imageType = $imageData['image_type'] ?: 'image/jpeg';
    header('Content-Type: ' . $imageType);
    
    // Cache por 1 dia
    header('Cache-Control: public, max-age=86400');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
    
    // Envia a imagem
    echo $imageData['image'];
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro ao buscar imagem do produto: " . $e->getMessage());
    // Retornar placeholder SVG direto
    header('Content-Type: image/svg+xml');
    echo file_get_contents(__DIR__ . '/../assets/img/placeholder.svg');
    exit;
}
