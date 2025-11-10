<?php 
$pageTitle = 'Perfil | Batrip'; 
include '../../includes/head.php';
include '../../includes/auth.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Requer login para acessar o perfil
require_login();

// Buscar dados do usuário atual
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Usuário não encontrado, fazer logout
        logout();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar usuário: " . $e->getMessage());
    $error = "Erro ao carregar dados do perfil.";
}

// Buscar pedidos do usuário
$orders = [];
try {
    $stmt = $pdo->prepare('
        SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar pedidos: " . $e->getMessage());
}

// Configurações de imagem de perfil
$userId = $user['id'];
$profileImgPath = "../../assets/img/perfil/usuario_" . $userId . ".jpg";
$hasProfileImg = !empty($user['profile_img']) && file_exists(__DIR__ . '/../../assets/img/perfil/usuario_' . $userId . '.jpg');

// Avatar baseado no primeiro caractere do nome
$avatar = strtoupper(substr($user['name'], 0, 1));
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    
    <!-- Hero Profile Section -->
    <section class="hero-profile-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <div class="profile-hero-card">
                        <div class="profile-avatar-large mb-3">
                            <?php if ($hasProfileImg): ?>
                                <img src="<?= $profileImgPath ?>?v=<?= filemtime(__DIR__ . '/../../assets/img/perfil/usuario_' . $userId . '.jpg') ?>" alt="Foto de perfil" class="profile-img-hero">
                            <?php else: ?>
                                <div class="profile-avatar-placeholder">
                                    <?= icon('user', 'icon-5x') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h1 class="profile-welcome-title">
                            <?= icon('user-circle', 'icon me-2') ?>
                            Bem-vindo, <span class="accent-text">@<?= htmlspecialchars($user['display_name'] ?? '') ?></span>!
                        </h1>
                        <p class="profile-subtitle"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <?= icon('calendar', 'icon') ?>
                                <span>Membro desde <?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Profile Content -->
    <section class="section profile-content">
        <div class="container">
            <div class="row g-4">
                <!-- Profile Info Card -->
                <div class="col-lg-6">
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h3><?= icon('user-edit', 'icon me-2') ?>Informações Pessoais</h3>
                            <div class="profile-actions">
<<<<<<< HEAD
                                <a href="registros/perfil_editar.php" class="btn btn-custom btn-sm">
                                    <?= icon('edit', 'icon me-1') ?>Editar Perfil
=======
                                <a href="perfil_editar.php" class="btn btn-custom btn-sm">
                                    <i class="fas fa-edit me-1"></i>Editar Perfil
>>>>>>> parent of aed6c44 (caminhos corrigidos sistema de adicionar produtos normais funcionando)
                                </a>
                                <a href="alterar_senha.php" class="btn btn-outline-warning btn-sm ms-2">
                                    <?= icon('key', 'icon me-1') ?>Alterar Senha
                                </a>
                            </div>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <?= icon('user', 'icon me-2') ?>Nome Completo
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($user['name'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <?= icon('at', 'icon me-2') ?>Nome de Usuário
                                    </div>
                                    <div class="info-value">@<?= htmlspecialchars($user['display_name'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <?= icon('envelope', 'icon me-2') ?>Email
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($user['email'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <?= icon('map-marker', 'icon me-2') ?>Endereço
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($user['endereco'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <?= icon('city', 'icon me-2') ?>Cidade/Estado
                                    </div>
                                    <div class="info-value">
                                        <?php 
                                        $cidade = $user['cidade'] ?? '';
                                        $estado = $user['estado'] ?? '';
                                        if (!empty($cidade) && !empty($estado)) {
                                            echo htmlspecialchars($cidade . ' / ' . $estado);
                                        } elseif (!empty($cidade)) {
                                            echo htmlspecialchars($cidade);
                                        } elseif (!empty($estado)) {
                                            echo htmlspecialchars($estado);
                                        } else {
                                            echo 'Não informado';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <?= icon('mail-bulk', 'icon me-2') ?>CEP
                                    </div>
                                    <div class="info-value">
                                        <?php 
                                        $cep = $user['cep'] ?? '';
                                        if (!empty($cep) && strlen($cep) === 8) {
                                            echo htmlspecialchars(preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep));
                                        } elseif (!empty($cep)) {
                                            echo htmlspecialchars($cep);
                                        } else {
                                            echo 'Não informado';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Card -->
                <div class="col-lg-6">
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h3><?= icon('shopping-bag', 'icon me-2') ?>Histórico de Pedidos</h3>
                            <?php if ($orders): ?>
                                <span class="badge bg-success"><?= count($orders) ?> pedidos</span>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <?php if (!$orders): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <?= icon('shopping-cart', 'icon-5x') ?>
                                    </div>
                                    <h4>Nenhum pedido encontrado</h4>
                                    <p>Você ainda não realizou nenhuma compra. Que tal explorar nossos produtos?</p>
                                    <a class="btn btn-custom" href="../index.php">
                                        <?= icon('store', 'icon me-2') ?>Ir às Compras
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="orders-list">
                                    <?php foreach ($orders as $o): ?>
                                    <div class="order-item">
                                        <div class="order-header">
                                            <div class="order-id">
                                                <strong>Pedido #<?= (int)$o['id'] ?></strong>
                                                <span class="order-date"><?= date('d/m/Y', strtotime($o['created_at'] ?? 'now')) ?></span>
                                            </div>
                                            <div class="order-total">
                                                <span class="total-label">Total:</span>
                                                <span class="total-value">R$ <?= number_format((float)$o['total'],2,',','.') ?></span>
                                            </div>
                                        </div>
                                        <div class="order-details">
                                            <div class="detail-item">
                                                <span>Subtotal: R$ <?= number_format((float)$o['subtotal'],2,',','.') ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span>Frete: R$ <?= number_format((float)$o['shipping'],2,',','.') ?></span>
                                            </div>
                                        </div>
                                        <div class="order-actions">
                                            <a class="btn btn-outline-light btn-sm" href="pedido.php?id=<?= (int)$o['id'] ?>">
                                                <?= icon('eye', 'icon me-1') ?>Ver Detalhes
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

