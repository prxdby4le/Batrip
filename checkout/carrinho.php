<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho | Batrip</title>
    <link rel="icon" href="materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="materials/batrip png branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#lancamentos">Lançamentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#pecas">Peças</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#conjuntos">Conjuntos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#artistas">Artistas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#personalizacao">Personalização</a>
                    </li>
    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
                                    </thead>
                                    <tbody id="cart-items-container"></tbody>
                                </table>
                            </div>
                            <div id="cart-empty-message" class="alert alert-info text-center mt-4 d-none">Seu carrinho está vazio.</div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body" id="cart-summary"></div>
                    </div>
                </div>
                <!-- Removido o resumo do pedido nesta página para servir apenas para finalização -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h3 class="footer-title">Batrip</h3>
                    <p style="color: var(--text-gray);">
                        Exclusividade • Sonoridade • Autenticidade<br>
                        A marca favorita do seu artista favorito.
                    </p>
                </div>
                <div class="col-md-4">
                    <h4 class="footer-title">Contato</h4>
                    <a href="mailto:contato@batrip.com" class="footer-link">
                        <i class="fas fa-envelope"></i> contato@batrip.com
                    </a>
                    <a href="tel:+5511123456789" class="footer-link">
                        <i class="fas fa-phone"></i> (11) 123456789
                    </a>
                    <p class="footer-link">
                        <i class="fas fa-map-marker-alt"></i> São Paulo, SP
                    </p>
                </div>
                <div class="col-md-4">
                    <h4 class="footer-title">Redes Sociais</h4>
                    <div>
                        <a href="https://www.instagram.com/batrip___/" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="https://x.com/pradasoueu" class="social-icon"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: var(--border-gray); margin: 2rem 0 1rem;">
            <div class="text-center">
                <p style="color: var(--text-gray); margin: 0;">
                    © 2025 BATRIP. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts/script.js"></script>
  </body>
</html>