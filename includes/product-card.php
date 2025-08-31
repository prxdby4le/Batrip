<?php
// Parâmetros esperados: $productTitle, $productPrice, $productImage, $productLink, $cartLink
?>
<div class="product-card">
	<a href="<?= htmlspecialchars($productLink) ?>" class="product-image d-block">
		<img src="<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
	</a>
	<h3 class="product-title"><?= htmlspecialchars($productTitle) ?></h3>
	<p class="product-price"><?= htmlspecialchars($productPrice) ?></p>
	<div class="d-flex gap-2">
		<a href="<?= htmlspecialchars($productLink) ?>" class="btn btn-outline-light w-50">Ver</a>
		<a href="<?= htmlspecialchars($cartLink ?: '#') ?>" class="btn btn-custom w-50">Comprar</a>
	</div>
</div>
