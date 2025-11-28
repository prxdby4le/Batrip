<?php 
// Navbar global - Sistema de autenticação integrado
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/icon-helper.php';

// Garantir que $pdo esteja disponível
// auth.php já inclui db.php, mas vamos garantir que esteja disponível no escopo atual
if (!isset($pdo)) {
    // Tentar obter de $GLOBALS primeiro
    if (isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
    } elseif (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
        // Se ainda não estiver definido após incluir db.php, usar $GLOBALS
        if (!isset($pdo) && isset($GLOBALS['pdo'])) {
            $pdo = $GLOBALS['pdo'];
        }
    } else {
        // Fallback: usar Database do MVC
        if (file_exists(dirname(__DIR__) . '/app/Core/Database.php')) {
            require_once dirname(__DIR__) . '/app/Core/Database.php';
            try {
                $pdo = \App\Core\Database::getInstance()->getConnection();
                $GLOBALS['pdo'] = $pdo;
            } catch (\Exception $e) {
                error_log("Erro ao conectar ao banco na nav: " . $e->getMessage());
            }
        }
    }
}

// Usar baseHref calculado no head.php para caminhos consistentes
$baseHref = $GLOBALS['baseHref'] ?? '/';

// Buscar quantidade de itens no carrinho usando cart-functions
require_once __DIR__ . '/cart-functions.php';
$cart_count = get_cart_count();

// Buscar dados do usuário se logado
$user_data = null;
if (is_logged_in() && isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->prepare('SELECT name, display_name, profile_img FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();
    } catch (\PDOException $e) {
        error_log("Erro ao buscar dados do usuário na nav: " . $e->getMessage());
    }
}
?>
<nav class="navbar navbar-expand-lg fixed-top">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <script>window.CSRF_TOKEN = '<?php echo addslashes(get_csrf_token()); ?>';</script>
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars($baseHref) ?>">
            <img src="<?= htmlspecialchars($baseHref) ?>assets/materials/batrip-png-branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($baseHref) ?>#lancamentos">Lançamentos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($baseHref) ?>#conjuntos">Conjuntos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($baseHref) ?>#artistas">Artistas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($baseHref) ?>sobre">Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($baseHref) ?>produtos">Produtos</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <!-- Usuário logado -->
                    <div class="dropdown">
                        <button class="btn login-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($user_data && !empty($user_data['profile_img'])): ?>
                                <img src="<?= htmlspecialchars($baseHref) ?>assets/img/perfil/<?php echo htmlspecialchars($user_data['profile_img']); ?>" 
                                     alt="Perfil" class="rounded-circle me-1" style="width: 20px; height: 20px; object-fit: cover;">
                            <?php else: ?>
                                <?= icon('user-circle', 'icon me-1') ?>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($user_data['display_name'] ?? 'Usuário'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($baseHref) ?>perfil">
                                <?= icon('user', 'icon me-2') ?>Meu Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($baseHref) ?>pedidos">
                                <?= icon('shopping-bag', 'icon me-2') ?>Meus Pedidos
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (is_admin()): ?>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($baseHref) ?>adm">
                                    <?= icon('key', 'icon me-2') ?>Área Admin
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($baseHref) ?>logout">
                                <?= icon('arrow-right', 'icon me-2') ?>Sair
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Usuário não logado -->
                    <a href="<?= htmlspecialchars($baseHref) ?>login" class="login-btn">
                        <?= icon('user-circle', 'icon me-1') ?>Login
                    </a>
                    <a href="<?= htmlspecialchars($baseHref) ?>register" class="login-btn">
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
