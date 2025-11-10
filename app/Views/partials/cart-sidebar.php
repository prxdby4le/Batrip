<?php
/**
 * Cart Sidebar - Carrinho lateral offcanvas
 */

use App\Helpers\CartHelper;

$cart = CartHelper::getCart();
$cart_count = CartHelper::getItemCount();
$cart_subtotal = CartHelper::getTotal();
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="cartSidebarLabel">
            <i class="bi bi-cart me-2"></i>Carrinho
            <span class="badge bg-danger ms-2" id="sidebar-cart-count"><?php echo $cart_count; ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    
    <div class="offcanvas-body">
        <?php if (empty($cart)): ?>
            <!-- Carrinho vazio -->
            <div class="text-center py-4">
                <div class="empty-cart-icon mb-3">
                    <i class="bi bi-cart" style="font-size: 4rem; color: var(--text-gray);"></i>
                </div>
                <h6>Carrinho vazio</h6>
                <p class="text-muted">Adicione produtos para começar suas compras!</p>
            </div>
        <?php else: ?>
            <!-- Itens do carrinho -->
            <div class="cart-items">
                <?php foreach ($cart as $index => $item): ?>
                    <?php 
                    $itemId = $item['id'] ?? 0;
                    $itemQty = $item['qty'] ?? 1;
                    $itemPrice = $item['price'] ?? 0;
                    $itemSize = $item['size'] ?? 'M';
                    $itemTitle = $item['title'] ?? 'Produto';
                    ?>
                    <div class="cart-item mb-3 p-3 border rounded" 
                         data-cart-id="<?php echo $itemId; ?>" 
                         data-cart-size="<?php echo htmlspecialchars($itemSize); ?>">
                        <div class="d-flex align-items-start">
                            <?php if ($itemId > 0): ?>
                                <img src="<?php echo BASE_URL; ?>product-image.php?id=<?php echo $itemId; ?>" 
                                     alt="<?php echo htmlspecialchars($itemTitle); ?>" 
                                     class="me-3 rounded" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($itemTitle); ?></h6>
                                <div class="text-muted small">
                                    Tamanho: <?php echo htmlspecialchars($itemSize); ?><br>
                                    Qtd: <?php echo $itemQty; ?><br>
                                    <strong>R$ <?php echo number_format($itemPrice * $itemQty, 2, ',', '.'); ?></strong>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-sidebar" 
                                    data-product-id="<?php echo $itemId; ?>" 
                                    data-product-size="<?php echo htmlspecialchars($itemSize); ?>">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumo do carrinho -->
            <div class="cart-summary mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong>R$ <?php echo number_format($cart_subtotal, 2, ',', '.'); ?></strong>
                </div>
                <div class="text-muted small mb-3">Frete calculado no checkout</div>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>cart" class="btn btn-outline-light">
                        <i class="bi bi-pencil me-1"></i>Editar Carrinho
                    </a>
                    <a href="<?php echo BASE_URL; ?>checkout" class="btn btn-custom">
                        <i class="bi bi-bag-check me-1"></i>Finalizar Compra
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
