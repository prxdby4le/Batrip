<?php
// Inicia buffer de saída para evitar "headers already sent"
ob_start();

$pageTitle = 'Administração | Batrip';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// Verificar se o usuário é admin
require_admin();

// Buscar produtos do banco de dados
try {
    $stmt = $pdo->query('SELECT id, title, price, image, sizes, active, created_at FROM products ORDER BY id DESC');
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    error_log("Erro ao buscar produtos: " . $e->getMessage());
}

// Buscar pedidos normais
try {
    $stmt = $pdo->query('
        SELECT o.id, u.name, u.email, o.status, o.created_at,
               GROUP_CONCAT(CONCAT(oi.quantity, "x ", p.title) SEPARATOR ", ") as items
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.order_type = "normal"
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ');
    $normalOrders = $stmt->fetchAll();
} catch (PDOException $e) {
    $normalOrders = [];
    error_log("Erro ao buscar pedidos normais: " . $e->getMessage());
}

// Buscar pedidos personalizados
try {
    $stmt = $pdo->query('
        SELECT o.id, u.name, u.email, o.status, o.created_at, o.custom_description
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.order_type = "custom"
        ORDER BY o.created_at DESC
    ');
    $customOrders = $stmt->fetchAll();
} catch (PDOException $e) {
    $customOrders = [];
    error_log("Erro ao buscar pedidos personalizados: " . $e->getMessage());
}

include '../../includes/head.php';
?>
<style>
    .admin-section { margin-top: 20px; }
    .product-img-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .table td, .table th { vertical-align: middle; }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Responsividade para tabelas */
    @media (max-width: 768px) {
        .table-responsive table th,
        .table-responsive table td {
            white-space: nowrap;
            font-size: 0.85rem;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .product-img-preview {
            width: 40px !important;
            height: 40px !important;
        }
    }
</style>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="admin-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Painel de Administração</h2>
                <a href="products/form.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Novo Produto
                </a>
            </div>
            
            <div class="table-responsive mb-5">
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
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="mb-0">Nenhum produto cadastrado.</p>
                                    <a href="products/form.php" class="btn btn-success mt-2">Cadastrar Primeiro Produto</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= (int)$product['id'] ?></td>
                                    <td>
                                        <img src="products/image.php?id=<?= (int)$product['id'] ?>" 
                                             alt="<?= htmlspecialchars($product['title']) ?>" 
                                             class="product-img-preview">
                                    </td>
                                    <td><?= htmlspecialchars($product['title']) ?></td>
                                    <td>R$ <?= number_format((float)$product['price'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($product['sizes'] ?: 'P,M,G,GG') ?></td>
                                    <td>
                                        <span class="badge <?= $product['active'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $product['active'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group d-flex d-md-inline-flex" role="group">
                                            <a href="products/form.php?id=<?= (int)$product['id'] ?>" 
                                               class="btn btn-sm btn-outline-light" title="Editar">
                                                <i class="fas fa-edit"></i>
                                                <span class="d-md-none ms-1">Editar</span>
                                            </a>
                                            <form method="post" action="products/toggle.php" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning" type="submit" 
                                                        title="<?= $product['active'] ? 'Desativar' : 'Ativar' ?>">
                                                    <i class="fas fa-<?= $product['active'] ? 'eye-slash' : 'eye' ?>"></i>
                                                    <span class="d-md-none ms-1"><?= $product['active'] ? 'Desativar' : 'Ativar' ?></span>
                                                </button>
                                            </form>
                                            <form method="post" action="products/delete.php" class="d-inline" 
                                                  onsubmit="return confirm('Excluir este produto?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="d-md-none ms-1">Excluir</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <section class="admin-section">
        <div class="container">
            <h2 class="mb-4 mt-5">Pedidos Recebidos</h2>
            <ul class="nav nav-tabs mb-3" id="ordersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="normal-orders-tab" data-bs-toggle="tab" data-bs-target="#normal-orders" type="button" role="tab">Pedidos Normais</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="custom-orders-tab" data-bs-toggle="tab" data-bs-target="#custom-orders" type="button" role="tab">Pedidos Personalizados</button>
                </li>
            </ul>
            <div class="tab-content" id="ordersTabContent">
                <div class="tab-pane fade show active" id="normal-orders" role="tabpanel">
                    <div class="card">
                        <div class="card-header">Pedidos Normais</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0" id="normalOrdersTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Email</th>
                                            <th>Produtos</th>
                                            <th>Status</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($normalOrders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-3">Nenhum pedido normal encontrado.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($normalOrders as $order): ?>
                                                <tr>
                                                    <td>#<?= (int)$order['id'] ?></td>
                                                    <td><?= htmlspecialchars($order['name']) ?></td>
                                                    <td><?= htmlspecialchars($order['email']) ?></td>
                                                    <td class="text-truncate" style="max-width: 200px;">
                                                        <?= htmlspecialchars($order['items'] ?: 'Sem itens') ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge status-badge 
                                                            <?php 
                                                                switch($order['status']) {
                                                                    case 'pending': echo 'bg-warning text-dark'; break;
                                                                    case 'processing': echo 'bg-info'; break;
                                                                    case 'shipped': echo 'bg-primary'; break;
                                                                    case 'delivered': echo 'bg-success'; break;
                                                                    case 'cancelled': echo 'bg-danger'; break;
                                                                    default: echo 'bg-secondary';
                                                                }
                                                            ?>">
                                                            <?php 
                                                                switch($order['status']) {
                                                                    case 'pending': echo 'Pendente'; break;
                                                                    case 'processing': echo 'Processando'; break;
                                                                    case 'shipped': echo 'Enviado'; break;
                                                                    case 'delivered': echo 'Entregue'; break;
                                                                    case 'cancelled': echo 'Cancelado'; break;
                                                                    default: echo ucfirst($order['status']);
                                                                }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="custom-orders" role="tabpanel">
                    <div class="card">
                        <div class="card-header">Pedidos Personalizados</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0" id="customOrdersTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Email</th>
                                            <th>Descrição</th>
                                            <th>Status</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($customOrders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-3">Nenhum pedido personalizado encontrado.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($customOrders as $order): ?>
                                                <tr>
                                                    <td>#<?= (int)$order['id'] ?></td>
                                                    <td><?= htmlspecialchars($order['name']) ?></td>
                                                    <td><?= htmlspecialchars($order['email']) ?></td>
                                                    <td class="text-truncate" style="max-width: 250px;">
                                                        <?= htmlspecialchars($order['custom_description'] ?: 'Sem descrição') ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge status-badge 
                                                            <?php 
                                                                switch($order['status']) {
                                                                    case 'pending': echo 'bg-warning text-dark'; break;
                                                                    case 'processing': echo 'bg-info'; break;
                                                                    case 'shipped': echo 'bg-primary'; break;
                                                                    case 'delivered': echo 'bg-success'; break;
                                                                    case 'cancelled': echo 'bg-danger'; break;
                                                                    default: echo 'bg-secondary';
                                                                }
                                                            ?>">
                                                            <?php 
                                                                switch($order['status']) {
                                                                    case 'pending': echo 'Pendente'; break;
                                                                    case 'processing': echo 'Processando'; break;
                                                                    case 'shipped': echo 'Enviado'; break;
                                                                    case 'delivered': echo 'Entregue'; break;
                                                                    case 'cancelled': echo 'Cancelado'; break;
                                                                    default: echo ucfirst($order['status']);
                                                                }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>

