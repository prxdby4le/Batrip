<?php
// Barra lateral do carrinho (offcanvas)
// Sidebar do carrinho em PHP
// cart-functions.php já foi incluído no nav.php
$cart = get_cart();
$cep = get_user_cep();
$frete = calcular_frete($cep);
$subtotal = get_cart_subtotal();
$total = $subtotal + $frete;
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartSidebarLabel"><i class="fas fa-shopping-cart"></i> Carrinho</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (empty($cart)): ?>
      <div class="text-center text-muted">Seu carrinho está vazio.</div>
    <?php else: ?>
      <?php foreach ($cart as $item): ?>
        <div class="d-flex align-items-center mb-3">
          <img src="<?php echo htmlspecialchars((strpos($item['img'], 'assets/img/') === 0 ? $item['img'] : 'assets/img/' . ltrim($item['img'], '/')), ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
          <div>
            <div class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></div>
            <div class="text-muted small">Tamanho: <?php echo htmlspecialchars($item['size']); ?></div>
            <div class="text-muted small">
              <?php echo $item['qty']; ?> x R$ <?php echo number_format($item['price'], 2, ',', '.'); ?> = <span class="item-subtotal"><?php echo number_format($item['price'] * $item['qty'], 2, ',', '.'); ?></span>
            </div>
          </div>
          <form method="post" action="cart.php" class="ms-auto">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="remove_title" value="<?php echo htmlspecialchars($item['title']); ?>">
            <input type="hidden" name="remove_size" value="<?php echo htmlspecialchars($item['size']); ?>">
            <button class="btn btn-sm btn-link text-danger remove-cart-item" type="submit"><i class="fas fa-trash"></i></button>
          </form>
        </div>
      <?php endforeach; ?>
      <hr>
      <div class="d-flex justify-content-between"><span>Subtotal:</span><span id="cart-preview-subtotal">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span></div>
      <div class="d-flex justify-content-between"><span>Frete:</span><span id="cart-preview-frete">R$ <?php echo number_format($frete, 2, ',', '.'); ?></span></div>
      <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="cart-preview-total">R$ <?php echo number_format($total, 2, ',', '.'); ?></span></div>
      <div class="text-muted small">CEP: <?php echo htmlspecialchars($cep); ?></div>
  <a href="checkout/revisao.php" class="btn btn-custom w-100 mt-3">Finalizar Compra</a>
    <?php endif; ?>
  </div>
</div>
<?php // Previa do carrinho renderizada acima ?>
