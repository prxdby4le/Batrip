<?php 
$pageTitle = 'Em Breve | Batrip';
require_once __DIR__ . '/../../includes/head.php';
?>
<body>
    <?php require_once __DIR__ . '/../../includes/nav.php'; ?>
    <?php require_once __DIR__ . '/../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section produto-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <h2 class="product-title mb-2">Em breve</h2>
                    <p class="product-desc mb-4">Novos produtos e coleções estão chegando. Fique ligado!</p>
                    <a href="../checkout/carrinho.php" class="btn btn-custom w-100 mt-4">Ir para o Carrinho</a>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

