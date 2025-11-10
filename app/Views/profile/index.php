<?php
/**
 * View: Profile/Index
 * Página de perfil do usuário
 */
?>

<section class="profile-page" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--accent-blue);"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($user['name'] ?? 'Usuário'); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                </div>
                
                <div class="list-group mt-3">
                    <a href="<?php echo BASE_URL; ?>perfil" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person me-2"></i>Meu Perfil
                    </a>
                    <a href="<?php echo BASE_URL; ?>perfil/editar" class="list-group-item list-group-item-action">
                        <i class="bi bi-pencil me-2"></i>Editar Perfil
                    </a>
                    <a href="<?php echo BASE_URL; ?>pedidos" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i>Meus Pedidos
                    </a>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Informações do Perfil</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Nome:</strong>
                                <p><?php echo htmlspecialchars($user['name'] ?? ''); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong>
                                <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($user['phone'])): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Telefone:</strong>
                                <p><?php echo htmlspecialchars($user['phone']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['address']) || !empty($user['city'])): ?>
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Endereço:</strong>
                                <p>
                                    <?php 
                                    $addressParts = array_filter([
                                        $user['address'] ?? '',
                                        $user['city'] ?? '',
                                        $user['state'] ?? '',
                                        $user['zipcode'] ?? ''
                                    ]);
                                    echo htmlspecialchars(implode(', ', $addressParts));
                                    ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Membro desde:</strong>
                                <p><?php echo date('d/m/Y', strtotime($user['created_at'] ?? 'now')); ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?php echo BASE_URL; ?>perfil/editar" class="btn btn-custom">
                                <i class="bi bi-pencil me-2"></i>Editar Perfil
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pedidos Recentes -->
                <?php if (!empty($orders)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pedidos Recentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($order['status']) {
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'shipped' => 'primary',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?php echo BASE_URL; ?>pedidos" class="btn btn-outline-primary">
                                Ver Todos os Pedidos
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

