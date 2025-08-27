<?php 
$pageTitle = 'Camiseta Spiderweb Oversized | Batrip';
include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php if (!@include '../../includes/cart-sidebar.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o carrinho lateral.</div>'; } ?>
    <div class="navbar-space"></div>
    <section class="section produto-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="product-image-store">
                        <img src="assets/img/spiderweb-oversized.jpeg" alt="Camiseta Spiderweb Oversized" class="img-fluid rounded product-img-store">
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="product-title mb-2">Camiseta Spiderweb Oversized</h2>
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
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

