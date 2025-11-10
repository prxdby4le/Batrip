<?php
$pageTitle = 'Conjunto | Batrip';
require_once __DIR__ . '/../../includes/head.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$set = null;
$setItems = [];
if ($id) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM sets WHERE id = ? AND active = 1');
        $stmt->execute([$id]);
        $set = $stmt->fetch();
    if ($set) {
      $stmt = $pdo->prepare('SELECT si.quantity, p.id as product_id, p.title, p.price, p.sizes FROM set_items si JOIN products p ON p.id = si.product_id WHERE si.set_id = ? ORDER BY p.title');
      $stmt->execute([$id]);
      $setItems = $stmt->fetchAll();
    }
    } catch (PDOException $e) {
        error_log('Erro ao buscar conjunto: ' . $e->getMessage());
    }
}

if (!$set) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Conjunto não encontrado</title></head><body style="font-family:sans-serif;background:#111;color:#eee;padding:2rem;">';
    echo '<h1>404 • Conjunto não encontrado</h1><p>O conjunto que você procura não está disponível.</p>';
    echo '<p><a href="/" style="color:#6cf;">Voltar para a home</a></p>';
    echo '</body></html>';
    exit;
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<?php include '../../includes/cart-sidebar.php'; ?>
<div class="navbar-space"></div>
<section class="section">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-md-6">
        <img src="/set-image.php?id=<?= (int)$id ?>&size=large" alt="<?= htmlspecialchars($set['title']) ?>" class="img-fluid rounded shadow" style="object-fit:cover; width:100%; max-height:520px;">
      </div>
      <div class="col-md-6">
        <h1 class="mb-2"><?= htmlspecialchars($set['title']) ?></h1>
        <p class="text-muted">Conjunto</p>
        <p class="product-price h4">R$ <?= number_format((float)$set['price'], 2, ',', '.') ?></p>
        <?php if (!empty($set['description'])): ?>
          <div class="mt-3"><p style="white-space:pre-line; color:var(--text-gray)"><?= nl2br(htmlspecialchars($set['description'])) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($setItems)): ?>
          <div class="mt-4">
            <h5 class="mb-2">Este conjunto inclui:</h5>
            <div class="table-responsive">
              <table class="table table-dark table-striped align-middle mb-0">
                <thead>
                  <tr>
                    <th>Produto</th>
                    <th style="width:120px;">Qtd</th>
                    <th style="width:160px;">Tamanho</th>
                    <th style="width:140px;">Preço</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($setItems as $idx => $si): 
                        $pid = (int)$si['product_id'];
                        $qty = (int)$si['quantity'];
                        $sizesStr = (string)($si['sizes'] ?? '');
                        $sizes = array_filter(array_map('trim', explode(',', $sizesStr ?: 'P,M,G,GG')));
                        $sizes = array_unique(array_map('strtoupper', $sizes));
                        $defaultSize = $sizes[0] ?? 'M';
                  ?>
                    <tr>
                      <td>
                        <a href="/produto.php?id=<?= $pid ?>" class="link-light text-decoration-underline"><?= htmlspecialchars($si['title']) ?></a>
                      </td>
                      <td><?= $qty ?>x</td>
                      <td>
                        <select class="form-select form-select-sm set-size" data-product-id="<?= $pid ?>" data-qty="<?= $qty ?>">
                          <?php foreach ($sizes as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $s === $defaultSize ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </td>
                      <td>R$ <?= number_format((float)$si['price'], 2, ',', '.') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
        <div class="mt-4 d-flex gap-2 align-items-center flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <label for="setQty" class="form-label mb-0">Quantidade de conjuntos:</label>
            <input type="number" id="setQty" class="form-control" style="width:100px;" value="1" min="1" max="10">
          </div>
          <button type="button" class="btn btn-custom" onclick="submitSetWithSizes(<?= (int)$id ?>)">
            <?= icon('cart-plus', 'icon me-2') ?>Adicionar conjunto ao carrinho
          </button>
          <a href="/" class="btn btn-outline-light">Voltar</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
<script>
function submitSetWithSizes(setId) {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || (window.CSRF_TOKEN || '');
  const selects = document.querySelectorAll('select.set-size');
  if (!selects.length) { if (typeof showAlert === 'function') showAlert('Nenhum item encontrado no conjunto.', 'warning'); return; }
  const items = [];
  for (const sel of selects) {
    const pid = parseInt(sel.getAttribute('data-product-id'), 10) || 0;
    const size = sel.value;
    if (!pid || !size) { if (typeof showAlert === 'function') showAlert('Selecione os tamanhos de todos os itens.', 'warning'); return; }
    items.push({ product_id: pid, size: size });
  }
  const setQtyEl = document.getElementById('setQty');
  const setQty = Math.max(1, Math.min(10, parseInt(setQtyEl ? setQtyEl.value : '1', 10) || 1));
  fetch('/cart-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify({ action: 'add_set', set_id: setId, set_qty: setQty, items })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      if (typeof updateCartCount === 'function') updateCartCount(res.cart_count);
      if (typeof showAlert === 'function') showAlert('Conjunto adicionado ao carrinho!', 'success');
    } else {
      if (typeof showAlert === 'function') showAlert(res.message || 'Erro ao adicionar conjunto', 'danger');
    }
  })
  .catch(err => {
    console.error(err);
    if (typeof showAlert === 'function') showAlert('Erro ao adicionar conjunto', 'danger');
  });
}
</script>
</body>
</html>
