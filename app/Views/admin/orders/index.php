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
    <div class="table-responsive">
      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Data</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Frete</th>
            <th class="text-end">Total</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= htmlspecialchars($o['customer_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($o['created_at'] ?? '-') ?></td>
            <td class="text-end">R$ <?= number_format((float)($o['subtotal'] ?? 0),2,',','.') ?></td>
            <td class="text-end">R$ <?= number_format((float)($o['shipping'] ?? ($o['frete']['preco'] ?? 0)),2,',','.') ?></td>
            <td class="text-end">R$ <?= number_format((float)($o['total'] ?? 0),2,',','.') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-light" href="<?php echo BASE_URL; ?>adm/pedidos/<?= (int)$o['id'] ?>">Ver</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bag" style="font-size: 4rem; color: var(--text-gray);"></i>
            <h4 class="mt-3">Nenhum pedido encontrado</h4>
            <p class="text-white">Os pedidos aparecerão aqui quando forem realizados.</p>
        </div>
    </div>
<?php endif; ?>
