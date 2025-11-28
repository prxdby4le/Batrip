<?php
/**
 * View: Admin/Dashboard/Index
 * Dashboard principal do admin
 */

$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$recentProducts = $recentProducts ?? [];
?>

<div class="navbar-space"></div>
<section class="admin-section" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 gap-2 flex-wrap">
            <h2 class="mb-0">Painel de Administração</h2>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>adm/produtos/novo" class="btn btn-success">
                    <i class="bi bi-plus me-2"></i>Novo Produto
                </a>
                <a href="<?= BASE_URL ?>adm/pedidos" class="btn btn-outline-light">
                    <i class="bi bi-bag me-2"></i>Ver Pedidos
                </a>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total de Produtos</h5>
                        <h2 class="text-primary"><?= $stats['totalProducts'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total de Pedidos</h5>
                        <h2 class="text-info"><?= $stats['totalOrders'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Pedidos Pendentes</h5>
                        <h2 class="text-warning"><?= $stats['pendingOrders'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Receita Total</h5>
                        <h2 class="text-success">R$ <?= number_format($stats['totalRevenue'] ?? 0, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Produtos -->
        <div class="table-responsive mb-5">
            <h3 class="mb-3">Produtos</h3>
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Tamanhos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentProducts)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="mb-0">Nenhum produto cadastrado.</p>
                                <a href="<?= BASE_URL ?>adm/produtos/novo" class="btn btn-success mt-2">Cadastrar Primeiro Produto</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentProducts as $product): ?>
                            <tr>
                                <td><?= (int)$product['id'] ?></td>
                                <td>
                                    <img src="<?= BASE_URL ?>product-image.php?id=<?= (int)$product['id'] ?>"
                                         alt="<?= htmlspecialchars($product['title']) ?>" 
                                         class="product-img-preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                                </td>
                                <td><?= htmlspecialchars($product['title']) ?></td>
                                <td>R$ <?= number_format((float)$product['price'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($product['sizes'] ?? 'P,M,G,GG') ?></td>
                                <td>
                                    <span class="badge <?= ($product['active'] ?? 1) ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ($product['active'] ?? 1) ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= BASE_URL ?>adm/produtos/<?= (int)$product['id'] ?>/editar" 
                                           class="btn btn-sm btn-outline-light" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="text-center">
                <a href="<?= BASE_URL ?>adm/produtos" class="btn btn-outline-light">Ver Todos os Produtos</a>
            </div>
        </div>
        
        <!-- Pedidos -->
        <div class="table-responsive">
            <h3 class="mb-3">Pedidos Recebidos</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-3">Nenhum pedido encontrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <?php
                                        $items = json_decode($order['items'] ?? '[]', true);
                                        $itemsSummary = '';
                                        if (!empty($items)) {
                                            $summaries = [];
                                            foreach ($items as $item) {
                                                $qty = $item['qty'] ?? $item['quantity'] ?? 1;
                                                $title = $item['title'] ?? 'Produto';
                                                $size = $item['size'] ?? '';
                                                $summaries[] = "{$qty}x {$title}" . ($size ? " ({$size})" : '');
                                            }
                                            $itemsSummary = implode(', ', $summaries);
                                        }
                                        ?>
                                        <tr>
                                            <td>#<?= (int)$order['id'] ?></td>
                                            <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?></td>
                                            <td>R$ <?= number_format((float)($order['total'] ?? 0), 2, ',', '.') ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($order['status'] ?? 'pending') {
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'production_complete' => 'info',
                                                        'shipped' => 'primary',
                                                        'delivered' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php 
                                                    echo match($order['status'] ?? 'pending') {
                                                        'pending' => 'Pendente',
                                                        'processing' => 'Em Produção',
                                                        'production_complete' => 'Produção Completa',
                                                        'shipped' => 'Enviado',
                                                        'delivered' => 'Entregue',
                                                        'cancelled' => 'Cancelado',
                                                        default => ucfirst($order['status'] ?? 'pending')
                                                    };
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>adm/pedidos/<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-light">
                                                    Ver Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>adm/pedidos" class="btn btn-outline-light">Ver Todos os Pedidos</a>
            </div>
        </div>
    </div>
</section>

<style>
.product-img-preview {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}
</style>
