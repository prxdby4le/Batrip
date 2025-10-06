<?php 
$pageTitle = 'Perfil | Batrip'; 
include '../../includes/head.php';
include '../../includes/auth.php';

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
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h1 class="profile-welcome-title">
                            <i class="fas fa-user-circle me-2"></i>
                            Bem-vindo, <span class="accent-text">@<?= htmlspecialchars($user['display_name'] ?? '') ?></span>!
                        </h1>
                        <p class="profile-subtitle"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <i class="fas fa-calendar-alt"></i>
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
                            <h3><i class="fas fa-user-edit me-2"></i>Informações Pessoais</h3>
                            <div class="profile-actions">
                                <a href="registros/perfil_editar.php" class="btn btn-custom btn-sm">
                                    <i class="fas fa-edit me-1"></i>Editar Perfil
                                </a>
                                <a href="alterar_senha.php" class="btn btn-outline-warning btn-sm ms-2">
                                    <i class="fas fa-key me-1"></i>Alterar Senha
                                </a>
                            </div>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-user me-2"></i>Nome Completo
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($user['name'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-at me-2"></i>Nome de Usuário
                                    </div>
                                    <div class="info-value">@<?= htmlspecialchars($user['display_name'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($user['email'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Endereço
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($user['endereco'] ?? 'Não informado') ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-city me-2"></i>Cidade/Estado
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
                                        <i class="fas fa-mail-bulk me-2"></i>CEP
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
                            <h3><i class="fas fa-shopping-bag me-2"></i>Histórico de Pedidos</h3>
                            <?php if ($orders): ?>
                                <span class="badge bg-success"><?= count($orders) ?> pedidos</span>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <?php if (!$orders): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h4>Nenhum pedido encontrado</h4>
                                    <p>Você ainda não realizou nenhuma compra. Que tal explorar nossos produtos?</p>
                                    <a class="btn btn-custom" href="../index.php">
                                        <i class="fas fa-store me-2"></i>Ir às Compras
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
                                                <i class="fas fa-eye me-1"></i>Ver Detalhes
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

