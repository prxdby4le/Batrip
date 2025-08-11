<?php
// Página principal do carrinho em PHP
include_once __DIR__ . '/../includes/cart-functions.php';
session_start();

// Processa remoção de item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_title'], $_POST['remove_size'])) {
    remove_from_cart($_POST['remove_title'], $_POST['remove_size']);
    header('Location: carrinho.php');
    exit;
}

$cart = get_cart();
$cep = get_user_cep();
$frete = calcular_frete($cep);
$subtotal = get_cart_subtotal();
$total = $subtotal + $frete;
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho | Batrip</title>
    <link rel="icon" href="/Batrip/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/Batrip/assets/css/styles.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
    <div class="container">
        <h2 class="section-title mb-4">Seu Carrinho</h2>
        <?php if (empty($cart)): ?>
            <div class="alert alert-info text-center mt-4">Seu carrinho está vazio.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Produto</th>
                            <th>Tamanho</th>
                            <th>Qtd</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                        <tr>
                            <td><img src="<?php echo (strpos($item['img'], '/Batrip/assets/img/') === 0 ? htmlspecialchars($item['img']) : '/Batrip/assets/img/' . ltrim(htmlspecialchars($item['img']), '/')); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;"></td>
                            <td><div class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></div></td>
                            <td><?php echo htmlspecialchars($item['size']); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td class="text-end">R$ <?php echo number_format($item['price'] * $item['qty'], 2, ',', '.'); ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="remove_title" value="<?php echo htmlspecialchars($item['title']); ?>">
                                    <input type="hidden" name="remove_size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                    <button class="btn btn-sm btn-link text-danger" type="submit"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card mb-3">
                <div class="card-body" id="cart-summary">
                    <h5 class="fw-bold mb-3">Resumo do Pedido</h5>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span></div>
                    <div class="d-flex justify-content-between"><span>Frete</span><span>R$ <?php echo number_format($frete, 2, ',', '.'); ?></span></div>
                    <hr><div class="d-flex justify-content-between fw-bold"><span>Total</span><span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span></div>
                    <div class="text-muted small">CEP: <?php echo htmlspecialchars($cep); ?></div>
                    <a href="#" class="btn btn-custom w-100 mt-3">Finalizar Compra</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
