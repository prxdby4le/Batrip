<?php
/**
 * View: Admin Dashboard
 */

$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$recentProducts = $recentProducts ?? [];
?>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Produtos</h6>
                    <h3 class="mb-0"><?php echo $stats['totalProducts'] ?? 0; ?></h3>
                </div>
                <i class="bi bi-box" style="font-size: 2.5rem; color: var(--accent-blue);"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Pedidos</h6>
                    <h3 class="mb-0"><?php echo $stats['totalOrders'] ?? 0; ?></h3>
                </div>
                <i class="bi bi-bag" style="font-size: 2.5rem; color: #28a745;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Pendentes</h6>
                    <h3 class="mb-0"><?php echo $stats['pendingOrders'] ?? 0; ?></h3>
                </div>
                <i class="bi bi-clock" style="font-size: 2.5rem; color: #ffc107;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Receita Total</h6>
                    <h3 class="mb-0">R$ <?php echo number_format($stats['totalRevenue'] ?? 0, 2, ',', '.'); ?></h3>
                </div>
                <i class="bi bi-currency-dollar" style="font-size: 2.5rem; color: #17a2b8;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pedidos Recentes -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-bag me-2"></i>Pedidos Recentes</h5>
        <a href="<?php echo BASE_URL; ?>adm/pedidos" class="btn btn-sm btn-outline-light">Ver Todos</a>
    </div>
    <div class="card-body">
        <?php if (!empty($recentOrders)): ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $badge = $badges[$order['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center py-3">Nenhum pedido ainda</p>
        <?php endif; ?>
    </div>
</div>

<!-- Produtos Recentes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-box me-2"></i>Produtos Recentes</h5>
        <a href="<?php echo BASE_URL; ?>adm/produtos" class="btn btn-sm btn-outline-light">Ver Todos</a>
    </div>
    <div class="card-body">
        <?php if (!empty($recentProducts)): ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProducts as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product['active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $product['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center py-3">Nenhum produto cadastrado</p>
        <?php endif; ?>
    </div>
</div>
