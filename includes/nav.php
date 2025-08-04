<?php // Navbar global para todas as páginas ?>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php' : '../index.php'); ?>">
            <img src="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'assets/materials/batrip png branco.png' : '../assets/materials/batrip png branco.png'); ?>" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php#lancamentos' : '../index.php#lancamentos'); ?>">Lançamentos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php#conjuntos' : '../index.php#conjuntos'); ?>">Conjuntos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php#artistas' : '../index.php#artistas'); ?>">Artistas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php#personalizacao' : '../index.php#personalizacao'); ?>">Personalização</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'sobre.php' : '../sobre.php'); ?>">Sobre</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'login.php' : '../login.php'); ?>" class="login-btn">
                    <i class="fas fa-user"></i> Login
                </a>
                <a href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'register.php' : '../register.php'); ?>" class="login-btn">
                    <i class="fas fa-user"></i> Registrar
                </a>
                <a href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'perfil.php' : '../perfil.php'); ?>" class="login-btn">
                    <i class="fas fa-user"></i> Perfil
                </a>
            </div>
        </div>
        <button class="btn btn-outline-light ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar" style="z-index:1051;">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge bg-danger" id="cart-count">2</span>
        </button>
    </div>
</nav>
