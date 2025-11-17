<?php
$pageTitle = 'Produto | Batrip';
include '../includes/head.php';
require_once __DIR__ . '/../includes/legacy-redirect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redireciona para rota limpa (opcional via LEGACY_REDIRECTS=1)
if ($id > 0) {
	legacy_redirect_if_enabled('produto/' . $id);
}

if ($id <= 0) {
	http_response_code(404);
	include '../includes/nav.php';
	$bh = $GLOBALS['baseHref'] ?? '/';
	echo '<div class="navbar-space"></div><div class="container py-5"><h1 class="section-title">Produto não encontrado</h1><p>O item solicitado não existe.</p><p><a href="'. htmlspecialchars($bh) .'index.php" class="btn btn-custom">Voltar</a></p></div>';
	include '../includes/footer.php';
	include '../includes/scripts.php';
	echo '</body></html>';
	exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
	http_response_code(404);
	include '../includes/nav.php';
	$bh = $GLOBALS['baseHref'] ?? '/';
	echo '<div class="navbar-space"></div><div class="container py-5"><h1 class="section-title">Produto não encontrado</h1><p>O item solicitado pode ter sido removido ou está indisponível.</p><p><a href="'. htmlspecialchars($bh) .'index.php" class="btn btn-custom">Voltar para a loja</a></p></div>';
	include '../includes/footer.php';
	include '../includes/scripts.php';
	echo '</body></html>';
	exit;
}

$productTitle = $p['title'] ?? 'Produto';
$productPrice = 'R$ ' . number_format((float)($p['price'] ?? 0), 2, ',', '.');
$productImage = $p['id']; // Usar endpoint product-image.php?id=<id> para servir imagem do banco
$productDescription = $p['description'] ?? '';
$productSizes = array_map('trim', explode(',', (!empty($p['sizes']) ? $p['sizes'] : 'P,M,G,GG')));
$productSizeChart = [];
if (!empty($p['size_chart'])) {
	$dec = json_decode((string)$p['size_chart'], true);
	if (is_array($dec)) { $productSizeChart = $dec; }
}

// Buscar imagens extras para galeria (se houver)
$productImages = [];
try {
	$stmt = $pdo->prepare('SELECT url FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, position ASC, id ASC');
	$stmt->execute([$id]);
	$urls = $stmt->fetchAll(PDO::FETCH_COLUMN);
	if (!empty($urls)) {
		// Mapear para endpoints com idx
		foreach (array_values($urls) as $i => $u) {
			$productImages[] = ($GLOBALS['baseHref'] ?? '/') . 'product-image.php?id=' . (int)$id . '&idx=' . (int)$i;
		}
	}
} catch (Throwable $e) {
	// Sem galeria extra; segue com imagem única
}
?>
<body>
<?php include '../includes/nav.php'; ?>
<?php include '../includes/cart-sidebar.php'; ?>
<?php include '../includes/product-page.php'; ?>
<?php if (!empty($productSizeChart)): ?>
<section class="section pt-0">
	<div class="container">
		<h3 class="section-title">Tabela de Medidas (cm)</h3>
		<div class="table-responsive">
			<table class="table table-dark table-striped align-middle w-auto">
				<thead>
					<tr>
						<th>Tamanho</th>
						<th>Peito</th>
						<th>Comprimento</th>
						<th>Ombro</th>
						<th>Manga</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($productSizeChart as $row): ?>
						<tr>
							<td><?= htmlspecialchars((string)($row['size'] ?? '')) ?></td>
							<td><?= htmlspecialchars((string)($row['bust_cm'] ?? '')) ?></td>
							<td><?= htmlspecialchars((string)($row['length_cm'] ?? '')) ?></td>
							<td><?= htmlspecialchars((string)($row['shoulder_cm'] ?? '')) ?></td>
							<td><?= htmlspecialchars((string)($row['sleeve_cm'] ?? '')) ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	</section>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
