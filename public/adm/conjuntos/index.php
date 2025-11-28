<?php
// Admin listagem de conjuntos
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
$pageTitle = 'Conjuntos | Administração';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/icon-helper.php';
require_admin();

// Buscar conjuntos
$sets = [];
try {
    $stmt = $pdo->query('SELECT id, title, price, image, active, created_at FROM sets ORDER BY id DESC');
    $sets = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erro ao buscar conjuntos: ' . $e->getMessage());
}

include '../../../includes/head.php';
$baseHref = $baseHref ?? '/';
?>
<body>
<?php include '../../../includes/cart-sidebar.php';
      require '../../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="admin-section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Conjuntos</h2>
      <a href="<?= $baseHref ?>adm/conjuntos/form.php" class="btn btn-success">
        <?= icon('plus', 'icon me-2') ?>Novo Conjunto
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Imagem</th>
            <th>Título</th>
            <th>Preço</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($sets)): ?>
          <tr>
            <td colspan="6" class="text-center py-4">Nenhum conjunto cadastrado.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($sets as $set): ?>
            <tr>
              <td><?= (int)$set['id'] ?></td>
              <td style="width:70px;">
                <img src="<?= $baseHref ?>set-image.php?id=<?= (int)$set['id'] ?>&size=thumb" alt="<?= htmlspecialchars($set['title']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #333;">
              </td>
              <td><?= htmlspecialchars($set['title']) ?></td>
              <td>R$ <?= number_format((float)$set['price'], 2, ',', '.') ?></td>
              <td>
                <span class="badge <?= $set['active'] ? 'bg-success' : 'bg-secondary' ?>">
                  <?= $set['active'] ? 'Ativo' : 'Inativo' ?>
                </span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <a class="btn btn-sm btn-outline-light" href="<?= $baseHref ?>adm/conjuntos/form.php?id=<?= (int)$set['id'] ?>" title="Editar">
                    <?= icon('edit', 'icon') ?>
                  </a>
                  <form method="post" action="<?= $baseHref ?>adm/conjuntos/toggle.php" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int)$set['id'] ?>">
                    <button class="btn btn-sm btn-outline-warning" type="submit" title="<?= $set['active'] ? 'Desativar' : 'Ativar' ?>">
                      <?= icon($set['active'] ? 'eye-slash' : 'eye', 'icon') ?>
                    </button>
                  </form>
                  <form method="post" action="<?= $baseHref ?>adm/conjuntos/delete.php" class="d-inline" onsubmit="return confirm('Excluir este conjunto?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int)$set['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir">
                      <?= icon('trash', 'icon') ?>
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
<?php include '../../../includes/footer.php'; ?>
<?php include '../../../includes/scripts.php'; ?>
</body>
</html>
