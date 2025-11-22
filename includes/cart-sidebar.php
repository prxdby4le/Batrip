<?php
// Cart sidebar funcional
require_once __DIR__ . '/cart-functions.php';
require_once __DIR__ . '/icon-helper.php';

// Determinar caminho base se não estiver definido
if (!isset($base)) {
    $base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';
}

$cart = get_cart();
$cart_count = get_cart_count();
$cart_subtotal = get_cart_subtotal();
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartSidebarLabel">
      <?= icon('shopping-cart', 'icon me-2') ?>Carrinho
      <span class="badge bg-danger ms-2" id="sidebar-cart-count"><?= $cart_count ?></span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (empty($cart)): ?>
      <div class="text-center py-4">
        <div class="empty-cart-icon mb-3">
          <?= icon('shopping-cart', 'icon-3x', 'color: var(--text-gray);') ?>
        </div>
        <h6>Carrinho vazio</h6>
        <p class="text-muted">Adicione produtos para começar suas compras!</p>
      </div>
    <?php else: ?>
      <div class="cart-items">
        <?php foreach ($cart as $index => $item): ?>
          <?php 
          $itemId = isset($item['id']) ? (int)$item['id'] : 0;
          $itemQty = isset($item['qty']) ? (int)$item['qty'] : 1;
          $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
          $itemSize = isset($item['size']) ? trim($item['size']) : 'M';
          $itemTitle = isset($item['title']) ? $item['title'] : 'Produto';
          ?>
          <div class="cart-item mb-3 p-3 border rounded" data-cart-id="<?= $itemId ?>" data-cart-size="<?= htmlspecialchars($itemSize) ?>">
            <div class="d-flex align-items-start">
              <?php if ($itemId > 0): ?>
                <img src="<?= $base ?>product-image.php?id=<?= $itemId ?>" 
                     alt="<?= htmlspecialchars($itemTitle) ?>" 
                     class="me-3 rounded" style="width: 50px; height: 50px; object-fit: cover;">
              <?php endif; ?>
              <div class="flex-grow-1">
                <h6 class="mb-1 text-black"><?= htmlspecialchars($itemTitle) ?></h6>
                <div class="text-black small">
                  Tamanho: <?= htmlspecialchars($itemSize) ?><br>
                  Qtd: <?= $itemQty ?><br>
                  <strong style="color:#000 !important;">R$ <?= number_format($itemPrice * $itemQty, 2, ',', '.') ?></strong>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger btn-remove-sidebar" 
                      data-product-id="<?= $itemId ?>" 
                      data-product-size="<?= htmlspecialchars($itemSize) ?>">
                <?= icon('times', 'icon') ?>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <div class="cart-summary mt-3 pt-3 border-top">
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal:</span>
          <strong>R$ <?= number_format($cart_subtotal, 2, ',', '.') ?></strong>
        </div>
        <div class="text-black small mb-3">Frete calculado no checkout</div>
        <div class="d-grid gap-2">
          <a href="<?= $base ?>checkout/carrinho.php" class="btn btn-outline-light">
            <?= icon('edit', 'icon me-1') ?>Editar Carrinho
          </a>
          <a href="<?= $base ?>checkout/endereco.php" class="btn btn-custom">
            <?= icon('shopping-bag', 'icon me-1') ?>Finalizar Compra
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
