<?php
// Parâmetros esperados: $productTitle, $productPrice, $productImage, $productLink, $cartLink
require_once __DIR__ . '/auth.php';
$baseForm = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';
$formAction = $baseForm . 'cart.php';
$priceNumeric = 0.0;
if (isset($productPrice)) {
    $priceSan = str_replace(['R$', ' ', '.'], '', (string)$productPrice);
    $priceSan = str_replace(',', '.', $priceSan);
    $priceNumeric = is_numeric($priceSan) ? (float)$priceSan : 0.0;
}
$showQtyControl = isset($showQtyControl) ? (bool)$showQtyControl : true;
$showSizeControl = isset($showSizeControl) ? (bool)$showSizeControl : true;
$buyButtonLabel = isset($buyButtonLabel) && is_string($buyButtonLabel) && $buyButtonLabel !== '' ? $buyButtonLabel : 'Comprar';
?>
<div class="product-card">
	<a href="<?= htmlspecialchars($productLink) ?>" class="product-image d-block">
		<img src="<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
	</a>
	<h3 class="product-title"><?= htmlspecialchars($productTitle) ?></h3>
	<p class="product-price"><?= htmlspecialchars($productPrice) ?></p>
	<div class="d-flex gap-2 align-items-stretch">
		<a href="<?= htmlspecialchars($productLink) ?>" class="btn btn-outline-light w-50">Ver</a>
		<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="w-50 d-flex gap-1 align-items-center justify-content-end">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
			<input type="hidden" name="action" value="add">
			<input type="hidden" name="title" value="<?= htmlspecialchars($productTitle) ?>">
			<input type="hidden" name="price" value="<?= htmlspecialchars(number_format($priceNumeric, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>">
			<input type="hidden" name="img" value="<?= htmlspecialchars($productImage) ?>">
			<?php if ($showSizeControl): ?>
				<label for="size-<?= md5($productTitle . $productImage) ?>" class="visualmente-hidden visually-hidden">Tamanho</label>
				<select name="size" id="size-<?= md5($productTitle . $productImage) ?>" class="form-select form-select-sm" style="width:auto; min-width:84px;">
					<option value="P">P</option>
					<option value="M" selected>M</option>
					<option value="G">G</option>
					<option value="GG">GG</option>
				</select>
			<?php else: ?>
				<input type="hidden" name="size" value="M">
			<?php endif; ?>
			<label for="qty-<?= md5($productTitle . $productImage) ?>" class="visually-hidden">Quantidade</label>
			<?php if ($showQtyControl): ?>
				<div class="input-group input-group-sm card-qty-group" style="width: 120px;">
					<button class="btn btn-outline-secondary card-qty-dec" type="button" aria-label="Diminuir">-</button>
					<input type="number" id="qty-<?= md5($productTitle . $productImage) ?>" name="qty" class="form-control text-center card-qty-input" value="1" min="1">
					<button class="btn btn-outline-secondary card-qty-inc" type="button" aria-label="Aumentar">+</button>
				</div>
			<?php else: ?>
				<input type="hidden" name="qty" value="1">
			<?php endif; ?>
			<input type="hidden" name="open_cart" value="1">
			<input type="hidden" name="redirect" value="stay">
			<button type="submit" class="btn btn-custom w-100"><?php echo htmlspecialchars($buyButtonLabel); ?></button>
		</form>
	</div>
</div>
