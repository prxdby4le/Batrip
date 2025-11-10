<?php
/**
 * View: Products/Show
 * Detalhes do produto
 */

$product = $product ?? [];
$relatedProducts = $relatedProducts ?? [];
?>

<!-- Produto -->
<section class="product-detail" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <?php if (!empty($product)): ?>
            <div class="row">
                <!-- Imagem do Produto -->
                <div class="col-md-6 mb-4">
                    <div class="product-detail-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo BASE_URL; ?>product-image.php?id=<?php echo $product['id']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="img-fluid rounded"
                                 style="width: 100%; max-height: 600px; object-fit: cover;">
                        <?php else: ?>
                            <img src="<?php echo ASSETS_URL; ?>img/placeholder.svg" 
                                 alt="Produto" 
                                 class="img-fluid rounded">
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informações do Produto -->
                <div class="col-md-6">
                    <h1 class="product-detail-title mb-3">
                        <?php echo htmlspecialchars($product['title']); ?>
                    </h1>
                    
                    <div class="product-detail-price mb-4">
                        <h2 class="text-primary">
                            R$ <?php echo number_format($product['price'], 2, ',', '.'); ?>
                        </h2>
                    </div>
                    
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-detail-description mb-4">
                            <h5>Descrição</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulário de Compra -->
                        <form id="addToCartForm" class="mb-4" method="POST" action="<?php echo BASE_URL; ?>cart/add">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="product_title" value="<?php echo htmlspecialchars($product['title']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                        
                        <div class="mb-3">
                            <label for="size" class="form-label">Tamanho</label>
                            <select class="form-select" id="size" name="size" required>
                                <option value="">Selecione o tamanho</option>
                                <option value="PP">PP</option>
                                <option value="P">P</option>
                                <option value="M" selected>M</option>
                                <option value="G">G</option>
                                <option value="GG">GG</option>
                            </select>
                        </div>

                        <?php
                        // Exibir Tabela/Guia de Tamanhos vinda do Admin, se existir
                        $sizeTableHtml = '';
                        $possibleKeys = [
                            'size_table', 'size_table_html', 'size_chart', 'size_chart_html',
                            'tabela_tamanhos', 'guia_tamanhos', 'size_guide', 'sizeGuide',
                            'tabela_medidas', 'tabelaMedidas'
                        ];
                        foreach ($possibleKeys as $k) {
                            if (!empty($product[$k])) { $sizeTableHtml = (string)$product[$k]; break; }
                        }
                        // Alternativa: imagem de tabela de tamanho
                        $sizeTableImage = '';
                        foreach (['size_table_image','size_chart_image','tabela_tamanhos_imagem'] as $k) {
                            if (!empty($product[$k])) { $sizeTableImage = (string)$product[$k]; break; }
                        }

                        if ($sizeTableHtml || $sizeTableImage):
                            // Sanitização básica para evitar scripts/eventos
                            $clean = $sizeTableHtml;
                            if ($clean) {
                                // Remove tags perigosas
                                $clean = preg_replace('/<(script|style)[^>]*>.*?<\\/\1>/is', '', $clean);
                                // Remove atributos on*
                                $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
                                // Remove protocolos javascript:
                                $clean = preg_replace('/javascript\s*:/i', '', $clean);
                            }
                        ?>
                        <div class="mb-3">
                            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#sizeTableCollapse" aria-expanded="false" aria-controls="sizeTableCollapse">
                                Tabela de tamanhos
                            </button>
                            <div class="collapse mt-2" id="sizeTableCollapse">
                                <div class="card card-body bg-dark border-secondary">
                                    <?php if ($clean): ?>
                                        <div class="size-table-content">
                                            <?php // Conteúdo vindo do admin (sanitizado)
                                                echo $clean; 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($sizeTableImage): ?>
                                        <div class="text-center">
                                            <img src="<?php echo htmlspecialchars($sizeTableImage); ?>" alt="Tabela de tamanhos" class="img-fluid">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantidade</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   value="1" min="1" max="10" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-lg">
                                <i class="bi bi-cart-plus me-2"></i>
                                Adicionar ao Carrinho
                            </button>
                            <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-outline-light">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar aos Produtos
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Produtos Relacionados -->
            <?php if (!empty($relatedProducts)): ?>
                <section class="related-products mt-5 pt-5 border-top">
                    <h3 class="mb-4">Produtos Relacionados</h3>
                    <div class="row">
                        <?php foreach ($relatedProducts as $related): ?>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="product-card">
                                    <a href="<?php echo BASE_URL; ?>produto/<?php echo $related['id']; ?>" class="text-decoration-none">
                                        <div class="product-image-wrapper">
                                            <img src="<?php echo BASE_URL; ?>product-image.php?id=<?php echo $related['id']; ?>" 
                                                 alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                                 class="product-image">
                                        </div>
                                        <div class="product-info">
                                            <h5 class="product-title"><?php echo htmlspecialchars($related['title']); ?></h5>
                                            <p class="product-price">R$ <?php echo number_format($related['price'], 2, ',', '.'); ?></p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <h3>Produto não encontrado</h3>
                <p class="text-muted">O produto que você procura não existe ou foi removido.</p>
                <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom mt-3">
                    Ver Todos os Produtos
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Script para adicionar ao carrinho -->
<script>
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const size = document.getElementById('size').value || 'M';
    const qty = parseInt(document.getElementById('quantity').value || '1', 10);
    const id = parseInt(this.querySelector('input[name="product_id"]').value, 10);

    fetch('<?php echo BASE_URL; ?>cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ id, size, qty })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const c1 = document.getElementById('cart-count');
            const c2 = document.getElementById('sidebar-cart-count');
            if (c1) c1.textContent = data.cart_count;
            if (c2) c2.textContent = data.cart_count;
            const cartSidebarEl = document.getElementById('cartSidebar');
            if (cartSidebarEl && window.bootstrap && bootstrap.Offcanvas) {
                const cartSidebar = new bootstrap.Offcanvas(cartSidebarEl);
                cartSidebar.show();
            } else {
                alert('Produto adicionado ao carrinho!');
            }
        } else {
            alert(data.message || 'Erro ao adicionar produto');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao adicionar produto ao carrinho');
    });
});
</script>
