<?php
// Include para cartão de produto na listagem
// Recebe variáveis: $productTitle, $productPrice, $productImage, $productLink, $cartLink
?>
<div class="product-card">
    <div class="product-image" style="width:100%; aspect-ratio:1/1; overflow:hidden;">
        <img src="<?php echo $productImage; ?>" alt="<?php echo $productTitle; ?>" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
    </div>
    <h3 class="product-title"><?php echo $productTitle; ?></h3>
    <p class="product-price"><?php echo $productPrice; ?></p>
    <a href="<?php echo $productLink; ?>" class="btn btn-custom">Ver Peça</a>
    <a href="<?php echo $cartLink ?? '#'; ?>" class="btn btn-custom">Carrinho</a>
</div>
