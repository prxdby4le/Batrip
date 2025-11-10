<?php 
// Navbar global - Sistema de autenticação integrado
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/icon-helper.php';

// Determinar caminhos baseado na estrutura do diretório
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';

// Buscar quantidade de itens no carrinho usando cart-functions
require_once __DIR__ . '/cart-functions.php';
$cart_count = get_cart_count();

// Buscar dados do usuário se logado
$user_data = null;
if (is_logged_in()) {
    try {
        $stmt = $pdo->prepare('SELECT name, display_name, profile_img FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erro ao buscar dados do usuário na nav: " . $e->getMessage());
    }
}
?>
<nav class="navbar navbar-expand-lg fixed-top">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <script>window.CSRF_TOKEN = '<?php echo addslashes(get_csrf_token()); ?>';</script>
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base; ?>index.php">
            <img src="<?php echo $base; ?>assets/materials/batrip-png-branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base; ?>index.php#lancamentos">Lançamentos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base; ?>index.php#conjuntos">Conjuntos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base; ?>index.php#artistas">Artistas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base; ?>index.php#sobre">Sobre</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <!-- Usuário logado -->
                    <div class="dropdown">
                        <button class="btn login-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($user_data && !empty($user_data['profile_img'])): ?>
                                <img src="<?php echo $base; ?>assets/img/perfil/<?php echo htmlspecialchars($user_data['profile_img']); ?>" 
                                     alt="Perfil" class="rounded-circle me-1" style="width: 20px; height: 20px; object-fit: cover;">
                            <?php else: ?>
                                <?= icon('user-circle', 'icon me-1') ?>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($user_data['display_name'] ?? 'Usuário'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo $base; ?>registros/perfil.php">
                                <?= icon('user', 'icon me-2') ?>Meu Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>registros/pedidos.php">
                                <?= icon('shopping-bag', 'icon me-2') ?>Meus Pedidos
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (is_admin()): ?>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>adm/index-adm.php">
                                    <?= icon('key', 'icon me-2') ?>Área Admin
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>registros/logout.php">
                                <?= icon('arrow-right', 'icon me-2') ?>Sair
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Usuário não logado -->
                    <a href="<?php echo $base; ?>registros/login.php" class="login-btn">
                        <?= icon('user-circle', 'icon me-1') ?>Login
                    </a>
                    <a href="<?php echo $base; ?>registros/register.php" class="login-btn">
                        <?= icon('user-plus', 'icon me-1') ?>Registrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <button class="btn btn-outline-light ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar" style="z-index:1051;">
            <?= icon('shopping-cart', 'icon') ?>
            <span class="badge bg-danger" id="cart-count"><?php echo $cart_count; ?></span>
        </button>
    </div>
</nav>
