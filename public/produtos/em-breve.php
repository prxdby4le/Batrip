<?php $pageTitle = 'Em Breve | Batrip'; ?>
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
    <?php if (!@include '../includes/cart-sidebar.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o carrinho lateral.</div>'; } ?>
    <div class="navbar-space"></div>
    <section class="section produto-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <h2 class="product-title mb-2">Em breve</h2>
                    <p class="product-desc mb-4">Novos produtos e coleções estão chegando. Fique ligado!</p>
                    <img src="../assets/img/fragmentado-frente.jpeg" alt="Em breve" class="img-fluid rounded product-img-store" style="max-width: 350px;">
                </div>
            </div>
        </div>
    </section>
    <?php if (!@include '../includes/footer.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o rodapé.</div>'; } ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
<?php $pageTitle = 'Em Breve | Batrip'; ?>
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
// ...navbar agora é incluída via nav.php
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- ...conteúdo da página em breve... -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
