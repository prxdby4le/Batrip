<?php
$pageTitle = 'Carrinho | Batrip';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/cart-functions.php';

$cart = get_cart();
$cart_items = [];
$total = 0;

if (!empty($cart)) {
    try {
        $product_ids = array_keys($cart);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, title, price, image FROM products WHERE id IN ($placeholders) AND active = 1");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $product) {
            $quantity = $cart[$product['id']]['quantity'];
            $size = $cart[$product['id']]['size'];
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            
            $cart_items[] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity,
                'size' => $size,
                'subtotal' => $subtotal
            ];
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar produtos do carrinho: " . $e->getMessage());
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
            <h2 class="section-title mb-4"><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h2>
            
            <?php if (empty($cart_items)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                    <h4>Seu carrinho está vazio</h4>
                    <p class="mb-3">Adicione alguns produtos incríveis à sua coleção!</p>
                    <a href="<?= $base ?>index.php" class="btn btn-custom">
                        <i class="fas fa-shopping-bag me-2"></i>Continuar Comprando
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="row align-items-center border-bottom py-3" data-product-id="<?= $item['id'] ?>">
                                        <div class="col-6 col-md-2">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['title']) ?>" 
                                                 class="img-fluid rounded">
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
                                                       value="<?= $item['quantity'] ?>" min="1" max="10">
                                                <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
                                            </div>
                                        </div>
                                        <div class="col-4 col-md-2 text-center d-none d-md-block">
                                            <strong>R$ <span class="item-subtotal"><?= number_format($item['subtotal'], 2, ',', '.') ?></span></strong>
                                        </div>
                                        <div class="col-6 col-md-2 text-center mt-2 mt-md-0">
                                            <button class="btn btn-sm btn-outline-danger btn-remove w-100 w-md-auto" 
                                                    data-product-id="<?= $item['id'] ?>">
                                                <i class="fas fa-trash me-1"></i><span class="d-md-none">Remover</span>
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
                                        <i class="fas fa-arrow-right me-2"></i>Finalizar Compra
                                    </a>
                                    <a href="<?= $base ?>index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Continuar Comprando
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
                const productId = row.dataset.productId;
                const quantityInput = row.querySelector('.quantity-input');
                const isIncrease = this.classList.contains('btn-increase');
                
                let newQuantity = parseInt(quantityInput.value);
                newQuantity = isIncrease ? newQuantity + 1 : Math.max(1, newQuantity - 1);
                
                updateCartItem(productId, newQuantity);
            });
        });
        
        // Input manual de quantidade
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('[data-product-id]');
                const productId = row.dataset.productId;
                const newQuantity = Math.max(1, Math.min(10, parseInt(this.value) || 1));
                
                this.value = newQuantity;
                updateCartItem(productId, newQuantity);
            });
        });
        
        // Remover item
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Remover este item do carrinho?')) {
                    const productId = this.dataset.productId;
                    removeCartItem(productId);
                }
            });
        });
    });
    
    function updateCartItem(productId, quantity) {
        fetch('<?= $base ?>cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Recarrega para atualizar totais
            } else {
                alert('Erro ao atualizar carrinho: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar carrinho');
        });
    }
    
    function removeCartItem(productId) {
        fetch('<?= $base ?>cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
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
    </script>
    
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
