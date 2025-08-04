<?php $pageTitle = 'Camiseta Fragmentos Boxy | Batrip'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="../../assets/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php if (!@include '../../includes/cart-sidebar.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o carrinho lateral.</div>'; } ?>
    <div class="navbar-space"></div>
    <section class="section produto-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="product-image-store">
                        <img src="../../assets/img/fragmentado-costa.jpeg" alt="Camiseta Fragmentos Boxy" class="img-fluid rounded product-img-store">
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="product-title mb-2">Camiseta Fragmentos Boxy</h2>
                    <p class="product-price mb-2">R$ 149,99</p>
                    <p class="product-desc">
                        Do Opium ao Streetwear, a Batrip explora textura e aspectos musicais em forma de moda.
                        A fusão do mundo punk e rock com as ruas criando uma passarela alternativa e agressiva.
                    </p>
                    <form>
                        <div class="mb-3">
                            <label for="tamanho" class="form-label">Tamanho</label>
                            <select class="form-select" id="tamanho">
                                <option>P</option>
                                <option>M</option>
                                <option>G</option>
                                <option>GG</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" class="form-control" id="quantidade" value="1" min="1">
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Adicionar ao Carrinho</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php if (!@include '../../includes/footer.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o rodapé.</div>'; } ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
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
    <!-- ...conteúdo da página camiseta fragmentos boxy... -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
