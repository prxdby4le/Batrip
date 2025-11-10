<?php
/**
 * View: Checkout/Index
 * Página de checkout
 */

use App\Helpers\CartHelper;

$cart = CartHelper::getCart();
$cart_total = CartHelper::getTotal();
$cart_count = CartHelper::getItemCount();
// Dados de frete e prefill vindos do controller
$shipping = $shipping ?? ($_SESSION['shipping'] ?? null);
$shippingCost = $shippingCost ?? ($shipping['cost'] ?? 0);
$prefill = $prefill ?? ($_SESSION['shipping_input'] ?? []);
?>

<!-- Checkout -->
<section class="checkout-page" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <h1 class="mb-4">Finalizar Compra</h1>
        
        <div class="row">
            <!-- Formulário de Checkout -->
            <div class="col-lg-7">
                <form method="POST" action="<?php echo BASE_URL; ?>checkout/process">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <!-- Informações Pessoais -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-person me-2"></i>Informações Pessoais
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="(11) 99999-9999" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Endereço de Entrega -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-geo-alt me-2"></i>Endereço de Entrega
                            </h5>
                            
                            <div class="row">
                    <div class="col-md-4 mb-3">
                     <label for="zipcode" class="form-label">CEP</label>
                     <input type="text" class="form-control" id="zipcode" name="zipcode" 
                         value="<?php echo htmlspecialchars($prefill['zipcode'] ?? ''); ?>" placeholder="00000-000" required>
                    </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="address" name="address" 
                        value="<?php echo htmlspecialchars($prefill['address'] ?? ''); ?>" placeholder="Rua, número" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="city" class="form-label">Cidade</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($prefill['city'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">Estado</label>
                                    <select class="form-select" id="state" name="state" required>
                                        <option value="">Selecione</option>
                                        <?php
                                        $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                        $selState = $prefill['state'] ?? '';
                                        foreach ($ufs as $uf) {
                                            $sel = ($selState === $uf) ? 'selected' : '';
                                            echo "<option value=\"{$uf}\" {$sel}>{$uf}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagamento -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-credit-card me-2"></i>Forma de Pagamento
                            </h5>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="payment_pix" value="pix" checked>
                                <label class="form-check-label" for="payment_pix">
                                    PIX
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="payment_credit" value="credit">
                                <label class="form-check-label" for="payment_credit">
                                    Cartão de Crédito
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="payment_boleto" value="boleto">
                                <label class="form-check-label" for="payment_boleto">
                                    Boleto Bancário
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-custom btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Finalizar Pedido
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Resumo do Pedido -->
            <div class="col-lg-5">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Resumo do Pedido</h5>
                        
                        <!-- Itens -->
                        <div class="checkout-items mb-3">
                            <?php foreach ($cart as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                        <small class="text-muted">
                                            Tam: <?php echo htmlspecialchars($item['size']); ?> | 
                                            Qtd: <?php echo $item['qty']; ?>
                                        </small>
                                    </div>
                                    <div>
                                        R$ <?php echo number_format($item['price'] * $item['qty'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Totais -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?php echo $cart_count; ?> itens):</span>
                            <strong>R$ <?php echo number_format($cart_total, 2, ',', '.'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>Frete:</span>
                            <span>
                                <?php if (!empty($shipping)): ?>
                                    R$ <?php echo number_format($shippingCost, 2, ',', '.'); ?> (<?php echo htmlspecialchars($shipping['method']); ?>)
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>frete" class="text-decoration-none" style="color: var(--accent-red);">Calcular frete</a>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong class="text-primary fs-4">
                                R$ <?php echo number_format($cart_total + ($shippingCost ?? 0), 2, ',', '.'); ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
