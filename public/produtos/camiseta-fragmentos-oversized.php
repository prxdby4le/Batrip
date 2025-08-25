<?php 
$pageTitle = 'Camiseta Fragmentado Oversized | Batrip';
include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    
    <?php
    $productTitle = 'Camiseta Fragmentado Oversized';
    $productPrice = 'R$ 149,99';
    $productImage = '/Batrip/assets/img/fragmentado-frente.jpeg';
    include '../../includes/product-page.php';
    ?>
    
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
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
    <script src="../assets/js/bootstrap-js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
<?php $pageTitle = 'Camiseta Fragmentos Oversized | Batrip'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="/Batrip/materials/batrip symbol.png" type="image/x-icon">
    <link href="../assets/css/bootstrap-css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/Batrip/assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
// ...navbar agora é incluída via nav.php
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- ...conteúdo da página camiseta fragmentos oversized... -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>

