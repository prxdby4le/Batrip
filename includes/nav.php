<?php 
// Navbar global para todas as páginas 
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart-functions.php';
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';
$cart_count = get_cart_count();
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
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'index.php#personalizacao' : '../index.php#personalizacao'); ?>">Personalização</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public' ? 'sobre.php' : '../sobre.php'); ?>">Sobre</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <?php
                    // Exibe o nome de exibição (@) se disponível
                    $displayName = null;
                    if (isset($_SESSION['user_id'])) {
                        require_once __DIR__ . '/db.php';
                        $stmt = $pdo->prepare('SELECT display_name FROM users WHERE id = ?');
                        $stmt->execute([$_SESSION['user_id']]);
                        $row = $stmt->fetch();
                        if ($row && !empty($row['display_name'])) {
                            $displayName = $row['display_name'];
                        }
                    }
                    ?>
                    <span class="text-light me-2">Olá, <?php echo $displayName ? '@' . htmlspecialchars($displayName) : htmlspecialchars($_SESSION['user_name']); ?></span>
                    <?php if (function_exists('is_admin') && is_admin()): ?>
                        <a href="<?php echo $base; ?>adm/products/index.php" class="login-btn">
                            <i class="fas fa-tools"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $base; ?>registros/pedidos.php" class="login-btn">
                        <i class="fas fa-box"></i> Pedidos
                    </a>
                    <a href="<?php echo $base; ?>registros/perfil.php" class="login-btn">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                    <?php if (function_exists('is_admin') && is_admin()): ?>
                        <a href="<?php echo $base; ?>adm/users/index.php" class="login-btn">
                            <i class="fas fa-users-cog"></i> Usuários
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $base; ?>registros/logout.php" class="login-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base; ?>registros/login.php" class="login-btn">
                        <i class="fas fa-user"></i> Login
                    </a>
                    <a href="<?php echo $base; ?>registros/register.php" class="login-btn">
                        <i class="fas fa-user"></i> Registrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <button class="btn btn-outline-light ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar" style="z-index:1051;">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge bg-danger" id="cart-count"><?php echo $cart_count; ?></span>
        </button>
    </div>
</nav>
