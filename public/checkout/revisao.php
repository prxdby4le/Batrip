<?php
// Buffer cedo para evitar 'headers already sent' por BOM/whitespace acidental
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
$pageTitle = 'Revisão do Pedido | Batrip';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Base simples para links relativos a partir de /public/checkout/
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';

// Buscar detalhes dos produtos ANTES do processamento do pedido
$cart = get_cart();
$cart_items = [];
$subtotal = 0;
foreach ($cart as $item) {
    $productId = isset($item['id']) ? (int)$item['id'] : 0;
    if ($productId > 0) {
        try {
            $stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND active = 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            if ($product) {
                $quantity = isset($item['qty']) ? (int)$item['qty'] : 1;
                $size = isset($item['size']) ? trim($item['size']) : 'M';
                $item_subtotal = $product['price'] * $quantity;
                $subtotal += $item_subtotal;
                $cart_items[] = [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'price' => (float)$product['price'],
                    'quantity' => $quantity,
                    'size' => $size,
                    'subtotal' => $item_subtotal
                ];
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar produto ID {$productId}: " . $e->getMessage());
        }
    }
}

// PROCESSAMENTO DO PEDIDO ANTES DE QUALQUER HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pedido'])) {
    $userId = $_SESSION['user_id'];
    $endereco = json_encode($_SESSION['checkout_endereco']);
    $freteArr = $_SESSION['checkout_frete'];
    $freteJson = json_encode($freteArr);
    $shipping = isset($freteArr['preco']) ? (float)$freteArr['preco'] : 0;
    $total = $subtotal + $shipping;
    $status = 'aguardando';
    $items = json_encode($cart_items);

    $stmt = $pdo->prepare('INSERT INTO orders (user_id, endereco, frete, subtotal, shipping, total, status, items) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $endereco, $freteJson, $subtotal, $shipping, $total, $status, $items]);
    $orderId = $pdo->lastInsertId();

    // Limpa carrinho usando a chave correta
    if (defined('CART_SESSION_KEY')) {
        unset($_SESSION[CART_SESSION_KEY]);
    } else {
        unset($_SESSION['cart']);
    }
    unset($_SESSION['checkout_endereco'], $_SESSION['checkout_frete'], $_SESSION['checkout_pagamento']);
    header('Location: /checkout/sucesso.php?order=' . $orderId);
    exit;
}

// Verificar se todo o checkout foi preenchido
if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete']) || !isset($_SESSION['checkout_pagamento'])) {
    header('Location: pagamento.php');
    exit;
}

// Verificar se há itens no carrinho
$cart = get_cart();
if (empty($cart)) {
    header('Location: ' . $base . 'index.php');
    exit;
}
?>
<?php
// Buffer cedo para evitar 'headers already sent' por BOM/whitespace acidental
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
$pageTitle = 'Revisão do Pedido | Batrip';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Base simples para links relativos a partir de /public/checkout/
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';

// Verificar se todo o checkout foi preenchido
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

// Buscar detalhes dos produtos
$cart_items = [];
$subtotal = 0;

foreach ($cart as $item) {
    $productId = isset($item['id']) ? (int)$item['id'] : 0;
    
    if ($productId > 0) {
        try {
            $stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND active = 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product) {
                $quantity = isset($item['qty']) ? (int)$item['qty'] : 1;
                $size = isset($item['size']) ? trim($item['size']) : 'M';
                $item_subtotal = $product['price'] * $quantity;
                $subtotal += $item_subtotal;
                
                $cart_items[] = [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'price' => (float)$product['price'],
                    'quantity' => $quantity,
                    'size' => $size,
                    'subtotal' => $item_subtotal
                ];
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar produto ID {$productId}: " . $e->getMessage());
        }
    }
}

$frete = $_SESSION['checkout_frete']['preco'];
$total = $subtotal + $frete;

include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <form method="POST" class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base, ENT_QUOTES) ?>index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="carrinho.php">Carrinho</a></li>
                    <li class="breadcrumb-item"><a href="endereco.php">Endereço</a></li>
                    <li class="breadcrumb-item"><a href="frete.php">Frete</a></li>
                    <li class="breadcrumb-item"><a href="pagamento.php">Pagamento</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Revisão</li>
                    <li class="breadcrumb-item">Finalizar</li>
                    <li class="breadcrumb-item">Sucesso</li>
                </ol>
            </nav>
            
            <h2 class="section-title mb-4"><?= icon('clipboard-check', 'icon') ?> Revisão do Pedido</h2>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Endereço -->
                    <div class="card bg-dark text-light mb-4">
                        <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= icon('map-marker', 'icon me-2') ?>Endereço de Entrega</h5>
                            <a href="checkout/endereco.php" class="btn btn-sm btn-outline-light">Editar</a>
                        </div>
                        <div class="card-body">
                            <p class="mb-1">
                                <?= htmlspecialchars($_SESSION['checkout_endereco']['endereco']) ?>, 
                                <?= htmlspecialchars($_SESSION['checkout_endereco']['numero']) ?>
                                <?php if (!empty($_SESSION['checkout_endereco']['complemento'])): ?>
                                    - <?= htmlspecialchars($_SESSION['checkout_endereco']['complemento']) ?>
                                <?php endif; ?>
                            </p>
                            <p class="mb-1">
                                <?= htmlspecialchars($_SESSION['checkout_endereco']['bairro']) ?> - 
                                <?= htmlspecialchars($_SESSION['checkout_endereco']['cidade']) ?>/<?= htmlspecialchars($_SESSION['checkout_endereco']['uf']) ?>
                            </p>
                            <p class="mb-0">CEP: <?= htmlspecialchars($_SESSION['checkout_endereco']['cep']) ?></p>
                            <?php if (!empty($_SESSION['checkout_endereco']['comentario'])): ?>
                                <p class="mb-0 mt-2 text-white">
                                    <small><?= icon('comment', 'icon me-1') ?><?= htmlspecialchars($_SESSION['checkout_endereco']['comentario']) ?></small>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Itens do Pedido -->
                    <div class="card bg-dark text-light mb-4">
                        <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= icon('shopping-bag', 'icon me-2') ?>Itens do Pedido</h5>
                            <a href="checkout/carrinho.php" class="btn btn-sm btn-outline-light">Editar</a>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="row align-items-center border-bottom border-secondary py-3">
                                    <div class="col-3 col-md-2">
                                        <img src="<?= $base ?>product-image.php?id=<?= $item['id'] ?>" 
                                             alt="<?= htmlspecialchars($item['title']) ?>" 
                                             class="img-fluid rounded"
                                             style="max-height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-6 col-md-7">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                                        <small class="text-white">
                                            Tamanho: <?= htmlspecialchars($item['size']) ?> • 
                                            Qtd: <?= $item['quantity'] ?>
                                        </small>
                                    </div>
                                    <div class="col-3 text-end">
                                        <strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Frete -->
                    <div class="card bg-dark text-light mb-4">
                        <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= icon('truck', 'icon me-2') ?>Frete</h5>
                            <a href="checkout/frete.php" class="btn btn-sm btn-outline-light">Editar</a>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($_SESSION['checkout_frete']['opcao']) ?></strong>
                                    <div class="small text-muted">Prazo: <?= htmlspecialchars($_SESSION['checkout_frete']['prazo']) ?></div>
                                </div>
                                <strong class="text-success">R$ <?= number_format($frete, 2, ',', '.') ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagamento -->
                    <div class="card bg-dark text-light mb-4">
                        <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= icon('credit-card', 'icon me-2') ?>Forma de Pagamento</h5>
                            <a href="checkout/pagamento.php" class="btn btn-sm btn-outline-light">Editar</a>
                        </div>
                        <div class="card-body">
                            <strong>
                                <?php 
                                $metodo = $_SESSION['checkout_pagamento']['metodo'];
                                echo $metodo === 'simulacao' ? 'Simulação de Pagamento' : ucfirst($metodo);
                                ?>
                            </strong>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <a href="pagamento.php" class="btn btn-outline-secondary">
                            <?= icon('arrow-left', 'icon me-2') ?>Voltar para Pagamento
                        </a>
                        <button type="submit" name="confirmar_pedido" class="btn btn-success w-100">
                            <?= icon('check-circle', 'icon me-2') ?>Finalizar Pedido
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card bg-dark text-light">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">Resumo Final</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (<?= count($cart_items) ?> <?= count($cart_items) === 1 ? 'item' : 'itens' ?>):</span>
                                <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frete:</span>
                                <strong>R$ <?= number_format($frete, 2, ',', '.') ?></strong>
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="fs-4">Total:</strong>
                                <strong class="fs-4 text-success">R$ <?= number_format($total, 2, ',', '.') ?></strong>
                            </div>
                            <div class="alert alert-info p-2">
                                <small>
                                    <?= icon('info-circle', 'icon me-1') ?>
                                    Ao finalizar, você confirma a compra
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

        </form>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pedido'])) {
    // Montar dados do pedido
    $userId = $_SESSION['user_id'];
    $endereco = json_encode($_SESSION['checkout_endereco']);
    $freteArr = $_SESSION['checkout_frete'];
    $freteJson = json_encode($freteArr);
    $subtotal = $subtotal;
    $shipping = isset($freteArr['preco']) ? (float)$freteArr['preco'] : 0;
    $total = $subtotal + $shipping;
    $status = 'aguardando';
    $items = json_encode($cart_items);

    $stmt = $pdo->prepare('INSERT INTO orders (user_id, endereco, frete, subtotal, shipping, total, status, items) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $endereco, $freteJson, $subtotal, $shipping, $total, $status, $items]);
    $orderId = $pdo->lastInsertId();

    // Limpar carrinho e dados do checkout
    // Limpa carrinho usando a chave correta
    if (defined('CART_SESSION_KEY')) {
        unset($_SESSION[CART_SESSION_KEY]);
    } else {
        unset($_SESSION['cart']);
    }
    unset($_SESSION['checkout_endereco'], $_SESSION['checkout_frete'], $_SESSION['checkout_pagamento']);

    header('Location: /checkout/sucesso.php?order=' . $orderId);
    exit;
}
?>
<!-- Botão de confirmação de pedido -->



