<?php require_once __DIR__ . '/../../includes/auth.php'; require_login(); ?>
<?php $pageTitle = 'Pagamento | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-credit-card"></i> Pagamento</h2>
            <div class="alert alert-info">O pagamento será processado por um serviço externo seguro. Clique no botão abaixo para continuar.</div>
            <a href="#" class="btn btn-success btn-lg w-100 mb-3">Pagar com Mercado Pago</a>
            <a href="#" class="btn btn-outline-primary btn-lg w-100">Pagar com Pix</a>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

