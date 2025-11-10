<?php
/**
 * View: Pages/About
 * Página Sobre
 */
?>

<!-- Sobre -->
<section class="about-page" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6">
                <h1 class="mb-4">Sobre a Batrip</h1>
                <p class="lead">
                    Somos mais que uma marca de streetwear. Somos um movimento.
                </p>
                <p>
                    A <strong>Batrip</strong> nasceu da paixão pela música trap brasileira e pela cultura urbana. 
                    Criamos peças exclusivas que representam a essência do trap, com design único e qualidade premium.
                </p>
                <p>
                    Cada produto é pensado para quem vive e respira a cultura da rua, 
                    para quem valoriza autenticidade e estilo próprio.
                </p>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?php echo ASSETS_URL; ?>materials/batrip-png-branco.png" 
                     alt="Batrip Logo" 
                     class="img-fluid"
                     style="max-width: 400px; filter: drop-shadow(0 0 30px rgba(255,255,255,0.1));">
            </div>
        </div>
        
        <!-- Valores -->
        <div class="row text-center mb-5">
            <div class="col-md-4 mb-4">
                <div class="value-card p-4">
                    <i class="bi bi-gem" style="font-size: 3rem; color: var(--accent-blue);"></i>
                    <h3 class="mt-3">Exclusividade</h3>
                    <p class="text-muted">
                        Peças únicas e edições limitadas para quem busca se destacar
                    </p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="value-card p-4">
                    <i class="bi bi-music-note-beamed" style="font-size: 3rem; color: var(--accent-blue);"></i>
                    <h3 class="mt-3">Sonoridade</h3>
                    <p class="text-muted">
                        Conectados com a cena musical e os artistas que moldam o trap brasileiro
                    </p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="value-card p-4">
                    <i class="bi bi-award" style="font-size: 3rem; color: var(--accent-blue);"></i>
                    <h3 class="mt-3">Autenticidade</h3>
                    <p class="text-muted">
                        Qualidade premium e design autêntico em cada detalhe
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Nossa História -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-12">
                <h2 class="mb-4 text-center">Nossa História</h2>
                <p class="text-center mb-4">
                    Fundada em 2024, a Batrip surgiu do desejo de criar uma marca que representasse 
                    verdadeiramente a cultura trap brasileira. Começamos com designs ousados e 
                    colaborações com artistas locais, e hoje somos reconhecidos como a marca 
                    favorita de quem vive esse estilo de vida.
                </p>
                <p class="text-center text-muted">
                    <strong>A marca favorita do seu artista favorito.</strong>
                </p>
            </div>
        </div>
        
        <!-- CTA -->
        <div class="text-center">
            <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom btn-lg">
                <i class="bi bi-shop me-2"></i>Ver Nossa Coleção
            </a>
        </div>
    </div>
</section>
