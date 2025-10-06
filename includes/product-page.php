<?php
// Template para página                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                         alt="<?php echo htmlspecialchars($productTitle); ?>" 
                         class="img-fluid rounded product-img-store"
                         onerror="this.src='assets/img/placeholder.svg'">produto individual
// Variáveis necessárias: $productTitle, $productPrice, $productImage, $productDescription (opcional)
// Gera ação do formulário relativa ao local atual (public/ vs public/produtos/)
// e converte preço string (ex: "R$ 149,99") para float
require_once __DIR__ . '/auth.php';
$baseForm = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';
$formAction = $baseForm . 'cart.php';
$priceNumeric = 0.0;
if (isset($productPrice)) {
    $priceSan = str_replace(['R$', ' ', '.'], '', (string)$productPrice);
    $priceSan = str_replace(',', '.', $priceSan);
    $priceNumeric = is_numeric($priceSan) ? (float)$priceSan : 0.0;
}
?>
<div class="navbar-space"></div>
<section class="section produto-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <!-- Galeria de imagens -->
                <div class="product-image-store mb-2">
                    <?php 
                    $imgSrc = is_numeric($productImage) 
                        ? 'product-image.php?id=' . (int)$productImage 
                        : (strpos($productImage, 'http') === 0 || strpos($productImage, 'assets/') === 0 
                            ? $productImage 
                            : 'product-image.php?id=' . (isset($p) ? (int)$p['id'] : 0));
                    ?>
                    <img id="mainProductImage" 
                         src="<?php echo htmlspecialchars($imgSrc); ?>" 
                         alt="<?php echo htmlspecialchars($productTitle); ?>" 
                         class="img-fluid rounded product-img-store"
                         onerror="this.src='assets/img/placeholder.png'">
                </div>
                
                <!-- Miniaturas -->
                <div id="productThumbnails" class="d-flex gap-2 overflow-auto pb-2">
                    <!-- Miniaturas serão carregadas via JavaScript -->
                </div>
                
                <script>
                // Carregar todas as imagens do produto
                <?php if (is_numeric($productImage)): ?>
                fetch('product-image.php?id=<?= (int)$productImage ?>&all=1')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.images && data.images.length > 1) {
                            const container = document.getElementById('productThumbnails');
                            const mainImg = document.getElementById('mainProductImage');
                            
                            data.images.forEach((img, index) => {
                                const thumb = document.createElement('img');
                                thumb.src = img.url;
                                thumb.className = 'img-thumbnail' + (index === 0 ? ' border-primary' : '');
                                thumb.style.width = '80px';
                                thumb.style.height = '80px';
                                thumb.style.objectFit = 'cover';
                                thumb.style.cursor = 'pointer';
                                thumb.onclick = function() {
                                    mainImg.src = img.url;
                                    container.querySelectorAll('img').forEach(t => t.classList.remove('border-primary'));
                                    thumb.classList.add('border-primary');
                                };
                                container.appendChild(thumb);
                            });
                        }
                    })
                    .catch(err => console.error('Erro ao carregar imagens:', err));
                <?php endif; ?>
                </script>
            </div>
            <div class="col-md-6">
                <h2 class="product-title mb-2"><?php echo $productTitle; ?></h2>
                <p class="product-price mb-2"><?php echo $productPrice; ?></p>
                <p class="product-desc">
                    <?php echo $productDescription ?? 'Do Opium ao Streetwear, a Batrip explora textura e aspectos musicais em forma de moda. A fusão do mundo punk e rock com as ruas criando uma passarela alternativa e agressiva.'; ?>
                </p>
                <form method="post" action="<?php echo htmlspecialchars($formAction); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($productTitle); ?>">
                    <input type="hidden" name="price" value="<?php echo htmlspecialchars(number_format($priceNumeric, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="img" value="<?php echo htmlspecialchars($productImage); ?>">
                    <input type="hidden" name="redirect" value="cart">
                    <div class="mb-3">
                        <label for="tamanho" class="form-label">Tamanho</label>
                        <select class="form-select" id="tamanho" name="size" required>
                            <?php
                            $sizes = isset($productSizes) && is_array($productSizes) && count($productSizes) > 0
                                ? $productSizes
                                : ['P','M','G','GG'];
                            foreach ($sizes as $sz):
                                $sz = trim((string)$sz);
                                if ($sz === '') continue;
                            ?>
                                <option value="<?php echo htmlspecialchars($sz); ?>"><?php echo htmlspecialchars($sz); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <div class="input-group product-qty-group" style="max-width: 200px;">
                            <button class="btn btn-outline-secondary product-qty-dec" type="button" aria-label="Diminuir">-</button>
                            <input type="number" class="form-control text-center product-qty-input" id="quantidade" name="qty" value="1" min="1" required>
                            <button class="btn btn-outline-secondary product-qty-inc" type="button" aria-label="Aumentar">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Comprar</button>
                </form>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="section-title">Produtos Relacionados</h3>
                <div class="row">
                    <!-- Produtos relacionados podem ser inseridos aqui -->
                </div>
            </div>
        </div>
    </div>
</section>
