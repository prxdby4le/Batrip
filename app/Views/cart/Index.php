<?php
/**
 * View: Cart/Index
 * Página do carrinho de compras
 */

use App\Helpers\CartHelper;

// Usa dados passados do controller
// Prioriza $cartItems do controller, depois $cart (compatibilidade)
$cart = !empty($cartItems) ? $cartItems : ($cart ?? []);
$cart_total = $total ?? 0;
$cart_count = $count ?? 0;
?>

<div class="navbar-space"></div>
<!-- Carrinho -->
<section class="cart-page" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <h1 class="mb-4">Meu Carrinho</h1>
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($cart)): ?>
            <div class="row">
                <!-- Itens do Carrinho -->
                <div class="col-lg-8">
                    <div class="cart-items">
                        <?php foreach ($cart as $index => $item): 
                            $isSet = CartHelper::isSet($item);
                            $setId = $isSet ? CartHelper::getSetId($item) : null;
                            $imageUrl = $isSet 
                                ? BASE_URL . 'set-image.php?id=' . ($setId ?? $item['id']) 
                                : BASE_URL . 'product-image.php?id=' . $item['id'];
                        ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                 class="img-fluid rounded"
                                                 onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.svg'">
                                        </div>
                                        <div class="col-md-4">
                                            <h5>
                                                <?php echo htmlspecialchars($item['title']); ?>
                                                <?php if ($isSet): ?>
                                                    <span class="badge bg-secondary ms-2">Conjunto</span>
                                                <?php endif; ?>
                                            </h5>
                                            <?php if (!$isSet): ?>
                                                <p class="text-white mb-0">Tamanho: <?php echo htmlspecialchars($item['size'] ?? 'M'); ?></p>
                                            <?php else: ?>
                                                <p class="text-white mb-0"><small>Conjunto completo</small></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">Quantidade</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm cart-qty-update" 
                                                   value="<?php echo $item['qty']; ?>" 
                                                   min="1" max="10"
                                                   data-index="<?php echo $index; ?>">
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <strong>R$ <?php echo number_format($item['price'] * $item['qty'], 2, ',', '.'); ?></strong>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button class="btn btn-sm btn-outline-danger cart-remove" 
                                                    data-index="<?php echo $index; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-outline-danger cart-clear">
                            <i class="bi bi-trash me-2"></i>Limpar Carrinho
                        </button>
                    </div>
                </div>
                
                <!-- Resumo -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Resumo do Pedido</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Itens (<?php echo $cart_count; ?>):</span>
                                <strong>R$ <?php echo number_format($cart_total, 2, ',', '.'); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-white">
                                <span>Frete:</span>
                                <span>Calculado no checkout</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary">R$ <?php echo number_format($cart_total, 2, ',', '.'); ?></strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="<?php echo BASE_URL; ?>checkout" class="btn btn-custom btn-lg">
                                    <i class="bi bi-bag-check me-2"></i>Finalizar Compra
                                </a>
                                <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-outline-light">
                                    <i class="bi bi-arrow-left me-2"></i>Continuar Comprando
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Carrinho Vazio -->
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: var(--text-gray);"></i>
                <h3 class="mt-4">Seu carrinho está vazio</h3>
                <p class="text-white mb-4">Adicione produtos para começar suas compras!</p>
                <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom">
                    Ver Produtos
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Script para gerenciar carrinho -->
<script>
// Atualizar quantidade
document.querySelectorAll('.cart-qty-update').forEach(input => {
    input.addEventListener('change', function() {
        const index = this.dataset.index;
        const qty = parseInt(this.value);
        
        if (qty < 1) {
            this.value = 1;
            return;
        }
        
        updateCartQuantity(index, qty);
    });
});

// Remover item
document.querySelectorAll('.cart-remove').forEach(btn => {
    btn.addEventListener('click', function() {
        const index = this.dataset.index;
        removeCartItem(index);
    });
});

// Limpar carrinho
document.querySelector('.cart-clear')?.addEventListener('click', function() {
    if (confirm('Tem certeza que deseja limpar o carrinho?')) {
        clearCart();
    }
});

function updateCartQuantity(index, qty) {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('<?php echo BASE_URL; ?>cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ index: parseInt(index, 10), qty: parseInt(qty, 10) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao atualizar quantidade');
        }
    });
}

function removeCartItem(index) {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('<?php echo BASE_URL; ?>cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ index: parseInt(index, 10) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao remover item');
        }
    });
}

function clearCart() {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('<?php echo BASE_URL; ?>cart/clear', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrf,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao limpar carrinho');
        }
    });
}
</script>
