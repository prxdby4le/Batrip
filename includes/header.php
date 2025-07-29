<!-- HEADER INCLUDE -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="/Batrip/materials/batrip png branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
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
                        <a class="nav-link" href="index.php#conjuntos">Conjuntos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#artistas">Artistas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#personalizacao">Personalização</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/sobre.php">Sobre</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="../autentication/login.php" class="login-btn">
                        <i class="fas fa-user"></i> Login
                    </a>
                    <a href="../autentication/register.php" class="login-btn">
                        <i class="fas fa-user"></i> Registrar
                    </a>
                    <a href="../profile/perfil.php" class="login-btn">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                    <a href="/Batrip/carrinho.php" class="btn btn-outline-light ms-2" style="z-index:1051;" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge bg-danger" id="cart-count">2</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

<!-- Barra lateral do carrinho (offcanvas) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartSidebarLabel"><i class="fas fa-shopping-cart"></i> Carrinho</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body">
    <!-- Prévia dos itens do carrinho -->
    <div id="cart-preview"></div>
  </div>
</div>