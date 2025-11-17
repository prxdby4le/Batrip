<?php
// Admin criação/edição de conjuntos
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
$pageTitle = 'Novo Conjunto | Administração';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/icon-helper.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$set = ['title' => '', 'description' => '', 'price' => '0.00', 'image' => '', 'active' => 1];
$allProducts = [];
$existingItems = [];

if ($id) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM sets WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $set = $row; $pageTitle = 'Editar Conjunto | Administração'; }
    // Carregar itens existentes
    $stmt = $pdo->prepare('SELECT si.product_id, si.quantity FROM set_items si WHERE si.set_id = ?');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $it) { $existingItems[(int)$it['product_id']] = (int)$it['quantity']; }
    } catch (PDOException $e) { error_log('Erro buscar conjunto: ' . $e->getMessage()); }
}

// Carregar produtos ativos para montar o conjunto
try {
  $stmt = $pdo->query('SELECT id, title, price FROM products WHERE active = 1 ORDER BY title ASC');
  $allProducts = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Erro ao buscar produtos para conjunto: ' . $e->getMessage());
}

include '../../../includes/head.php';
$baseHref = $baseHref ?? '/';
?>
<body>
<?php include '../../../includes/cart-sidebar.php'; ?>
<div class="navbar-space"></div>
<section class="admin-section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><?= $id ? 'Editar Conjunto' : 'Novo Conjunto' ?></h2>
  <a href="<?= $baseHref ?>adm/conjuntos/index.php" class="btn btn-outline-light">Voltar</a>
    </div>

    <div class="card bg-dark text-light">
      <div class="card-body">
  <form method="post" action="<?= $baseHref ?>adm/conjuntos/save.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Título</label>
              <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($set['title']) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Preço (R$)</label>
              <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= htmlspecialchars((string)$set['price']) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select name="active" class="form-select">
                <option value="1" <?= !empty($set['active']) ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= empty($set['active']) ? 'selected' : '' ?>>Inativo</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Descrição</label>
              <textarea name="description" rows="4" class="form-control" placeholder="Detalhes do conjunto..."><?= htmlspecialchars($set['description']) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Imagem (JPG/PNG)</label>
              <input type="file" name="image" accept="image/*" class="form-control">
              <?php if (!empty($set['image']) && $id): ?>
                <div class="mt-2">
                  <img src="<?= $baseHref ?>set-image.php?id=<?= (int)$id ?>&size=thumb" alt="preview" style="width:140px;height:140px;object-fit:cover;border-radius:8px;border:1px solid #333;">
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!empty($allProducts)): ?>
          <hr class="my-4" />
          <div class="row g-3">
            <div class="col-12">
              <h5 class="mb-2">Itens do conjunto</h5>
              <p class="text-muted mb-3">Selecione os produtos que compõem este conjunto e defina as quantidades.</p>
              <div class="table-responsive">
                <table class="table table-dark table-striped align-middle mb-0">
                  <thead>
                    <tr>
                      <th style="width:60px;">Incluir</th>
                      <th>Produto</th>
                      <th style="width:140px;">Preço</th>
                      <th style="width:140px;">Quantidade</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allProducts as $p): $pid=(int)$p['id']; $checked = array_key_exists($pid, $existingItems); $qty = $existingItems[$pid] ?? 1; ?>
                      <tr>
                        <td>
                          <input type="checkbox" class="form-check-input" name="items[<?= $pid ?>][checked]" value="1" <?= $checked ? 'checked' : '' ?> />
                        </td>
                        <td>
                          <strong><?= htmlspecialchars($p['title']) ?></strong>
                        </td>
                        <td>R$ <?= number_format((float)$p['price'], 2, ',', '.') ?></td>
                        <td>
                          <input type="number" min="1" step="1" class="form-control form-control-sm" name="items[<?= $pid ?>][qty]" value="<?= (int)$qty ?>" />
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success">
              <?= icon('save', 'icon me-2') ?>Salvar
            </button>
            <a href="<?= $baseHref ?>adm/conjuntos/index.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php include '../../../includes/footer.php'; ?>
<?php include '../../../includes/scripts.php'; ?>
</body>
</html>
