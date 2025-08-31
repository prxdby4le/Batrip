<?php
$pageTitle = '-**¡not all bats are dead!**_';
include '../includes/head.php';

// Padroniza basePath e cria token CSRF para formulários
$basePath = $basePath ?? '/';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$csrfToken = get_csrf_token();

// Busca lançamentos (produtos ativos mais recentes)
try {
    $stmtHome = $pdo->query("SELECT id, title, price, image FROM products WHERE active = 1 ORDER BY id DESC LIMIT 6");
    $homeProducts = $stmtHome->fetchAll();
} catch (Throwable $e) {
    $homeProducts = [];
}
?>
<body>
    <?php include '../includes/nav.php'; ?>
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
                <?php if (!empty($homeProducts)): ?>
                    <?php foreach ($homeProducts as $hp): ?>
                        <div class="col-md-4 mb-4">
                            <?php
                            $productTitle = $hp['title'];
                            $productPrice = 'R$ ' . number_format((float)$hp['price'], 2, ',', '.');
                            $productImage = $hp['image'];
                            $productLink = $basePath . 'produto.php?id=' . (int)$hp['id'];
                            $cartLink = '#';
                            $showQtyControl = false;
                            $showSizeControl = false;
                            $buyButtonLabel = 'Adicionar ao Carrinho';
                            include '../includes/product-card.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">Sem produtos no momento.</div>
                <?php endif; ?>
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
                            <img src="<?= $basePath ?>assets/img/fragmentado-frente.jpeg" alt="Camiseta Fragmentado Oversized" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                        </div>
                        <h3 class="product-title">Drop Fragmentado</h3>
                        <p style="color: var(--text-gray); margin-bottom: 1rem;">Oversized + Boxy</p>
                        <p class="product-price">R$ 270,00</p>
                        <a href="<?= $basePath ?>produtos/conjunto-fragmentado.php" class="btn btn-custom">Ver Conjunto</a>
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
                        <a href="<?= $basePath ?>produtos/em-breve.php" class="btn btn-custom">Ver Conjunto</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Artistas Parceiros -->
    <section id="artistas" class="section">
        <div class="container">
            <h2 class="section-title">No fone e na peita</h2>
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
                                        <img src="<?= $basePath ?>assets/img/chard-la-plaga.jpg" alt="Chard la Plaga">
                                    </div>
                                    <h3 class="artist-name">Chard la Plaga</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/link-do-zap.jpg" alt="Link do Zap">
                                    </div>
                                    <h3 class="artist-name">Link do Zap</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/ugovhb.jpg" alt="Ugovhb">
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
                                        <img src="<?= $basePath ?>assets/img/ef.jpg" alt="EF">
                                    </div>
                                    <h3 class="artist-name">EF</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/pradasoueu.jpg" alt="pradasoueu">
                                    </div>
                                    <h3 class="artist-name">pradasoueu</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/prxdby4le.jpg" alt="prxdby4le">
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
                                        <img src="<?= $basePath ?>assets/img/thejoia.jpg" alt="TheJoia">
                                    </div>
                                    <h3 class="artist-name">TheJoia</h3>
                                    <p class="artist-genre">Cantora e produtora</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/mugi.png" alt="Mugi">
                                    </div>
                                    <h3 class="artist-name">Mugi</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/yung-loof.jpg" alt="Yung Loof">
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
                            <form id="custom-form" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
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
    <?php include '../includes/scripts.php'; ?>
</body>
</html>

