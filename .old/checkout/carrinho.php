<?php
$pageTitle = 'Carrinho | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

$cart = get_cart();
$cart_items = [];
$total = 0;

// Processar itens do carrinho com dados atualizados do banco
if (!empty($cart)) {
    foreach ($cart as $item) {
        $productId = isset($item['id']) ? (int)$item['id'] : 0;
        
        if ($productId > 0) {
            try {
                $stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND active = 1');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if ($product) {
                    $quantity = isset($item['qty']) ? (int)$item['qty'] : 1;
                    $size = isset($item['size']) ? trim($item['size']) : 'M';
                    $subtotal = $product['price'] * $quantity;
                    $total += $subtotal;
                    
                    $cart_items[] = [
                        'id' => $product['id'],
                        'title' => $product['title'],
                        'price' => (float)$product['price'],
                        'quantity' => $quantity,
                        'size' => $size,
                        'subtotal' => $subtotal
                    ];
                }
            } catch (PDOException $e) {
                error_log("Erro ao buscar produto ID {$productId}: " . $e->getMessage());
            }
        }
    }
}

include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><?= icon('shopping-cart', 'icon') ?> Carrinho de Compras</h2>
            
            <?php if (empty($cart_items)): ?>
                <div class="alert alert-info text-center">
                    <?= icon('shopping-cart', 'icon-3x mb-3 text-muted') ?>
                    <h4>Seu carrinho está vazio</h4>
                    <p class="mb-3">Adicione alguns produtos incríveis à sua coleção!</p>
                    <a href="<?= $base ?>index.php" class="btn btn-custom">
                        <?= icon('shopping-bag', 'icon me-2') ?>Continuar Comprando
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="row align-items-center border-bottom py-3" 
                                         data-product-id="<?= (int)$item['id'] ?>" 
                                         data-product-size="<?= htmlspecialchars($item['size']) ?>"
                                         data-product-price="<?= $item['price'] ?>">
                                        <div class="col-6 col-md-2">
                                            <img src="<?= $base ?>product-image.php?id=<?= (int)$item['id'] ?>" 
                                                 alt="<?= htmlspecialchars($item['title']) ?>" 
                                                 class="img-fluid rounded"
                                                 style="max-height: 100px; object-fit: cover;">
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                                            <small class="text-muted">Tamanho: <?= htmlspecialchars($item['size']) ?></small>
                                            <div class="d-md-none mt-2">
                                                <strong>R$ <span class="item-subtotal"><?= number_format($item['subtotal'], 2, ',', '.') ?></span></strong>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-2 mt-2 mt-md-0">
                                            <label class="form-label d-md-none small">Quantidade:</label>
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-secondary btn-decrease" type="button">-</button>
                                                <input type="number" class="form-control text-center quantity-input" 
                                                       value="<?= (int)$item['quantity'] ?>" min="1" max="10" readonly>
                                                <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
                                            </div>
                                        </div>
                                        <div class="col-4 col-md-2 text-center d-none d-md-block">
                                            <strong>R$ <span class="item-subtotal"><?= number_format($item['subtotal'], 2, ',', '.') ?></span></strong>
                                        </div>
                                        <div class="col-6 col-md-2 text-center mt-2 mt-md-0">
                                            <button class="btn btn-sm btn-outline-danger btn-remove w-100 w-md-auto">
                                                <?= icon('trash', 'icon me-1') ?><span class="d-md-none">Remover</span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Resumo do Pedido</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>R$ <span id="cart-total"><?= number_format($total, 2, ',', '.') ?></span></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Frete:</span>
                                    <span class="text-muted">Calculado no próximo passo</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong>R$ <span id="cart-final-total"><?= number_format($total, 2, ',', '.') ?></span></strong>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="endereco.php" class="btn btn-custom">
                                        <?= icon('arrow-right', 'icon me-2') ?>Finalizar Compra
                                    </a>
                                    <a href="<?= $base ?>index.php" class="btn btn-outline-secondary">
                                        <?= icon('arrow-left', 'icon me-2') ?>Continuar Comprando
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <script>
    // Funcionalidade do carrinho na página
    document.addEventListener('DOMContentLoaded', function() {
        // Atualizar quantidade
        document.querySelectorAll('.btn-decrease, .btn-increase').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('[data-product-id]');
                const productId = parseInt(row.dataset.productId);
                const productSize = row.dataset.productSize;
                const quantityInput = row.querySelector('.quantity-input');
                const isIncrease = this.classList.contains('btn-increase');
                
                let newQuantity = parseInt(quantityInput.value);
                newQuantity = isIncrease ? Math.min(10, newQuantity + 1) : Math.max(1, newQuantity - 1);
                
                quantityInput.value = newQuantity;
                updateCartItem(productId, productSize, newQuantity, row);
            });
        });
        
        // Remover item
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Remover este item do carrinho?')) {
                    const row = this.closest('[data-product-id]');
                    const productId = parseInt(row.dataset.productId);
                    const productSize = row.dataset.productSize;
                    removeCartItem(productId, productSize);
                }
            });
        });
    });
    
    function updateCartItem(productId, size, quantity, row) {
        fetch('<?= $base ?>cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                id: productId,
                size: size,
                qty: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar subtotal do item dinamicamente
                const price = parseFloat(row.dataset.productPrice);
                const subtotal = price * quantity;
                row.querySelector('.item-subtotal').textContent = subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                // Atualizar total geral
                updateTotals();
                
                // Atualizar contador do carrinho no header
                if (data.cart_count !== undefined) {
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) cartCount.textContent = data.cart_count;
                }
            } else {
                alert('Erro ao atualizar carrinho: ' + (data.message || 'Erro desconhecido'));
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar carrinho');
            location.reload();
        });
    }
    
    function removeCartItem(productId, size) {
        fetch('<?= $base ?>cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                id: productId,
                size: size
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao remover item: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao remover item');
        });
    }
    
    function updateTotals() {
        let total = 0;
        document.querySelectorAll('[data-product-id]').forEach(row => {
            const subtotalText = row.querySelector('.item-subtotal').textContent;
            const subtotal = parseFloat(subtotalText.replace('.', '').replace(',', '.'));
            total += subtotal;
        });
        
        document.getElementById('cart-total').textContent = total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('cart-final-total').textContent = total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    </script>
    
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
