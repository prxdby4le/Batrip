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
    <h5 class="offcanvas-title" id="cartSidebarLabel"><i class="fas fa-shopping-cart"></i> Carrinho
      <span class="badge bg-secondary ms-2" title="Itens no carrinho"><?php echo (int)get_cart_count(); ?></span>
    </h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (empty($cart)): ?>
      <div class="text-center text-muted">Seu carrinho está vazio.</div>
    <?php else: ?>
      <?php foreach ($cart as $item): ?>
        <div class="d-flex align-items-start mb-3">
          <img src="<?php echo htmlspecialchars((strpos($item['img'], 'assets/img/') === 0 ? $item['img'] : 'assets/img/' . ltrim($item['img'], '/')), ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
          <div class="flex-grow-1">
            <div class="fw-bold small mb-1"><?php echo htmlspecialchars($item['title']); ?></div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <!-- Atualizar tamanho -->
              <form method="post" action="<?php echo isset($base) ? htmlspecialchars($base) . 'cart.php' : 'cart.php'; ?>" class="d-flex align-items-center gap-1 cart-size-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="updateSize">
                <input type="hidden" name="title" value="<?php echo htmlspecialchars($item['title']); ?>">
                <input type="hidden" name="old_size" value="<?php echo htmlspecialchars($item['size']); ?>">
                <label class="text-muted small me-1">Tam:</label>
                <select name="new_size" class="form-select form-select-sm cart-size-select" style="width:auto; min-width:84px;">
                  <?php foreach (['P','M','G','GG'] as $sz): ?>
                    <option value="<?php echo $sz; ?>" <?php echo ($item['size'] === $sz ? 'selected' : ''); ?>><?php echo $sz; ?></option>
                  <?php endforeach; ?>
                </select>
              </form>

              <!-- Atualizar quantidade -->
              <form method="post" action="<?php echo isset($base) ? htmlspecialchars($base) . 'cart.php' : 'cart.php'; ?>" class="d-flex align-items-center gap-1 cart-qty-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="updateQty">
                <input type="hidden" name="title" value="<?php echo htmlspecialchars($item['title']); ?>">
                <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                <div class="input-group input-group-sm" style="width: 120px;">
                  <button class="btn btn-outline-secondary cart-qty-dec" type="button" aria-label="Diminuir">-</button>
                  <input type="number" name="qty" class="form-control text-center cart-qty-input" value="<?php echo (int)$item['qty']; ?>" min="1">
                  <button class="btn btn-outline-secondary cart-qty-inc" type="button" aria-label="Aumentar">+</button>
                </div>
              </form>

              <div class="text-muted small ms-auto">
                <?php echo (int)$item['qty']; ?> x R$ <?php echo number_format($item['price'], 2, ',', '.'); ?> = <span class="item-subtotal"><?php echo number_format($item['price'] * $item['qty'], 2, ',', '.'); ?></span>
              </div>
            </div>
          </div>
          <form method="post" action="<?php echo isset($base) ? htmlspecialchars($base) . 'cart.php' : 'cart.php'; ?>" class="ms-2">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="remove_title" value="<?php echo htmlspecialchars($item['title']); ?>">
            <input type="hidden" name="remove_size" value="<?php echo htmlspecialchars($item['size']); ?>">
            <button class="btn btn-sm btn-link text-danger remove-cart-item" type="submit" title="Remover"><i class="fas fa-trash"></i></button>
          </form>
        </div>
      <?php endforeach; ?>
      <hr>
      <div class="d-flex justify-content-between"><span>Subtotal:</span><span id="cart-preview-subtotal">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span></div>
      <div class="d-flex justify-content-between"><span>Frete:</span><span id="cart-preview-frete">R$ <?php echo number_format($frete, 2, ',', '.'); ?></span></div>
      <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="cart-preview-total">R$ <?php echo number_format($total, 2, ',', '.'); ?></span></div>
      <div class="text-muted small">CEP: <?php echo htmlspecialchars($cep); ?></div>
      <div class="d-grid gap-2 mt-3">
        <a href="<?php echo isset($base) ? htmlspecialchars($base) . 'checkout/carrinho.php' : 'checkout/carrinho.php'; ?>" class="btn btn-outline-light">Ver Carrinho</a>
        <a href="<?php echo isset($base) ? htmlspecialchars($base) . 'checkout/endereco.php' : 'checkout/endereco.php'; ?>" class="btn btn-custom">Finalizar Compra</a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php // Previa do carrinho renderizada acima ?>
