
<?php
/**
 * View: Admin Products Index
 */

$products = $products ?? [];
?>

<!-- Navbar Admin -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-shield-lock me-2"></i>Administração</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm/produtos">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm/conjuntos">Conjuntos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm/pedidos">Pedidos</a>
                </li>
            </ul>
            <div class="d-flex">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> Retornar para visão de cliente
                </a>
            </div>
        </div>
    </div>
</nav>


<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <h3><i class="bi bi-box me-2"></i>Produtos</h3>
        <form id="filterForm" class="d-inline-block">
            <select class="form-select form-select-sm" id="typeFilter" name="type" style="min-width:140px;">
                <option value="all">Todos</option>
                <option value="product">Peças normais</option>
                <option value="set">Conjuntos</option>
            </select>
        </form>
    </div>
    <a href="<?php echo BASE_URL; ?>adm/produtos/novo" class="btn btn-custom">
        <i class="bi bi-plus-circle me-1"></i> Novo Produto
    </a>
</div>

<?php if (!empty($products)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 80px;">Imagem</th>
                            <th>Nome</th>
                            <th style="width: 120px;">Preço</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Data</th>
                            <th style="width: 200px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php 
                            $itemType = $product['item_type'] ?? ($product['type'] ?? 'product');
                            $isSet = $itemType === 'set';
                            $viewUrl = $isSet ? BASE_URL . 'conjunto/' . $product['id'] : BASE_URL . 'produto/' . $product['id'];
                            // Conjuntos usam rotas específicas
                            $editUrl = $isSet ? BASE_URL . 'adm/conjuntos/' . $product['id'] . '/editar' : BASE_URL . 'adm/produtos/' . $product['id'] . '/editar';
                            $deleteUrl = $isSet ? BASE_URL . 'adm/conjuntos/' . $product['id'] . '/deletar' : BASE_URL . 'adm/produtos/' . $product['id'] . '/deletar';
                            // Sempre usa set-image.php para conjuntos (mais confiável)
                            $imageUrl = $isSet ? BASE_URL . 'set-image.php?id=' . $product['id'] : (!empty($product['image']) ? $product['image'] : BASE_URL . 'product-image.php?id=' . $product['id']);
                            ?>
                            <tr data-type="<?= htmlspecialchars($itemType) ?>">
                                <td>
                                    <?php echo $product['id']; ?>
                                    <?php if ($isSet): ?>
                                        <br><small class="text-muted">Conjunto</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                                            alt="<?php echo htmlspecialchars($product['title']); ?>"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                            onerror="this.onerror=null; this.src='<?= $isSet ? BASE_URL . 'set-image.php?id=' . $product['id'] : BASE_URL . 'assets/img/placeholder.svg' ?>';">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                    <?php if ($isSet): ?>
                                        <br><span class="badge bg-info">Conjunto</span>
                                    <?php endif; ?>
                                    <?php if (!empty($product['description'])): ?>
                                        <br><small class="text-white">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product['active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $product['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td><?php echo isset($product['created_at']) ? date('d/m/Y H:i', strtotime($product['created_at'])) : '-'; ?></td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars($viewUrl) ?>" 
                                       class="btn btn-sm btn-outline-info" title="Ver" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= htmlspecialchars($editUrl) ?>" 
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?= htmlspecialchars($deleteUrl) ?>" 
                                          style="display: inline;" onsubmit="return confirm('Tem certeza que deseja deletar este <?= $isSet ? 'conjunto' : 'produto' ?>?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Deletar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('typeFilter').addEventListener('change', function() {
        const val = this.value;
        document.querySelectorAll('#productsTable tbody tr').forEach(row => {
            if (val === 'all') {
                row.style.display = '';
            } else {
                const rowType = row.getAttribute('data-type');
                row.style.display = (rowType === val) ? '' : 'none';
            }
        });
    });
    </script>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-box" style="font-size: 4rem; color: var(--text-gray);"></i>
            <h4 class="mt-3">Nenhum produto cadastrado</h4>
            <p class="text-white">Comece criando seu primeiro produto!</p>
            <a href="<?php echo BASE_URL; ?>adm/produtos/novo" class="btn btn-custom mt-3">
                <i class="bi bi-plus-circle me-1"></i> Criar Produto
            </a>
        </div>
    </div>
<?php endif; ?>
