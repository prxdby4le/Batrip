<?php
$pageTitle = '-**¡not all bats are dead!**_';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="assets/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../includes/nav.php'; ?>
<body>
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">batrip</h1>
            <p class="hero-subtitle">Exclusividade • Sonoridade • Autenticidade</p>
        </div>
    </section>

    <!-- Lançamentos -->
    <section id="lancamentos" class="section">
        <div class="container">
            <h2 class="section-title">Lançamentos</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="product-card">
                        <div class="product-image" style="width:100%; aspect-ratio:1/1; overflow:hidden;">
                            <img src="assets/img/fragmentado-costa.jpeg" alt="Camiseta Fragmentado Boxy" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                        </div>
                        <h3 class="product-title">Camiseta Fragmentos Boxy</h3>
                        <p class="product-price">R$ 149,99</p>
                        <a href="produtos/camiseta-fragmentos-boxy.php" class="btn btn-custom">Ver Peça</a>
                        <a href="#" class="btn btn-custom">Carrinho</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card">
                        <div class="product-image" style="width:100%; aspect-ratio:1/1; overflow:hidden;">
                            <img src="assets/img/fragmentado-frente.jpeg" alt="Camiseta Fragmentado Oversized" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                        </div>
                        <h3 class="product-title">Camiseta Fragmentado Oversized</h3>
                        <p class="product-price">R$ 149,99</p>
                        <a href="produtos/camiseta-fragmentos-oversized.php" class="btn btn-custom">Ver Peça</a>
                        <a href="#" class="btn btn-custom">Carrinho</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card">
                        <div class="product-image" style="width:100%; aspect-ratio:1/1; overflow:hidden;">
                            <img src="assets/img/spiderweb-oversized.jpeg" alt="Camiseta Fragmentado Boxy" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                        </div>
                        <h3 class="product-title">Camiseta Spiderweb Oversized</h3>
                        <p class="product-price">R$ 149,99</p>
                        <a href="produtos/camiseta-spiderweb-oversized.php" class="btn btn-custom">Ver Peça</a>
                        <a href="#" class="btn btn-custom">Carrinho</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Conjuntos -->
    <section id="conjuntos" class="section">
        <div class="container">
            <h2 class="section-title">Conjuntos</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/img/fragmentado-frente.jpeg" alt="Camiseta Fragmentado Oversized" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                        </div>
                        <h3 class="product-title">Drop Fragmentado</h3>
                        <p style="color: var(--text-gray); margin-bottom: 1rem;">Oversized + Boxy</p>
                        <p class="product-price">R$ 270,00</p>
                        <a href="produtos/conjunto-fragmentado.php" class="btn btn-custom">Ver Conjunto</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product-card">
                        <div class="product-image">
                            <!-- <img src="../assets/img/fragmentado-frente.jpeg" alt="Camiseta Fragmentado Oversized" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;"> -->
                        </div>
                        <h3 class="product-title">Em breve</h3>
                        <p style="color: var(--text-gray); margin-bottom: 1rem;">??? + ??? + ???</p>
                        <p class="product-price">R$ ???,??</p>
                        <a href="produtos/em-breve.php" class="btn btn-custom">Ver Conjunto</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Artistas Parceiros -->
    <section id="artistas" class="section">
        <div class="container">
            <h2 class="section-title">No fone e na peita</h2>
            <div id="artistasCarousel" class="carousel slide" data-bs-ride="carousel">
                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#artistasCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#artistasCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#artistasCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <!-- Itens do Carrossel -->
                <div class="carousel-inner">
                    <!-- Primeiro grupo -->
                    <div class="carousel-item active">
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/chard-la-plaga.jpg">
                                    </div>
                                    <h3 class="artist-name">Chard la Plaga</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/link-do-zap.jpg">
                                    </div>
                                    <h3 class="artist-name">Link do Zap</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/ugovhb.jpg">
                                    </div>
                                    <h3 class="artist-name">Ugovhb</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Segundo grupo -->
                    <div class="carousel-item">
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/ef.jpg">
                                    </div>
                                    <h3 class="artist-name">EF</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/pradasoueu.jpg">
                                    </div>
                                    <h3 class="artist-name">pradasoueu</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/prxdby4le.jpg">
                                    </div>
                                    <h3 class="artist-name">prxdby4le</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Terceiro grupo -->
                    <div class="carousel-item">
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/thejoia.jpg">
                                    </div>
                                    <h3 class="artist-name">TheJoia</h3>
                                    <p class="artist-genre">Cantora e produtora</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/mugi.png">
                                    </div>
                                    <h3 class="artist-name">Mugi</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="assets/img/yung-loof.jpg">
                                    </div>
                                    <h3 class="artist-name">Yung Loof</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#artistasCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#artistasCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Personalização -->
    <section id="personalizacao" class="section">
        <div class="container">
            <h2 class="section-title">Personalização de Camisetas</h2>
            <div class="custom-section">
                <div class="row">
                    <div class="col-md-6">
                        <h3 style="color: var(--accent-white); margin-bottom: 1rem;">Crie sua camiseta única</h3>
                        <p style="color: var(--text-gray); margin-bottom: 2rem;">
                            Envie sua ideia, referências e imagens. Nossa equipe vai criar uma peça exclusiva para você.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-form">
                            <form id="custom-form">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição da Ideia</label>
                                    <textarea class="form-control" id="descricao" rows="4" placeholder="Descreva sua ideia para a camiseta..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="referencias" class="form-label">Anexar Referências</label>
                                    <input type="file" class="form-control" id="referencias" multiple accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-custom w-100">Enviar Pedido</button>
                            </form>
                        </div>
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
