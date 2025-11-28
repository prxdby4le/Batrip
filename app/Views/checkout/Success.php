<?php
/**
 * View: Checkout/Success
 * Confirmação de pedido
 */

// Garante que ROOT_PATH está definido
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}

// Carrega configurações se necessário
if (!defined('BASE_URL')) {
    require_once ROOT_PATH . '/config/config.php';
}

$order_id = isset($order['id']) ? (int)$order['id'] : (isset($order_id) ? (int)$order_id : 0);
$shippingMethod = $order['shipping_method'] ?? null;
// Aceita tanto shipping_cost quanto shipping (compatibilidade)
$shippingCost = isset($order['shipping_cost']) ? (float)$order['shipping_cost'] : (isset($order['shipping']) ? (float)$order['shipping'] : 0.0);
$subtotal = isset($order['subtotal']) ? (float)$order['subtotal'] : 0.0;
$total = isset($order['total']) ? (float)$order['total'] : 0.0;
$items = isset($order['items']) && is_array($order['items']) ? $order['items'] : [];

// Decodifica endereço e frete se forem strings JSON
$address = [];
if (isset($order['endereco'])) {
    if (is_string($order['endereco'])) {
        $address = json_decode($order['endereco'], true) ?: [];
    } else {
        $address = $order['endereco'];
    }
}

$frete_data = [];
if (isset($order['frete'])) {
    if (is_string($order['frete'])) {
        $frete_data = json_decode($order['frete'], true) ?: [];
    } else {
        $frete_data = $order['frete'];
    }
}
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
                                                <th>Preço Unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $it): 
                                                // Aceita tanto 'qty' quanto 'quantity' (compatibilidade)
                                                $quantity = (int)($it['qty'] ?? $it['quantity'] ?? 1);
                                                $size = htmlspecialchars($it['size'] ?? '-');
                                                $title = htmlspecialchars($it['title'] ?? 'Produto sem nome');
                                                $price = (float)($it['price'] ?? 0);
                                                $subtotal_item = $price * $quantity;
                                                $image = $it['image'] ?? null;
                                            ?>
                                                <tr>
                                                    <td class="text-start">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <?php if ($image): ?>
                                                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                                                     alt="<?php echo $title; ?>" 
                                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                                                     onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.svg'; this.onerror=null;">
                                                            <?php endif; ?>
                                                            <span><?php echo $title; ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $size; ?></td>
                                                    <td><?php echo $quantity; ?></td>
                                                    <td>R$ <?php echo number_format($price, 2, ',', '.'); ?></td>
                                                    <td><strong>R$ <?php echo number_format($subtotal_item, 2, ',', '.'); ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            <div class="row text-start">
                                <div class="col-12">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>Subtotal dos Itens:</strong>
                                        <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                    </div>
                                    <?php if ($shippingCost > 0): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>Frete<?php echo $shippingMethod ? ' (' . htmlspecialchars($shippingMethod) . ')' : ''; ?>:</strong>
                                            <span>R$ <?php echo number_format($shippingCost, 2, ',', '.'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="fs-5">Total:</strong>
                                        <span class="fs-4 fw-bold" style="color: var(--accent-red);">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($address)): ?>
                                <div class="row text-start mt-4">
                                    <div class="col-12">
                                        <h6 class="mb-2"><strong>Endereço de Entrega:</strong></h6>
                                        <div class="text-muted">
                                            <?php 
                                            $endereco_parts = [];
                                            if (!empty($address['endereco'])) $endereco_parts[] = htmlspecialchars($address['endereco']);
                                            if (!empty($address['numero'])) $endereco_parts[] = htmlspecialchars($address['numero']);
                                            if (!empty($endereco_parts)) echo implode(', ', $endereco_parts) . '<br>';
                                            
                                            if (!empty($address['complemento'])) echo htmlspecialchars($address['complemento']) . '<br>';
                                            
                                            $cidade_parts = [];
                                            if (!empty($address['bairro'])) $cidade_parts[] = htmlspecialchars($address['bairro']);
                                            if (!empty($address['cidade'])) $cidade_parts[] = htmlspecialchars($address['cidade']);
                                            if (!empty($address['uf'])) $cidade_parts[] = htmlspecialchars($address['uf']);
                                            if (!empty($cidade_parts)) echo implode(' - ', $cidade_parts) . '<br>';
                                            
                                            if (!empty($address['cep'])) echo 'CEP: ' . htmlspecialchars($address['cep']);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
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
