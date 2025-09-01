<?php $pageTitle = 'Pagamento | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-credit-card"></i> Pagamento</h2>
            <div class="alert alert-info">O pagamento será processado por um serviço externo seguro. Para fins de teste, clique em Finalizar Pedido para simular a confirmação.</div>
            <a href="checkout/finalizar.php" class="btn btn-success btn-lg w-100 mb-3">Finalizar Pedido (Simulação)</a>
            <div class="d-grid gap-2">
                <button class="btn btn-outline-primary btn-lg" type="button" disabled>Pagar com Pix (em breve)</button>
                <button class="btn btn-outline-secondary btn-lg" type="button" disabled>Pagar com Mercado Pago (em breve)</button>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

