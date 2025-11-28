<?php
/**
 * View: Checkout/Success
 * Confirmação de pedido
 */

$order_id = isset($order['id']) ? $order['id'] : (isset($order_id) ? $order_id : 0);
$shippingMethod = $order['shipping_method'] ?? null;
$shippingCost = isset($order['shipping_cost']) ? (float)$order['shipping_cost'] : null;
$subtotal = isset($order['subtotal']) ? (float)$order['subtotal'] : null;
$total = isset($order['total']) ? (float)$order['total'] : null;
$items = isset($order['items']) && is_array($order['items']) ? $order['items'] : [];
?>

<div class="navbar-space"></div>
<!-- Sucesso -->
<section class="success-page" style="padding-top: 20px; padding-bottom: 60px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <!-- Ícone de Sucesso -->
                <div class="success-icon mb-4">
                    <i class="bi bi-check-circle" style="font-size: 6rem; color: #28a745;"></i>
                </div>
                
                <h1 class="mb-3">Pedido Realizado com Sucesso!</h1>
                
                <p class="lead mb-4">
                    Obrigado pela sua compra! Seu pedido foi recebido e está sendo processado.
                </p>
                
                <?php if ($order_id > 0): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Resumo do Pedido</h5>
                            <p class="display-6 text-primary mb-3">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></p>
                            <?php if (!empty($items)): ?>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Produto</th>
                                                <th>Tam.</th>
                                                <th>Qtd</th>
                                                <th>Preço</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $it): ?>
                                                <tr>
                                                    <td class="text-start"><?php echo htmlspecialchars($it['title'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($it['size'] ?? '-'); ?></td>
                                                    <td><?php echo (int)($it['qty'] ?? 1); ?></td>
                                                    <td>R$ <?php echo number_format((float)($it['price'] ?? 0), 2, ',', '.'); ?></td>
                                                    <td>R$ <?php echo number_format(((float)($it['price'] ?? 0)) * ((int)($it['qty'] ?? 1)), 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            <div class="row text-start">
                                <?php if ($subtotal !== null): ?>
                                    <div class="col-md-6 mb-2">
                                        <strong>Subtotal:</strong><br>
                                        R$ <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($shippingMethod !== null): ?>
                                    <div class="col-md-6 mb-2">
                                        <strong>Frete (<?php echo htmlspecialchars($shippingMethod); ?>):</strong><br>
                                        R$ <?php echo number_format($shippingCost, 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($total !== null): ?>
                                    <div class="col-12 mt-2">
                                        <strong>Total:</strong><br>
                                        <span class="fs-4" style="color: var(--accent-red);">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    Você receberá um e-mail com os detalhes do seu pedido e instruções de pagamento.
                </div>
                
                <div class="d-flex gap-3 justify-content-center mt-4">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-custom btn-lg">
                        <i class="bi bi-house me-2"></i>Voltar para Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-shop me-2"></i>Continuar Comprando
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
