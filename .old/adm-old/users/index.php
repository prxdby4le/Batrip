<?php
$pageTitle = 'Admin • Usuários';
include '../../../includes/head.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

$users = $pdo->query('SELECT id, name, email, is_admin, created_at FROM users ORDER BY id DESC')->fetchAll();
?>
<body>
<?php include '../../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Usuários</h1>
  </div>
  <div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Admin</th>
          <th>Desde</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge <?= !empty($u['is_admin']) ? 'bg-success' : 'bg-secondary' ?>"><?= !empty($u['is_admin']) ? 'Sim' : 'Não' ?></span></td>
          <td><?= htmlspecialchars($u['created_at'] ?? '-') ?></td>
          <td class="text-end">
            <div class="btn-group">
              <form action="toggle_admin.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-outline-warning" type="submit"><?php echo !empty($u['is_admin']) ? 'Remover Admin' : 'Tornar Admin'; ?></button>
              </form>
              <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
              <form action="delete.php" method="post" onsubmit="return confirm('Excluir este usuário? Esta ação é irreversível.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
              </form>
              <?php endif; ?>
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
