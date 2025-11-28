<?php
/**
 * View: Profile/Orders
 * Lista de pedidos do usuário
 */
?>

<div class="navbar-space"></div>
<section class="profile-orders-page" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Meus Pedidos</h2>
                
                <?php if (!empty($orders)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Data</th>
                                        <th>Itens</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            $items = json_decode($order['items'] ?? '[]', true);
                                            echo count($items) . ' item(ns)';
                                            ?>
                                        </td>
                                        <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($order['status']) {
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
                                                echo match($order['status']) {
                                                    'pending' => 'Pendente',
                                                    'processing' => 'Em Produção',
                                                    'production_complete' => 'Produção Completa',
                                                    'shipped' => 'Enviado',
                                                    'delivered' => 'Entregue',
                                                    'cancelled' => 'Cancelado',
                                                    default => ucfirst($order['status'])
                                                };
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>registros/pedido.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                Ver Detalhes
                                            </a>
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
                        <i class="bi bi-bag-x" style="font-size: 4rem; color: var(--text-gray);"></i>
                        <h4 class="mt-3">Nenhum pedido encontrado</h4>
                        <p class="text-white">Você ainda não realizou nenhum pedido.</p>
                        <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom mt-3">
                            Ver Produtos
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

