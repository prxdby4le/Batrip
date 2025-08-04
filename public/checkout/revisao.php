<?php $pageTitle = 'Revisão | Checkout | Batrip'; ?>
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
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section">
        <div class="container">
            <h2 class="section-title mb-4">Revisão do Pedido</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Itens do Pedido</h5>
                    <div id="review-items"></div>
                </div>
            </div>
            <form>
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentário</label>
                    <textarea class="form-control" id="comentario" rows="3" placeholder="Alguma observação?"></textarea>
                </div>
                <button type="submit" class="btn btn-custom w-100">Finalizar Pedido</button>
            </form>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
<?php
$pageTitle = 'Revisão do Pedido | Batrip';
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
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div style="height: 70px;"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-clipboard-check"></i> Revisão do Pedido</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Endereço de Entrega</h5>
                    <div id="resumo-endereco">Preencha o endereço para ver o resumo.</div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Itens do Pedido</h5>
                    <div id="resumo-itens">Itens do carrinho aqui.</div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Frete</h5>
                    <div id="resumo-frete">Selecione o frete para ver o resumo.</div>
                </div>
            </div>
            <div class="text-end">
                <a href="pagamento.php" class="btn btn-custom">Ir para Pagamento</a>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
