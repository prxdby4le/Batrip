<?php
$pageTitle = 'Detalhe do Pedido | Batrip';
// require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();
include '../../includes/head.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: pedidos.php'); exit; }

// Garante que o pedido pertence ao usuário
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); echo 'Pedido não encontrado.'; exit; }

$stmtIt = $pdo->prepare('SELECT title, size, price, qty, image FROM order_items WHERE order_id = ?');
$stmtIt->execute([$id]);
$items = $stmtIt->fetchAll();

$addr = json_decode($order['address'] ?? '{}', true) ?: [];
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-5" style="max-width: 900px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 m-0">Pedido #<?= (int)$order['id'] ?></h1>
    <a class="btn btn-sm btn-outline-light" href="pedidos.php">Voltar</a>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card bg-dark text-light">
        <div class="card-body">
          <h5 class="fw-bold">Itens</h5>
          <ul class="list-group list-group-flush">
            <?php foreach ($items as $it): ?>
            <li class="list-group-item bg-dark text-light">
              <div class="d-flex align-items-center gap-3">
                <?php if (!empty($it['image'])): ?>
                  <img src="<?= htmlspecialchars($it['image']) ?>" alt="thumb" style="width:56px;height:56px;object-fit:cover;border-radius:4px;">
                <?php endif; ?>
                <div class="flex-grow-1">
                  <div class="fw-semibold"><?= htmlspecialchars($it['title']) ?></div>
                  <div class="text-muted small">Tamanho: <?= htmlspecialchars($it['size']) ?> · Qtd: <?= (int)$it['qty'] ?></div>
                </div>
                <div class="text-end">
                  <div>R$ <?= number_format((float)$it['price'],2,',','.') ?></div>
                  <div class="text-muted small">Parcial: R$ <?= number_format((float)$it['price']*(int)$it['qty'],2,',','.') ?></div>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card bg-dark text-light mb-4">
        <div class="card-body">
          <h5 class="fw-bold">Resumo</h5>
          <div class="d-flex justify-content-between"><span>Subtotal</span><span>R$ <?= number_format((float)$order['subtotal'],2,',','.') ?></span></div>
          <div class="d-flex justify-content-between"><span>Frete</span><span>R$ <?= number_format((float)$order['shipping'],2,',','.') ?></span></div>
          <hr>
          <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>R$ <?= number_format((float)$order['total'],2,',','.') ?></span></div>
        </div>
      </div>
      <div class="card bg-dark text-light">
        <div class="card-body">
          <h5 class="fw-bold">Entrega</h5>
          <div><?= htmlspecialchars(($addr['endereco'] ?? '-')) ?></div>
          <div><?= htmlspecialchars(($addr['cidade'] ?? '-') . ' / ' . ($addr['estado'] ?? '-')) ?></div>
          <div>CEP: <?= htmlspecialchars($addr['cep'] ?? '-') ?></div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
