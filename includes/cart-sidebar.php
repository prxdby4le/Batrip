<?php
// Cart sidebar funcional
require_once __DIR__ . '/cart-functions.php';

$cart = get_cart();
$cart_count = get_cart_count();
$cart_subtotal = get_cart_subtotal();
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartSidebarLabel">
      <i class="fas fa-shopping-cart me-2"></i>Carrinho
      <span class="badge bg-danger ms-2" id="sidebar-cart-count"><?= $cart_count ?></span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (empty($cart)): ?>
      <div class="text-center py-4">
        <div class="empty-cart-icon mb-3">
          <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--text-gray);"></i>
        </div>
        <h6>Carrinho vazio</h6>
        <p class="text-muted">Adicione produtos para começar suas compras!</p>
      </div>
    <?php else: ?>
      <div class="cart-items">
        <?php foreach ($cart as $index => $item): ?>
          <div class="cart-item mb-3 p-3 border rounded">
            <div class="d-flex align-items-start">
              <?php if (!empty($item['image'])): ?>
                <img src="<?= $basePath ?? '../' ?>assets/img/<?= htmlspecialchars($item['image']) ?>" 
                     alt="<?= htmlspecialchars($item['title']) ?>" 
                     class="me-3 rounded" style="width: 50px; height: 50px; object-fit: cover;">
              <?php endif; ?>
              <div class="flex-grow-1">
                <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                <div class="text-muted small">
                  Tamanho: <?= htmlspecialchars($item['size']) ?><br>
                  Qtd: <?= (int)$item['qty'] ?><br>
                  <strong>R$ <?= number_format((float)$item['price'] * (int)$item['qty'], 2, ',', '.') ?></strong>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger" 
                      onclick="removeFromCart('<?= htmlspecialchars($item['title']) ?>', '<?= htmlspecialchars($item['size']) ?>')">
                <i class="fas fa-times"></i>
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
        <div class="text-muted small mb-3">Frete calculado no checkout</div>
        <div class="d-grid gap-2">
          <a href="<?= ($basePath ?? '../') ?>cart.php" class="btn btn-outline-light">
            <i class="fas fa-edit me-1"></i>Editar Carrinho
          </a>
          <a href="<?= ($basePath ?? '../') ?>checkout/" class="btn btn-custom">
            <i class="fas fa-shopping-bag me-1"></i>Finalizar Compra
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
