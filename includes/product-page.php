<?php
// Template para página de produto individual
// Variáveis necessárias: $productTitle, $productPrice, $productImage, $productDescription (opcional)
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
                <form>
                    <div class="mb-3">
                        <label for="tamanho" class="form-label">Tamanho</label>
                        <select class="form-select" id="tamanho">
                            <option>P</option>
                            <option>M</option>
                            <option>G</option>
                            <option>GG</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" value="1" min="1">
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Adicionar ao Carrinho</button>
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
