<?php
/**
 * Navbar Global - Sistema de autenticação integrado
 */

use App\Helpers\CartHelper;

// Buscar quantidade de itens no carrinho
$cart_count = CartHelper::getItemCount();

// Verificar se usuário está logado
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Usuário';
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
?>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo ASSETS_URL; ?>materials/batrip-png-branco.png" 
                 alt="Batrip Logo" 
                 style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>#lancamentos">Lançamentos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>#conjuntos">Conjuntos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>#artistas">Artistas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>sobre">Sobre</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-2">
                <?php if ($is_logged_in): ?>
                    <!-- Usuário logado -->
                    <div class="dropdown">
                        <button class="btn login-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($user_name); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>perfil">
                                    <i class="bi bi-person me-2"></i>Meu Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pedidos">
                                    <i class="bi bi-bag me-2"></i>Meus Pedidos
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($is_admin): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>adm">
                                        <i class="bi bi-gear me-2"></i>Área Admin
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Usuário não logado -->
                    <a href="<?php echo BASE_URL; ?>login" class="login-btn">
                        <i class="bi bi-person-circle me-1"></i>Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>register" class="login-btn">
                        <i class="bi bi-person-plus me-1"></i>Registrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Botão do Carrinho -->
        <button class="btn btn-outline-light ms-2" type="button" 
                data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" 
                aria-controls="cartSidebar" style="z-index:1051;">
            <i class="bi bi-cart"></i>
            <span class="badge bg-danger" id="cart-count"><?php echo $cart_count; ?></span>
        </button>
    </div>
</nav>
