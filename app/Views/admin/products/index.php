<?php
/**
 * View: Admin Products Index
 */

$products = $products ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-box me-2"></i>Produtos</h3>
    <a href="<?php echo BASE_URL; ?>adm/produtos/criar" class="btn btn-custom">
        <i class="bi bi-plus-circle me-1"></i> Novo Produto
    </a>
</div>

<?php if (!empty($products)): ?>
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
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>product-image.php?id=<?php echo $product['id']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #333; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                    <?php if (!empty($product['description'])): ?>
                                        <br><small class="text-muted">
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
                                <td><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>produto/<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-outline-info" title="Ver" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>adm/produtos/<?php echo $product['id']; ?>/editar" 
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?php echo BASE_URL; ?>adm/produtos/<?php echo $product['id']; ?>/deletar" 
                                          style="display: inline;" onsubmit="return confirm('Tem certeza que deseja deletar este produto?');">
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
            <i class="bi bi-box" style="font-size: 4rem; color: var(--text-gray);"></i>
            <h4 class="mt-3">Nenhum produto cadastrado</h4>
            <p class="text-muted">Comece criando seu primeiro produto!</p>
            <a href="<?php echo BASE_URL; ?>adm/produtos/criar" class="btn btn-custom mt-3">
                <i class="bi bi-plus-circle me-1"></i> Criar Produto
            </a>
        </div>
    </div>
<?php endif; ?>
