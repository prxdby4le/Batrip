<?php
/**
 * View: Admin Orders Show
 */

$order = $order ?? [];
$items = $order['items'] ?? [];
?>

<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>adm/pedidos" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row">
    <!-- Informações do Pedido -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bag me-2"></i>Pedido #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                </h5>
            </div>
            <div class="card-body">
                <!-- Status -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Status do Pedido</label>
                    <form method="POST" action="<?php echo BASE_URL; ?>adm/pedidos/<?php echo $order['id']; ?>/status" class="d-inline">
                        <select name="status" class="form-select d-inline w-auto" onchange="if(confirm('Atualizar status?')) this.form.submit();">
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processando</option>
                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </form>
                </div>
                
                <!-- Itens do Pedido -->
                <h6 class="mb-3">Itens do Pedido</h6>
                <div class="table-responsive">
                    <table class="table table-dark">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Tamanho</th>
                                <th>Quantidade</th>
                                <th>Preço Unit.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['title'] ?? 'Produto'); ?></td>
                                        <td><?php echo htmlspecialchars($item['size'] ?? '-'); ?></td>
                                        <td><?php echo $item['qty'] ?? 1; ?></td>
                                        <td>R$ <?php echo number_format($item['price'] ?? 0, 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum item</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Subtotal:</th>
                                <th>R$ <?php echo number_format($order['subtotal'], 2, ',', '.'); ?></th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-primary">R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informações do Cliente e Entrega -->
    <div class="col-lg-4">
        <!-- Cliente -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Cliente</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Nome:</strong><br><?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p class="mb-2"><strong>Email:</strong><br><?php echo htmlspecialchars($order['customer_email']); ?></p>
                <p class="mb-0"><strong>Telefone:</strong><br><?php echo htmlspecialchars($order['customer_phone']); ?></p>
            </div>
        </div>
        
        <!-- Entrega -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Endereço de Entrega</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p class="mb-2"><?php echo htmlspecialchars($order['shipping_city']); ?> - <?php echo htmlspecialchars($order['shipping_state']); ?></p>
                <p class="mb-0">CEP: <?php echo htmlspecialchars($order['shipping_zipcode']); ?></p>
            </div>
        </div>
        
        <!-- Pagamento -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pagamento</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Método:</strong><br>
                    <?php
                    $methods = [
                        'pix' => 'PIX',
                        'credit' => 'Cartão de Crédito',
                        'boleto' => 'Boleto Bancário'
                    ];
                    echo $methods[$order['payment_method']] ?? $order['payment_method'];
                    ?>
                </p>
                <p class="mb-0">
                    <strong>Data:</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                </p>
            </div>
        </div>
    </div>
</div>
