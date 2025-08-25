<?php
$pageTitle = 'Camiseta Fragmentos Boxy | Batrip';
include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php if (!@include '../../includes/cart-sidebar.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o carrinho lateral.</div>'; } ?>
    <div class="navbar-space"></div>
    
    <?php
    $productTitle = 'Camiseta Fragmentos Boxy';
    $productPrice = 'R$ 149,99';
    $productImage = '/Batrip/assets/img/fragmentado-costa.jpeg';
    $productImageAlt = 'Camiseta Fragmentos Boxy';
    include '../../includes/product-template.php';
    ?>
    
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
<?php $pageTitle = 'Camiseta Fragmentos Boxy | Batrip'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="/Batrip/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/Batrip/assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
// ...navbar agora é incluída via nav.php
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- ...conteúdo da página camiseta fragmentos boxy... -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
