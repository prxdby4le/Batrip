<?php
$pageTitle = 'Meus Pedidos | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();
include '../../includes/head.php';

$stmt = $pdo->prepare('SELECT id, subtotal, shipping, total, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 m-0">Meus Pedidos</h1>
  </div>
  <?php if (!$orders): ?>
    <div class="alert alert-info">Você ainda não realizou nenhum pedido.</div>
    <a class="btn btn-custom" href="../index.php">Ir às compras</a>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Data</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Frete</th>
            <th class="text-end">Total</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= htmlspecialchars($o['created_at'] ?? '-') ?></td>
            <td class="text-end">R$ <?= number_format((float)$o['subtotal'],2,',','.') ?></td>
            <td class="text-end">R$ <?= number_format((float)$o['shipping'],2,',','.') ?></td>
            <td class="text-end">R$ <?= number_format((float)$o['total'],2,',','.') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-light" href="pedido.php?id=<?= (int)$o['id'] ?>">Ver</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
