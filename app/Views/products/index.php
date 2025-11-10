<?php
/**
 * View: Products/Index
 * Listagem de produtos
 */
?>

<!-- Header da Página -->
<section class="page-header" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <h1 class="text-center">Nossos Produtos</h1>
        <p class="text-center text-muted">Explore nossa coleção completa</p>
    </div>
</section>

<!-- Produtos -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($products)): ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="product-card">
                            <a href="<?php echo BASE_URL; ?>produto/<?php echo $product['id']; ?>" class="text-decoration-none">
                                <div class="product-image-wrapper">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>product-image.php?id=<?php echo $product['id']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                             class="product-image">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_URL; ?>img/placeholder.svg" 
                                             alt="Produto" 
                                             class="product-image">
                                    <?php endif; ?>
                                    
                                    <?php if (isset($product['active']) && $product['active'] == 0): ?>
                                        <div class="badge bg-danger position-absolute top-0 end-0 m-2">
                                            Indisponível
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    
                                    <?php if (!empty($product['description'])): ?>
                                        <p class="product-description text-muted small">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>
                                            <?php echo strlen($product['description']) > 80 ? '...' : ''; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="product-price">
                                        R$ <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: var(--text-gray);"></i>
                <h4 class="mt-3">Nenhum produto disponível</h4>
                <p class="text-muted">Novos produtos serão adicionados em breve!</p>
            </div>
        <?php endif; ?>
    </div>
</section>
