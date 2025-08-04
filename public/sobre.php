<?php $pageTitle = 'Sobre a Batrip'; ?>
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
    <section class="section pt-5 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Sobre a Batrip</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <p style="color: var(--text-gray); font-size: 1.1rem;">
                        A Batrip nasceu da fusão entre música, moda e cultura alternativa. Nossa missão é criar peças exclusivas que expressem autenticidade e atitude, conectando artistas e fãs em uma só vibe.
                    </p>
                    <ul>
                        <li>Exclusividade</li>
                        <li>Sonoridade</li>
                        <li>Autenticidade</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="../assets/materials/batrip png branco.png" alt="Batrip Logo" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
<?php $pageTitle = 'Sobre | Batrip'; ?>
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
    <!-- Header e barra lateral do carrinho -->
// ...navbar agora é incluída via nav.php
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- ...conteúdo da página sobre... -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
