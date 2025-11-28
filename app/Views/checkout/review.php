<?php
/**
 * View: Checkout/Review
 * Revisão do pedido antes de finalizar
 */

require_once ROOT_PATH . '/includes/icon-helper.php';

$cart_items = $cart_items ?? [];
$subtotal = $subtotal ?? 0;
$frete = $frete ?? 0;
$total = $total ?? 0;

$endereco = $_SESSION['checkout_endereco'] ?? [];
$frete_data = $_SESSION['checkout_frete'] ?? [];

// MODO DE TESTE: Permitir revisão sem pagamento (sempre ativo para testes)
$isTestMode = true; // Sempre permitir finalizar sem pagamento para testes

// Em modo de teste, criar pagamento simulado se não existir
if ($isTestMode && !isset($_SESSION['checkout_pagamento'])) {
    $_SESSION['checkout_pagamento'] = [
        'metodo' => 'teste',
        'status' => 'simulado'
    ];
}

$pagamento = $_SESSION['checkout_pagamento'] ?? [];
?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
    <form method="POST" action="<?= BASE_URL ?>checkout/finalizar" class="container">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/carrinho">Carrinho</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/endereco">Endereço</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/frete">Frete</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/pagamento">Pagamento</a></li>
                <li class="breadcrumb-item active" aria-current="page">Revisão</li>
            </ol>
        </nav>
        
        <h2 class="section-title mb-4"><?= icon('clipboard-check', 'icon') ?> Revisão do Pedido</h2>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Endereço -->
                <div class="card bg-dark text-light mb-4">
                    <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= icon('map-marker', 'icon me-2') ?>Endereço de Entrega</h5>
                        <a href="<?= BASE_URL ?>checkout/endereco" class="btn btn-sm btn-outline-light">Editar</a>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <?= htmlspecialchars($endereco['endereco'] ?? '') ?>, 
                            <?= htmlspecialchars($endereco['numero'] ?? '') ?>
                            <?php if (!empty($endereco['complemento'] ?? '')): ?>
                                - <?= htmlspecialchars($endereco['complemento']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="mb-1">
                            <?= htmlspecialchars($endereco['bairro'] ?? '') ?> - 
                            <?= htmlspecialchars($endereco['cidade'] ?? '') ?>/<?= htmlspecialchars($endereco['uf'] ?? '') ?>
                        </p>
                        <p class="mb-0">CEP: <?= htmlspecialchars($endereco['cep'] ?? '') ?></p>
                        <?php if (!empty($endereco['comentario'] ?? '')): ?>
                            <p class="mb-0 mt-2 text-white">
                                <small><?= icon('comment', 'icon me-1') ?><?= htmlspecialchars($endereco['comentario']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Itens do Pedido -->
                <div class="card bg-dark text-light mb-4">
                    <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= icon('shopping-bag', 'icon me-2') ?>Itens do Pedido</h5>
                        <a href="<?= BASE_URL ?>checkout/carrinho" class="btn btn-sm btn-outline-light">Editar</a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="row align-items-center border-bottom border-secondary py-3">
                                <div class="col-3 col-md-2">
                                    <img src="<?= BASE_URL ?>product-image.php?id=<?= $item['id'] ?>" 
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
                        <a href="<?= BASE_URL ?>checkout/frete" class="btn btn-sm btn-outline-light">Editar</a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= htmlspecialchars($frete_data['opcao'] ?? '') ?></strong>
                                <div class="small text-muted">Prazo: <?= htmlspecialchars($frete_data['prazo'] ?? '') ?></div>
                            </div>
                            <strong class="text-success">R$ <?= number_format($frete, 2, ',', '.') ?></strong>
                        </div>
                    </div>
                </div>
                
                <!-- Pagamento -->
                <div class="card bg-dark text-light mb-4">
                    <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= icon('credit-card', 'icon me-2') ?>Forma de Pagamento</h5>
                        <a href="<?= BASE_URL ?>checkout/pagamento" class="btn btn-sm btn-outline-light">Editar</a>
                    </div>
                    <div class="card-body">
                        <strong>
                            <?php 
                            $metodo = $pagamento['metodo'] ?? 'pix';
                            echo $metodo === 'simulacao' ? 'Simulação de Pagamento' : ucfirst($metodo);
                            ?>
                        </strong>
                        
                        <?php if ($metodo === 'pix' && !empty($pix_qr_code ?? null) && !empty($pix_copy_paste ?? null)): ?>
                            <!-- QR Code PIX -->
                            <div class="mt-4 p-3 bg-secondary rounded">
                                <h6 class="mb-3"><?= icon('qr-code', 'icon me-2') ?>Pagamento via PIX</h6>
                                <p class="mb-3">Escaneie o QR Code abaixo no app do seu banco ou copie o código PIX.</p>
                                
                                <div class="text-center mb-3">
                                    <img src="data:image/png;base64,<?= htmlspecialchars($pix_qr_code) ?>" 
                                         alt="QR Code PIX" 
                                         class="img-fluid"
                                         style="max-width: 250px; border: 2px solid #fff; border-radius: 8px; padding: 10px; background: #fff;">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label"><strong>Código copia e cola:</strong></label>
                                    <textarea class="form-control bg-dark text-light" 
                                              rows="3" 
                                              readonly 
                                              id="pix-copy-code"><?= htmlspecialchars($pix_copy_paste) ?></textarea>
                                </div>
                                
                                <button type="button" 
                                        class="btn btn-outline-light w-100" 
                                        onclick="copiarCodigoPix()">
                                    <?= icon('copy', 'icon me-2') ?>Copiar código PIX
                                </button>
                                
                                <div class="alert alert-info mt-3 mb-0 p-2">
                                    <small>
                                        <?= icon('info-circle', 'icon me-1') ?>
                                        O pedido será confirmado após o pagamento ser identificado.
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-4">
                    <a href="<?= BASE_URL ?>checkout/pagamento" class="btn btn-outline-secondary">
                        <?= icon('arrow-left', 'icon me-2') ?>Voltar para Pagamento
                    </a>
                    <?php 
                    // MODO DE TESTE: Sempre habilitar o botão (permite finalizar sem pagamento)
                    // Em produção, desabilitar apenas se for PIX e não tiver QR Code
                    $buttonDisabled = false;
                    if (!$isTestMode && $metodo === 'pix' && empty($pix_qr_code ?? null)) {
                        $buttonDisabled = true;
                    }
                    ?>
                    <button type="submit" class="btn btn-success w-100" <?= $buttonDisabled ? 'disabled' : '' ?>>
                        <?= icon('check-circle', 'icon me-2') ?>Finalizar Pedido
                        <?php if ($isTestMode): ?>
                            <small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                (Modo de Teste - Sem pagamento)
                            </small>
                        <?php endif; ?>
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
    </form>
</section>

<script>
function copiarCodigoPix() {
    const codigo = document.getElementById('pix-copy-code');
    if (codigo) {
        codigo.select();
        codigo.setSelectionRange(0, 99999); // Para mobile
        
        try {
            document.execCommand('copy');
            alert('Código PIX copiado para a área de transferência!');
        } catch (err) {
            // Fallback para navegadores modernos
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(codigo.value).then(function() {
                    alert('Código PIX copiado para a área de transferência!');
                }).catch(function() {
                    alert('Erro ao copiar código. Selecione o texto e copie manualmente.');
                });
            } else {
                alert('Selecione o texto e copie manualmente (Ctrl+C ou Cmd+C).');
            }
        }
    }
}
</script>
