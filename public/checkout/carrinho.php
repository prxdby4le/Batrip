<?php $pageTitle = 'Carrinho | Batrip'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="../assets/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../includes/nav.php'; ?>
<body>
    <?php include '../includes/cart-sidebar.php'; ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
<?php
$pageTitle = 'Carrinho | Batrip';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="/assets/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../includes/nav.php'; ?>
<body>
    <!-- Header -->
// ...navbar agora é incluída via nav.php
    <?php include '../includes/cart-sidebar.php'; ?>
    <div style="height: 70px;"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h2>
            <div class="row">
                <div class="col-lg-8 order-lg-1 order-2">
                    <!-- Itens do carrinho e resumo serão renderizados dinamicamente -->
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
                <!-- Removido o resumo do pedido nesta página para servir apenas para finalização -->
            </div>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
