<?php
// Iniciar output buffering ANTES de qualquer output
ob_start();

// Suprimir warnings de upload que podem causar "headers already sent"
error_reporting(E_ALL & ~E_WARNING);

require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

// Verificar se houve erro de tamanho de POST antes de processar
if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0 && empty($_POST) && empty($_FILES)) {
    // POST foi truncado devido ao limite de post_max_size
    header('Location: form.php?error=arquivo_muito_grande');
    exit;
}

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
$title = trim($_POST['title'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$sizes = trim($_POST['sizes'] ?? 'P,M,G,GG');
$description = trim($_POST['description'] ?? '');
$active = (int)($_POST['active'] ?? 1);

// Validação básica
if ($title === '' || $price <= 0) {
    header('Location: form.php?id=' . $id . '&error=dados_invalidos');
    exit;
}

// Processar upload de múltiplas imagens
$uploadedImages = [];
$uploadError = '';

if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
    $maxSize = 10 * 1024 * 1024; // 10MB por imagem
    
    $fileCount = count($_FILES['images']['tmp_name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        // Pular se não foi enviado
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        
        // Verificar erros de upload
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            $uploadError = 'erro_upload';
            break;
        }
        
        $fileType = $_FILES['images']['type'][$i];
        $fileSize = $_FILES['images']['size'][$i];
        $fileTmp = $_FILES['images']['tmp_name'][$i];
        
        // Validar tipo MIME
        if (!in_array($fileType, $allowedTypes)) {
            $uploadError = 'tipo_invalido';
            break;
        }
        
        // Validar tamanho
        if ($fileSize > $maxSize) {
            $uploadError = 'arquivo_grande';
            break;
        }
        
        // Ler o arquivo
        $imageData = file_get_contents($fileTmp);
        if ($imageData === false) {
            $uploadError = 'erro_leitura';
            break;
        }
        
        $uploadedImages[] = [
            'data' => $imageData,
            'type' => $fileType
        ];
    }
    
    if ($uploadError) {
        header('Location: form.php?id=' . $id . '&error=' . $uploadError);
        exit;
    }
}

// Se for novo produto, ao menos uma imagem é obrigatória
if ($id === 0 && empty($uploadedImages)) {
    header('Location: form.php?id=' . $id . '&error=imagem_obrigatoria');
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($id > 0) {
        // Atualizar produto existente
        $stmt = $pdo->prepare('UPDATE products SET title=?, description=?, price=?, sizes=?, active=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$title, $description, $price, $sizes, $active, $id]);
        $productId = $id;
    } else {
        // Inserir novo produto
        $stmt = $pdo->prepare('INSERT INTO products (title, description, price, sizes, active) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $price, $sizes, $active]);
        $productId = $pdo->lastInsertId();
    }
    
    // Inserir novas imagens se houver
    if (!empty($uploadedImages)) {
        // Descobrir o próximo display_order
        $stmtOrder = $pdo->prepare('SELECT COALESCE(MAX(display_order), -1) + 1 as next_order FROM product_images WHERE product_id = ?');
        $stmtOrder->execute([$productId]);
        $nextOrder = (int)$stmtOrder->fetchColumn();
        
        // Verificar se o produto já tem imagem principal
        $stmtHasPrimary = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1');
        $stmtHasPrimary->execute([$productId]);
        $hasPrimary = (int)$stmtHasPrimary->fetchColumn() > 0;
        
        $stmtInsertImage = $pdo->prepare('INSERT INTO product_images (product_id, image, image_type, display_order, is_primary) VALUES (?, ?, ?, ?, ?)');
        
        foreach ($uploadedImages as $index => $img) {
            // A primeira imagem será principal se não houver nenhuma principal ainda
            $isPrimary = (!$hasPrimary && $index === 0) ? 1 : 0;
            $stmtInsertImage->execute([
                $productId,
                $img['data'],
                $img['type'],
                $nextOrder + $index,
                $isPrimary
            ]);
        }
    }
    
    $pdo->commit();
    header('Location: ../index-adm.php?success=1');
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorMsg = $e->getMessage();
    error_log("Erro ao salvar produto: " . $errorMsg);
    
    // Em desenvolvimento, mostrar mais detalhes
    $errorDetail = 'erro_banco';
    if (strpos($errorMsg, "doesn't have a default value") !== false) {
        $errorDetail = 'campo_obrigatorio';
    } elseif (strpos($errorMsg, 'Duplicate entry') !== false) {
        $errorDetail = 'duplicado';
    }
    
    header('Location: form.php?id=' . $id . '&error=' . $errorDetail . '&msg=' . urlencode(substr($errorMsg, 0, 100)));
    exit;
}
