<?php $pageTitle = 'Revisão do Pedido | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-clipboard-check"></i> Revisão do Pedido</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Endereço de Entrega</h5>
                    <div id="resumo-endereco">
                        Rua Exemplo, 123<br>
                        Bairro Exemplo - Cidade/UF<br>
                        CEP: 00000-000
                    </div>
                    <a href="endereco.php" class="btn btn-link p-0 mt-2">Editar endereço</a>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Itens do Pedido</h5>
                    <div class="text-muted">Seu carrinho está vazio.</div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Frete</h5>
                    <div class="text-muted">Opção: SEDEX • Valor: R$ 0,00</div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body d-flex justify-content-end">
                    <div class="text-end">
                        <div>Subtotal: <strong>R$ 0,00</strong></div>
                        <div>Frete: <strong>R$ 0,00</strong></div>
                        <div class="fs-5 mt-2">Total: <strong>R$ 0,00</strong></div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="checkout/pagamento.php" class="btn btn-custom">Ir para Pagamento</a>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>


