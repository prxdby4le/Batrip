<?php
/**
 * View: Errors/404
 * Página de erro 404
 */
?>

<!-- 404 -->
<section class="error-page" style="padding-top: 120px; padding-bottom: 80px;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6">
                <div class="error-icon mb-4">
                    <i class="bi bi-emoji-frown" style="font-size: 8rem; color: var(--text-gray);"></i>
                </div>
                
                <h1 class="display-1 mb-3">404</h1>
                <h2 class="mb-4">Página Não Encontrada</h2>
                
                <p class="lead text-muted mb-5">
                    Desculpe, a página que você está procurando não existe ou foi removida.
                </p>
                
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-custom btn-lg">
                        <i class="bi bi-house me-2"></i>Voltar para Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-shop me-2"></i>Ver Produtos
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
