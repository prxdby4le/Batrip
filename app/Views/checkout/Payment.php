<?php
/**
 * View: Checkout/Payment
 * Formulário de pagamento Mercado Pago
 */

require_once ROOT_PATH . '/includes/icon-helper.php';

$subtotal = $subtotal ?? 0;
$frete = $frete ?? 0;
$total = $total ?? 0;
$mp_public_key = $mp_public_key ?? '';

$frete_opcao = $_SESSION['checkout_frete']['opcao'] ?? '';
?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/carrinho">Carrinho</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/endereco">Endereço</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/frete">Frete</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pagamento</li>
                <li class="breadcrumb-item">Revisão</li>
            </ol>
        </nav>
        
        <h2 class="section-title mb-4"><?= icon('credit-card', 'icon') ?> Pagamento</h2>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="alert alert-info">
                    <?= icon('info-circle', 'icon me-2') ?>
                    <strong>Modo de Demonstração:</strong> O pagamento será processado via Mercado Pago.
                </div>
                
                <!-- Checkout Transparente Mercado Pago -->
                <form id="mp-checkout-form" method="POST" autocomplete="off">
                    <div class="card bg-dark text-light mb-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Escolha o método de pagamento</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo" id="metodo-cartao" value="cartao" checked>
                                        <label class="form-check-label" for="metodo-cartao">Cartão de Crédito</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo" id="metodo-boleto" value="boleto">
                                        <label class="form-check-label" for="metodo-boleto">Boleto</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo" id="metodo-pix" value="pix">
                                        <label class="form-check-label" for="metodo-pix">Pix</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="mp-card-form">
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">Número do cartão</label>
                                    <input type="text" class="form-control" id="cardNumber" data-checkout="cardNumber" 
                                           maxlength="19" autocomplete="cc-number">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cardExpiration" class="form-label">Validade (MM/AA)</label>
                                        <input type="text" class="form-control" id="cardExpiration" data-checkout="cardExpiration" 
                                               maxlength="5" autocomplete="cc-exp">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="securityCode" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="securityCode" data-checkout="securityCode" 
                                               maxlength="4" autocomplete="cc-csc">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardholderName" class="form-label">Nome impresso no cartão</label>
                                    <input type="text" class="form-control" id="cardholderName" data-checkout="cardholderName"
                                           autocomplete="cc-name">
                                </div>
                                <div class="mb-3">
                                    <label for="docNumber" class="form-label">CPF do titular</label>
                                    <input type="text" class="form-control" id="docNumber" data-checkout="docNumber" 
                                           maxlength="14" autocomplete="off">
                                </div>
                                <input type="hidden" name="paymentMethodId" id="paymentMethodId">
                                <input type="hidden" name="token" id="token">
                            </div>
                            
                            <div id="mp-boleto-form" style="display:none;">
                                <div class="mb-3">
                                    <label for="boletoName" class="form-label">Nome completo</label>
                                    <input type="text" class="form-control" id="boletoName" autocomplete="name">
                                </div>
                                <div class="mb-3">
                                    <label for="boletoEmail" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="boletoEmail" autocomplete="email">
                                </div>
                                <div class="mb-3">
                                    <label for="boletoCPF" class="form-label">CPF</label>
                                    <input type="text" class="form-control" id="boletoCPF" maxlength="14" autocomplete="off">
                                </div>
                            </div>
                            
                            <div id="mp-pix-form" style="display:none;">
                                <div class="mb-3">
                                    <label for="pixName" class="form-label">Nome completo</label>
                                    <input type="text" class="form-control" id="pixName" autocomplete="name">
                                </div>
                                <div class="mb-3">
                                    <label for="pixEmail" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="pixEmail" autocomplete="email">
                                </div>
                                <div class="mb-3">
                                    <label for="pixCPF" class="form-label">CPF</label>
                                    <input type="text" class="form-control" id="pixCPF" maxlength="14" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <a href="<?= BASE_URL ?>checkout/frete" class="btn btn-outline-secondary">
                            <?= icon('arrow-left', 'icon me-2') ?>Voltar
                        </a>
                        <button type="submit" class="btn btn-custom flex-fill" id="pay-btn">
                            Pagar<?= icon('arrow-right', 'icon ms-2') ?>
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
                            <span>Frete<?= $frete_opcao ? ' (' . htmlspecialchars($frete_opcao) . ')' : '' ?>:</span>
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

<!-- Mercado Pago JS SDK -->
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
let mp;
<?php if ($mp_public_key): ?>
mp = new MercadoPago('<?= htmlspecialchars($mp_public_key) ?>', {locale: 'pt-BR'});
window.MercadoPagoObj = mp;
<?php else: ?>
fetch('<?= BASE_URL ?>checkout/mp-public-key.php')
    .then(r => r.json())
    .then(data => {
        if (data.public_key) {
            mp = new MercadoPago(data.public_key, {locale: 'pt-BR'});
            window.MercadoPagoObj = mp;
        }
    });
<?php endif; ?>
</script>
<script src="<?= BASE_URL ?>assets/js/mp-checkout.js"></script>
<script>
// Atualiza o endpoint do formulário para usar a rota MVC
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mp-checkout-form');
    if (form) {
        // Intercepta o submit do mp-checkout.js e atualiza a URL
        const originalSubmit = form.onsubmit;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const metodo = form.querySelector('input[name="metodo"]:checked').value;
            
            // Se o script mp-checkout.js já está fazendo o submit, não fazemos nada
            // Caso contrário, precisamos atualizar o fetch dentro do mp-checkout.js
        });
    }
});
</script>

