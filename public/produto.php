<?php
$pageTitle = 'Produto | Batrip';
include '../includes/head.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: index.php'); exit; }

$productTitle = $p['title'];
$productPrice = 'R$ ' . number_format((float)$p['price'], 2, ',', '.');
$productImage = $p['image'];
$productDescription = $p['description'];
$productSizes = array_map('trim', explode(',', $p['sizes'] ?: 'P,M,G,GG'));
?>
<body>
<?php include '../includes/nav.php'; ?>
<?php include '../includes/cart-sidebar.php'; ?>
<?php include '../includes/product-page.php'; ?>
<?php include '../includes/footer.php'; ?>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
