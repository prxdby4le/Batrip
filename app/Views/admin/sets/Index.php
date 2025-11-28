
<?php
/**
 * View: Admin Sets Index
 */

$sets = $sets ?? [];
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
                    <a class="nav-link active" href="<?php echo BASE_URL; ?>adm/conjuntos">Conjuntos</a>
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
    <h3><i class="bi bi-box-seam me-2"></i>Conjuntos</h3>
    <a href="<?php echo BASE_URL; ?>adm/conjuntos/novo" class="btn btn-custom">
        <i class="bi bi-plus-circle me-1"></i> Novo Conjunto
    </a>
</div>

<?php if (!empty($sets)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
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
                        <?php foreach ($sets as $set): ?>
                            <tr>
                                <td><?php echo $set['id']; ?></td>
                                <td>
                                    <?php
                                    // Sempre usa set-image.php para conjuntos (mais confiável)
                                    $imageUrl = BASE_URL . 'set-image.php?id=' . (int)$set['id'];
                                    ?>
                                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                                            alt="<?php echo htmlspecialchars($set['title']); ?>"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                            onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/placeholder.svg';">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($set['title']); ?></strong>
                                    <br><span class="badge bg-info">Conjunto</span>
                                    <?php if (!empty($set['description'])): ?>
                                        <br><small class="text-white">
                                            <?php echo htmlspecialchars(substr($set['description'], 0, 50)); ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>R$ <?php echo number_format($set['price'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $set['active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $set['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td><?php echo isset($set['created_at']) ? date('d/m/Y H:i', strtotime($set['created_at'])) : '-'; ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>conjunto/<?= (int)$set['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" title="Ver" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>adm/conjuntos/<?= (int)$set['id'] ?>/editar" 
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>adm/conjuntos/<?= (int)$set['id'] ?>/deletar" 
                                          style="display: inline;" onsubmit="return confirm('Tem certeza que deseja deletar este conjunto?');">
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
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam" style="font-size: 4rem; color: var(--text-gray);"></i>
            <h4 class="mt-3">Nenhum conjunto cadastrado</h4>
            <p class="text-white">Comece criando seu primeiro conjunto!</p>
            <a href="<?php echo BASE_URL; ?>adm/conjuntos/novo" class="btn btn-custom mt-3">
                <i class="bi bi-plus-circle me-1"></i> Criar Conjunto
            </a>
        </div>
    </div>
<?php endif; ?>

