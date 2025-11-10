<?php
$pageTitle = 'Escolha o Frete | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Verificar se endereço foi preenchido
if (!isset($_SESSION['checkout_endereco'])) {
    header('Location: endereco.php');
    exit;
}

// Verificar se há itens no carrinho
$cart = get_cart();
if (empty($cart)) {
    header('Location: ' . $base . 'index.php');
    exit;
}

// Processar seleção de frete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frete_opcao = $_POST['frete'] ?? 'SEDEX';
    $frete_valores = [
        'SEDEX' => ['preco' => 29.90, 'prazo' => '3 dias úteis'],
        'PAC' => ['preco' => 19.90, 'prazo' => '7 dias úteis'],
        'GRATIS' => ['preco' => 0.00, 'prazo' => '15 dias úteis']
    ];
    
    $_SESSION['checkout_frete'] = [
        'opcao' => $frete_opcao,
        'preco' => $frete_valores[$frete_opcao]['preco'],
        'prazo' => $frete_valores[$frete_opcao]['prazo']
    ];
    
    header('Location: pagamento.php');
    exit;
}

$subtotal = get_cart_subtotal();
$frete_selecionado = $_SESSION['checkout_frete']['opcao'] ?? 'SEDEX';

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
                    <li class="breadcrumb-item active" aria-current="page">Frete</li>
                </ol>
            </nav>
            
            <h2 class="section-title mb-4"><?= icon('truck', 'icon') ?> Escolha o Frete</h2>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card bg-dark text-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Endereço de Entrega</h5>
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
                            <a href="endereco.php" class="btn btn-sm btn-outline-light mt-2">
                                <?= icon('edit', 'icon me-1') ?>Alterar endereço
                            </a>
                        </div>
                    </div>
                    
                    <form method="POST" id="frete-form" class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Opções de Frete</label>
                            
                            <div class="card bg-secondary mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="frete" id="sedex" value="SEDEX" 
                                               <?= $frete_selecionado === 'SEDEX' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100 d-flex justify-content-between" for="sedex">
                                            <div>
                                                <strong>SEDEX</strong>
                                                <div class="small text-muted">Entrega em até 3 dias úteis</div>
                                            </div>
                                            <strong class="text-success">R$ 29,90</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card bg-secondary mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="frete" id="pac" value="PAC"
                                               <?= $frete_selecionado === 'PAC' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100 d-flex justify-content-between" for="pac">
                                            <div>
                                                <strong>PAC</strong>
                                                <div class="small text-muted">Entrega em até 7 dias úteis</div>
                                            </div>
                                            <strong class="text-success">R$ 19,90</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($subtotal >= 200): ?>
                            <div class="card bg-secondary mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="frete" id="gratis" value="GRATIS"
                                               <?= $frete_selecionado === 'GRATIS' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100 d-flex justify-content-between" for="gratis">
                                            <div>
                                                <strong>Frete Grátis</strong>
                                                <div class="small text-muted">Entrega em até 15 dias úteis</div>
                                                <span class="badge bg-success">Compras acima de R$ 200</span>
                                            </div>
                                            <strong class="text-success">GRÁTIS</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12 d-flex gap-2">
                            <a href="endereco.php" class="btn btn-outline-secondary">
                                <?= icon('arrow-left', 'icon me-2') ?>Voltar
                            </a>
                            <button type="submit" class="btn btn-custom flex-fill">
                                Continuar para Pagamento<?= icon('arrow-right', 'icon ms-2') ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="col-lg-4">
                    <div class="card bg-dark text-light">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">Resumo</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frete:</span>
                                <span class="text-muted">Selecione uma opção</span>
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-success">R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
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

