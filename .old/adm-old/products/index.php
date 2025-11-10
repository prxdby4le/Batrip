<?php
$pageTitle = 'Admin • Produtos';
include '../../../includes/head.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

// Restrição mínima: requer login (sugestão: adicionar is_admin futuramente)
require_admin();

// Busca produtos
$stmt = $pdo->query('SELECT id, title, price, image, sizes, active, updated_at FROM products ORDER BY id DESC');
$products = $stmt->fetchAll();
?>
<body>
<?php include '../../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Produtos</h1>
    <a class="btn btn-sm btn-success" href="public/adm/products/form.php">Novo Produto</a>
  </div>

  <div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Título</th>
          <th>Preço</th>
          <th>Tamanhos</th>
          <th>Ativo</th>
          <th>Atualizado</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td class="text-truncate" style="max-width:280px;">
              <div class="d-flex align-items-center gap-2">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="thumb" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                <span><?= htmlspecialchars($p['title']) ?></span>
              </div>
            </td>
            <td>R$ <?= number_format((float)$p['price'], 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($p['sizes'] ?: 'P,M,G,GG') ?></td>
            <td>
              <span class="badge <?= $p['active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $p['active'] ? 'Ativo' : 'Inativo' ?></span>
            </td>
            <td><?= htmlspecialchars($p['updated_at'] ?: '-') ?></td>
            <td class="text-end">
              <div class="btn-group" role="group">
                <a class="btn btn-sm btn-outline-light" href="form.php?id=<?= (int)$p['id'] ?>">Editar</a>
                <form method="post" action="toggle.php" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn btn-sm btn-outline-warning" type="submit"><?= $p['active'] ? 'Desativar' : 'Ativar' ?></button>
                </form>
                <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Excluir este produto?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include '../../../includes/footer.php'; ?>
<?php include '../../../includes/scripts.php'; ?>
</body>
</html>
