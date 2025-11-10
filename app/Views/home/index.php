<?php
/**
 * View: Home/Index
 * Página inicial com produtos em destaque e seções
 */
?>

<!-- Hero Section -->
<section class="hero" style="padding-top: 80px;">
    <div class="container text-center">
        <h1 class="hero-title" style="font-size: 4rem; font-weight: 900; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 3px;">
            BATRIP
        </h1>
        <p class="hero-subtitle" style="font-size: 1.5rem; margin-bottom: 2rem; color: var(--text-gray);">
            A marca favorita do seu artista favorito
        </p>
        <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom btn-lg">
            Ver Produtos
        </a>
    </div>
</section>

<!-- Produtos em Destaque -->
<section id="lancamentos" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Lançamentos</h2>
        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="product-card">
                            <a href="<?php echo BASE_URL; ?>produto/<?php echo $product['id']; ?>" class="text-decoration-none">
                                <div class="product-image-wrapper">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>product-image.php?id=<?php echo $product['id']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                             class="product-image">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_URL; ?>img/placeholder.svg" 
                                             alt="Produto sem imagem" 
                                             class="product-image">
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    <p class="product-price">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Nenhum produto disponível no momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Seção de Conjuntos -->
<section id="conjuntos" class="py-5 bg-dark">
    <div class="container">
        <h2 class="text-center mb-4">Conjuntos Exclusivos</h2>
        <p class="text-center text-muted mb-5">
            Monte seu look completo com nossas combinações perfeitas
        </p>
        <div class="text-center">
            <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-outline-light">
                Ver Todos os Conjuntos
            </a>
        </div>
    </div>
</section>

<!-- Seção de Artistas -->
<section id="artistas" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">No fone e na peita</h2>
        <div id="artistasCarousel" class="carousel slide" data-bs-ride="carousel" role="region" aria-roledescription="carousel" aria-label="Artistas parceiros">
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
                                    <img src="<?php echo ASSETS_URL; ?>img/chard-la-plaga.jpg" alt="Chard la Plaga">
                                </div>
                                <h3 class="artist-name">Chard la Plaga</h3>
                                <p class="artist-genre">Cantor e produtor</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artist-card">
                                <div class="artist-avatar">
                                    <img src="<?php echo ASSETS_URL; ?>img/link-do-zap.jpg" alt="Link do Zap">
                                </div>
                                <h3 class="artist-name">Link do Zap</h3>
                                <p class="artist-genre">Cantor e produtor</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artist-card">
                                <div class="artist-avatar">
                                    <img src="<?php echo ASSETS_URL; ?>img/ugovhb.jpg" alt="Ugovhb">
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
                                    <img src="<?php echo ASSETS_URL; ?>img/ef.jpg" alt="EF">
                                </div>
                                <h3 class="artist-name">EF</h3>
                                <p class="artist-genre">Cantor e produtor</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artist-card">
                                <div class="artist-avatar">
                                    <img src="<?php echo ASSETS_URL; ?>img/pradasoueu.jpg" alt="pradasoueu">
                                </div>
                                <h3 class="artist-name">pradasoueu</h3>
                                <p class="artist-genre">Cantor e produtor</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artist-card">
                                <div class="artist-avatar">
                                    <img src="<?php echo ASSETS_URL; ?>img/prxdby4le.jpg" alt="prxdby4le">
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
                                    <img src="<?php echo ASSETS_URL; ?>img/thejoia.jpg" alt="TheJoia">
                                </div>
                                <h3 class="artist-name">TheJoia</h3>
                                <p class="artist-genre">Cantora e produtora</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artist-card">
                                <div class="artist-avatar">
                                    <img src="<?php echo ASSETS_URL; ?>img/mugi.png" alt="Mugi">
                                </div>
                                <h3 class="artist-name">Mugi</h3>
                                <p class="artist-genre">Cantor e produtor</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artist-card">
                                <div class="artist-avatar">
                                    <img src="<?php echo ASSETS_URL; ?>img/yung-loof.jpg" alt="Yung Loof">
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

<!-- Seção Sobre -->
<section id="sobre" class="py-5 bg-dark">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-4">Sobre a Batrip</h2>
                <p class="lead mb-4">
                    Somos uma marca de streetwear que une moda, música e autenticidade.
                </p>
                <p class="text-muted mb-4">
                    Cada peça é cuidadosamente desenvolvida para representar a essência do trap brasileiro,
                    com design exclusivo e qualidade premium.
                </p>
                <a href="<?php echo BASE_URL; ?>sobre" class="btn btn-custom">
                    Saiba Mais
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?php echo ASSETS_URL; ?>materials/batrip-png-branco.png" 
                     alt="Batrip Logo" 
                     style="max-width: 300px; filter: drop-shadow(0 0 20px rgba(255,255,255,0.1));">
            </div>
        </div>
    </div>
</section>
