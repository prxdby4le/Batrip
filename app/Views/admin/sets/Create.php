
<?php
/**
 * View: Admin Sets Create
 */

// Garante que config.php foi carregado
if (!defined('IMAGES_PER_PRODUCT_MAX')) {
    require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
}

$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
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

<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>adm/conjuntos" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Erros de Validação</h5>
        <ul class="mb-0">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="bi bi-plus-circle me-2"></i>Novo Conjunto
                </h4>
                
                <form method="POST" action="<?php echo BASE_URL; ?>adm/conjuntos/salvar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($old_input['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($old_input['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" 
                                       value="<?php echo htmlspecialchars($old_input['price'] ?? '0.00'); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção de produtos do conjunto -->
                    <div class="mb-4">
                        <hr class="my-4">
                        <h5 class="mb-3">
                            <i class="bi bi-box-seam me-2"></i>Produtos do Conjunto *
                        </h5>
                        <p class="text-muted mb-3">Selecione os produtos que compõem este conjunto e defina as quantidades.</p>
                        <?php if (!empty($products)): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">Incluir</th>
                                        <th>Produto</th>
                                        <th style="width:140px;">Preço</th>
                                        <th style="width:140px;">Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $p): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       class="form-check-input set-product-checkbox" 
                                                       name="set_items[<?= (int)$p['id'] ?>][checked]" 
                                                       value="1" 
                                                       data-product-id="<?= (int)$p['id'] ?>">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($p['title']) ?></strong>
                                            </td>
                                            <td>R$ <?= number_format((float)$p['price'], 2, ',', '.') ?></td>
                                            <td>
                                                <input type="number" 
                                                       min="1" 
                                                       step="1" 
                                                       class="form-control form-control-sm set-product-qty" 
                                                       name="set_items[<?= (int)$p['id'] ?>][qty]" 
                                                       value="1"
                                                       data-product-id="<?= (int)$p['id'] ?>"
                                                       disabled>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Nenhum produto ativo disponível. Crie produtos antes de criar um conjunto.
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagem do conjunto</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Formatos aceitos: JPG, PNG, WEBP (máx. 5MB)</small>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                            <label class="form-check-label" for="active">
                                Conjunto ativo (visível no site)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Criar Conjunto
                        </button>
                        <a href="<?php echo BASE_URL; ?>adm/conjuntos" class="btn btn-outline-light">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const setProductCheckboxes = document.querySelectorAll('.set-product-checkbox');
    const setProductQtyInputs = document.querySelectorAll('.set-product-qty');
    
    // Event listeners para checkboxes de produtos
    setProductCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const qtyInput = document.querySelector(`.set-product-qty[data-product-id="${productId}"]`);
            if (qtyInput) {
                qtyInput.disabled = !this.checked;
                if (!this.checked) {
                    qtyInput.value = '1';
                }
            }
        });
    });
})();
</script>

