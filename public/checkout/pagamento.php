<?php
$pageTitle = 'Pagamento | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Verificar se endereço e frete foram preenchidos
if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
    header('Location: endereco.php');
    exit;
}

// Verificar se há itens no carrinho
$cart = get_cart();
if (empty($cart)) {
    header('Location: ' . $base . 'index.php');
    exit;
}

$subtotal = get_cart_subtotal();
$frete = $_SESSION['checkout_frete']['preco'];
$total = $subtotal + $frete;

// Salvar método de pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['checkout_pagamento'] = [
        'metodo' => $_POST['metodo'] ?? 'simulacao'
    ];
    header('Location: revisao.php');
    exit;
}

include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="carrinho.php">Carrinho</a></li>
                    <li class="breadcrumb-item"><a href="endereco.php">Endereço</a></li>
                    <li class="breadcrumb-item"><a href="frete.php">Frete</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pagamento</li>
                </ol>
            </nav>
            
            <h2 class="section-title mb-4"><?= icon('credit-card', 'icon') ?> Pagamento</h2>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="alert alert-info">
                        <?= icon('info-circle', 'icon me-2') ?>
                        <strong>Modo de Demonstração:</strong> O pagamento será simulado. Em produção, integração com gateway de pagamento será implementada.
                    </div>
                    
                    <form method="POST">
                        <div class="card bg-dark text-light mb-3">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodo" id="simulacao" value="simulacao" checked>
                                    <label class="form-check-label w-100 d-flex justify-content-between" for="simulacao">
                                        <div>
                                            <strong>Simulação de Pagamento</strong>
                                            <div class="small text-muted">Para fins de teste</div>
                                        </div>
                                        <?= icon('check-circle', 'icon text-success fs-4') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-dark text-light mb-3 opacity-50">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodo" id="pix" value="pix" disabled>
                                    <label class="form-check-label" for="pix">
                                        <strong>Pix</strong>
                                        <div class="small text-muted">Pagamento instantâneo</div>
                                        <span class="badge bg-warning text-dark">Em breve</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-dark text-light mb-3 opacity-50">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodo" id="cartao" value="cartao" disabled>
                                    <label class="form-check-label" for="cartao">
                                        <strong>Cartão de Crédito</strong>
                                        <div class="small text-muted">Parcelamento em até 12x</div>
                                        <span class="badge bg-warning text-dark">Em breve</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <a href="frete.php" class="btn btn-outline-secondary">
                                <?= icon('arrow-left', 'icon me-2') ?>Voltar
                            </a>
                            <button type="submit" class="btn btn-custom flex-fill">
                                Revisar Pedido<?= icon('arrow-right', 'icon ms-2') ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="col-lg-4">
                    <div class="card bg-dark text-light">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">Resumo do Pedido</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frete (<?= htmlspecialchars($_SESSION['checkout_frete']['opcao']) ?>):</span>
                                <strong>R$ <?= number_format($frete, 2, ',', '.') ?></strong>
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="fs-5">Total:</strong>
                                <strong class="fs-5 text-success">R$ <?= number_format($total, 2, ',', '.') ?></strong>
                            </div>
                            <div class="small text-muted">
                                <?= icon('shield', 'icon me-1') ?>
                                Ambiente de teste seguro
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

