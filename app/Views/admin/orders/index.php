<?php
/**
 * View: Admin Orders Index
 */

$orders = $orders ?? [];
$currentStatus = $currentStatus ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-bag me-2"></i>Pedidos</h3>
    
    <!-- Filtros -->
    <div class="btn-group" role="group">
        <a href="<?php echo BASE_URL; ?>adm/pedidos" 
           class="btn btn-sm <?php echo empty($currentStatus) ? 'btn-primary' : 'btn-outline-light'; ?>">
            Todos
        </a>
        <a href="<?php echo BASE_URL; ?>adm/pedidos?status=pending" 
           class="btn btn-sm <?php echo $currentStatus === 'pending' ? 'btn-warning' : 'btn-outline-light'; ?>">
            Pendentes
        </a>
        <a href="<?php echo BASE_URL; ?>adm/pedidos?status=processing" 
           class="btn btn-sm <?php echo $currentStatus === 'processing' ? 'btn-info' : 'btn-outline-light'; ?>">
            Processando
        </a>
        <a href="<?php echo BASE_URL; ?>adm/pedidos?status=completed" 
           class="btn btn-sm <?php echo $currentStatus === 'completed' ? 'btn-success' : 'btn-outline-light'; ?>">
            Concluídos
        </a>
    </div>
</div>

<?php if (!empty($orders)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th style="width: 120px;">Total</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 150px;">Data</th>
                            <th style="width: 150px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
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
                                    $labels = [
                                        'pending' => 'Pendente',
                                        'processing' => 'Processando',
                                        'completed' => 'Concluído',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    $label = $labels[$order['status']] ?? $order['status'];
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo $label; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>adm/pedidos/<?php echo $order['id']; ?>" 
                                       class="btn btn-sm btn-outline-info" title="Ver Detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($order['status'] !== 'cancelled'): ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>adm/pedidos/<?php echo $order['id']; ?>/deletar" 
                                              style="display: inline;" onsubmit="return confirm('Tem certeza que deseja cancelar este pedido?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bag" style="font-size: 4rem; color: var(--text-gray);"></i>
            <h4 class="mt-3">Nenhum pedido encontrado</h4>
            <p class="text-muted">Os pedidos aparecerão aqui quando forem realizados.</p>
        </div>
    </div>
<?php endif; ?>
