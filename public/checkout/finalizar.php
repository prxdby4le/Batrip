<?php
// Buffer cedo para evitar 'headers already sent' por BOM/whitespace acidental
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
$pageTitle = 'Finalizando Pedido | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';
// Base simples para links relativos a partir de /public/checkout/
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';

// Verificar se o checkout está completo
if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete']) || !isset($_SESSION['checkout_pagamento'])) {
    header('Location: endereco.php');
    exit;
}

// Verificar se há itens no carrinho
$cart = get_cart();
if (empty($cart)) {
    header('Location: ' . $base . 'index.php');
    exit;
}

// Verificar se usuário está logado
if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = 'checkout/finalizar.php';
    header('Location: ' . $base . 'registros/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = null;
$error = null;

try {
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Calcular totais
    $subtotal = 0;
    $cart_items = [];
    
    foreach ($cart as $item) {
        $productId = isset($item['id']) ? (int)$item['id'] : 0;
        
        if ($productId > 0) {
            $stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND active = 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product) {
                $quantity = isset($item['qty']) ? (int)$item['qty'] : 1;
                $size = isset($item['size']) ? trim($item['size']) : 'M';
                $item_subtotal = $product['price'] * $quantity;
                $subtotal += $item_subtotal;
                
                $cart_items[] = [
                    'product_id' => $product['id'],
                    'title' => $product['title'],
                    'price' => (float)$product['price'],
                    'quantity' => $quantity,
                    'size' => $size
                ];
            }
        }
    }
    
    $frete = $_SESSION['checkout_frete']['preco'];
    $total = $subtotal + $frete;
    
    // Preparar dados do endereço
    $endereco_json = json_encode($_SESSION['checkout_endereco']);
    $frete_json = json_encode($_SESSION['checkout_frete']);
    
    // Inserir pedido na tabela orders (schema base: subtotal, shipping, total, address)
    $stmt = $pdo->prepare('
        INSERT INTO orders (user_id, subtotal, shipping, total, address, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    
    $stmt->execute([
        $user_id,
        $subtotal,
        $frete,
        $total,
        $endereco_json
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Inserir itens do pedido (se houver tabela order_items)
    // Verificar se tabela existe
    $table_check = $pdo->query("SHOW TABLES LIKE 'order_items'")->rowCount();
    
    if ($table_check > 0) {
        $stmt = $pdo->prepare('
            INSERT INTO order_items (order_id, title, size, price, qty, image)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['title'],
                $item['size'],
                $item['price'],
                $item['quantity'],
                null
            ]);
        }
    }
    
    // Commit da transação
    $pdo->commit();
    
    // Limpar carrinho e dados do checkout
    unset($_SESSION['cart']);
    unset($_SESSION['checkout_endereco']);
    unset($_SESSION['checkout_frete']);
    unset($_SESSION['checkout_pagamento']);
    
    // Redirecionar para página de sucesso
    header('Location: sucesso.php?order=' . $order_id);
    exit;
    
} catch (PDOException $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao finalizar pedido: " . $e->getMessage());
    $error = "Erro ao processar pedido. Por favor, tente novamente.";
}

include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <h4 class="alert-heading"><?= icon('exclamation-triangle', 'icon me-2') ?>Erro ao processar pedido</h4>
                    <p><?= htmlspecialchars($error) ?></p>
                    <hr>
                    <a href="revisao.php" class="btn btn-danger">Tentar Novamente</a>
                    <a href="carrinho.php" class="btn btn-outline-secondary">Voltar ao Carrinho</a>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                        <span class="visually-hidden">Processando...</span>
                    </div>
                    <h3>Processando seu pedido...</h3>
                    <p class="text-muted">Por favor, aguarde.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
