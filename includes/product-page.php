<?php
// Template para página de produto individual
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
                <div class="product-image-store">
                    <img src="<?php echo $productImage; ?>" alt="<?php echo $productTitle; ?>" class="img-fluid rounded product-img-store">
                </div>
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
