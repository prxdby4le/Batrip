<?php 
// Navbar global para todas as páginas (integração inicial)
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';
$cart_count = 0;
?>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php' : '../index.php'); ?>">
            <img src="assets/materials/batrip-png-branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
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
                        <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? '#sobre' : '../index.php#sobre'); ?>">Sobre</a>
                    </li>
                
            </ul>
            <div class="d-flex align-items-center gap-2">
                <!-- Integração inicial: navegação simulada -->
                <a href="<?php echo $base; ?>registros/login.php" class="login-btn">
                    <i class="bi bi-person-circle"></i> Login
                </a>
                <a href="<?php echo $base; ?>registros/register.php" class="login-btn">
                    <i class="bi bi-person-plus"></i> Registrar
                </a>
                <a href="<?php echo $base; ?>registros/perfil.php" class="login-btn">
                    <i class="bi bi-person-badge"></i> Visualizar Perfil
                </a>
                <a href="<?php echo $base; ?>registros/logout.php" class="login-btn">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
                <a href="<?php echo $base; ?>public/adm/index-adm.php" class="btn btn-outline-success ms-2">
                    <i class="bi bi-plus-circle"></i> Adicionar Produto
                </a>
                <a href="<?php echo $base; ?>registros/gerenciar_usuarios.php" class="btn btn-outline-primary ms-2">
                    <i class="bi bi-people"></i> Gerenciar Usuários
                </a>
            </div>
        </div>
        <button class="btn btn-outline-light ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar" style="z-index:1051;">
            <i class="bi bi-cart"></i>
            <span class="badge bg-danger" id="cart-count"><?php echo $cart_count; ?></span>
        </button>
    </div>
</nav>
