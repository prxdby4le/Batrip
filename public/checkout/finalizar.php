<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Buffer cedo para evitar 'headers already sent' por BOM/whitespace acidental
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
$pageTitle = 'Finalizando Pedido | Batrip';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';
// Base simples para links relativos a partir de /public/checkout/
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';

function finalizar_log($msg) {
    $logPath = __DIR__ . '/finalizar.log'; // Salva na mesma pasta do script
    if (!is_dir(dirname($logPath))) {
        mkdir(dirname($logPath), 0777, true);
    }
    file_put_contents($logPath, '['.date('Y-m-d H:i:s').'] '.$msg."\n", FILE_APPEND);
}
// Verificar se o checkout está completo
if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
    finalizar_log('Endereço ou frete ausente na sessão.');
    $error = 'Sessão de checkout incompleta. Por favor, volte e preencha o endereço e o frete.';
}
// Permite finalizar mesmo sem pagamento se simular estiver presente
if (!isset($error) && !isset($_SESSION['checkout_pagamento']) && !isset($_GET['simular']) && !isset($_POST['simular'])) {
    finalizar_log('Pagamento ausente na sessão.');
    $error = 'Sessão de pagamento não encontrada. Por favor, volte e tente novamente.';
}
if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
    finalizar_log('Endereço ou frete ausente na sessão.');
    header('Location: endereco.php');
    exit;
}

// Verificar se há itens no carrinho
$cart = get_cart();
if (empty($cart)) {
    finalizar_log('Carrinho vazio ao finalizar pedido.');
    header('Location: ' . $base . 'index.php');
    exit;
}

// Verificar se usuário está logado
if (!is_logged_in()) {
    finalizar_log('Usuário não está logado ao finalizar pedido.');
    $_SESSION['redirect_after_login'] = 'checkout/finalizar.php';
    header('Location: ' . $base . 'registros/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = null;
$error = null;

try {
    finalizar_log('Iniciando criação do pedido para user_id=' . $user_id);
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
    $endereco = $_SESSION['checkout_endereco'];
    $frete_data = $_SESSION['checkout_frete'];
    $endereco_json = json_encode($endereco);
    $frete_json = json_encode($frete_data);
    finalizar_log('Dados do pedido: subtotal=' . $subtotal . ', frete=' . $frete . ', total=' . $total);
    // Buscar dados do usuário
    $stmt = $pdo->prepare('SELECT name, email, phone FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $customer_name = $user['name'] ?? '';
    $customer_email = $user['email'] ?? '';
    $customer_phone = $user['phone'] ?? '';
    // Extrair campos do endereço
    $shipping_address = $endereco['endereco'] ?? '';
    $shipping_city = $endereco['cidade'] ?? '';
    $shipping_state = $endereco['uf'] ?? '';
    $shipping_zipcode = $endereco['cep'] ?? '';
    $shipping_method = $frete_data['servico'] ?? ($frete_data['nome'] ?? '');
    $shipping_cost = $frete;
    $payment_method = isset($_SESSION['checkout_pagamento']['metodo']) ? $_SESSION['checkout_pagamento']['metodo'] : (isset($_POST['simular']) || isset($_GET['simular']) ? 'pix' : '');
    $items_json = json_encode($cart_items);
    $status = 'aguardando';
    // Inserir pedido na tabela orders (apenas campos reais do schema)
    $stmt = $pdo->prepare('
        INSERT INTO orders (
            user_id, endereco, frete, order_type, subtotal, shipping, total, status, items, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([
        $user_id,
        $endereco_json,
        $frete_json,
        'normal',
        $subtotal,
        $frete,
        $total,
        $status,
        $items_json
    ]);
    $order_id = $pdo->lastInsertId();
    finalizar_log('Pedido criado com order_id=' . $order_id);
    // Inserir itens do pedido (se houver tabela order_items)
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
        finalizar_log('Itens do pedido inseridos para order_id=' . $order_id);
    }
    // Salvar pagamento se existir na sessão
    if (isset($_SESSION['checkout_pagamento'])) {
        $pag = $_SESSION['checkout_pagamento'];
        $stmt = $pdo->prepare('INSERT INTO payments (order_id, user_id, metodo, status, payment_id, valor, email, raw_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $order_id,
            $user_id,
            $pag['metodo'] ?? '',
            $pag['status'] ?? '',
            $pag['id'] ?? null,
            $pag['valor'] ?? $total,
            $pag['email'] ?? null,
            $pag['raw'] ?? null
        ]);
        finalizar_log('Pagamento registrado na tabela payments para order_id=' . $order_id);
    } elseif (isset($_GET['simular']) || isset($_POST['simular'])) {
        // Simula pagamento para fluxo Pix sem API
        $stmt = $pdo->prepare('INSERT INTO payments (order_id, user_id, metodo, status, payment_id, valor, email, raw_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $order_id,
            $user_id,
            'pix',
            'simulado',
            null,
            $total,
            null,
            json_encode(['simulado' => true])
        ]);
        finalizar_log('Pagamento simulado registrado na tabela payments para order_id=' . $order_id);
    }
    // Commit da transação
    $pdo->commit();
    finalizar_log('Pedido finalizado e commitado para order_id=' . $order_id);
    // Limpar carrinho e dados do checkout
    // Limpa carrinho usando a chave correta
    if (defined('CART_SESSION_KEY')) {
        unset($_SESSION[CART_SESSION_KEY]);
    } else {
        unset($_SESSION['cart']);
    }
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
    finalizar_log('Erro ao finalizar pedido: ' . $e->getMessage());
    $error = "Erro ao processar pedido. Por favor, tente novamente.";
}

include '../../includes/head.php';
?>
<body>
    <!-- Não inclui cart-sidebar.php nesta página para evitar erro do Offcanvas -->
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base, ENT_QUOTES) ?>index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="carrinho.php">Carrinho</a></li>
                    <li class="breadcrumb-item"><a href="endereco.php">Endereço</a></li>
                    <li class="breadcrumb-item"><a href="frete.php">Frete</a></li>
                    <li class="breadcrumb-item"><a href="pagamento.php">Pagamento</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Finalizar</li>
                    <li class="breadcrumb-item">Sucesso</li>
                </ol>
            </nav>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <h4 class="alert-heading"><?= icon('exclamation-triangle', 'icon me-2') ?>Erro ao processar pedido</h4>
                    <p><?= htmlspecialchars($error) ?></p>
                    <hr>
                    <a href="checkout/pagamento.php" class="btn btn-danger">Tentar Novamente</a>
                    <a href="checkout/carrinho.php" class="btn btn-outline-secondary">Voltar ao Carrinho</a>
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
