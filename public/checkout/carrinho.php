<?php 
require_once __DIR__ . '/../../includes/auth.php'; 
require_login(); 
$pageTitle = 'Carrinho | Batrip';
include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h2>
            <div class="row">
                <div class="col-lg-8 order-lg-1 order-2">
                    <div class="card mb-3">
                        <div class="card-body p-0">
                            <h5 class="fw-bold mb-3 px-3 pt-3">Itens do Pedido</h5>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="cart-items-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th></th>
                                            <th>Produto</th>
                                            <th>Tamanho</th>
                                            <th>Qtd</th>
                                            <th class="text-end">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-items-container"></tbody>
                                </table>
                            </div>
                            <div id="cart-empty-message" class="alert alert-info text-center mt-4 d-none">Seu carrinho está vazio.</div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body" id="cart-summary"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>