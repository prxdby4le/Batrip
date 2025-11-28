<?php
/**
 * View: Profile/Index
 * Página de perfil do usuário - Layout estilo rede social
 */

// Verificar foto de perfil
$profileImg = null;
if (!empty($user['profile_img'])) {
    $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__));
    $imgFileName = htmlspecialchars($user['profile_img']);
    // Verifica no novo local (public/assets/img/perfil/)
    $imgPath = $rootPath . '/public/assets/img/perfil/' . $imgFileName;
    // Se não encontrar, verifica no local antigo (assets/img/perfil/)
    if (!file_exists($imgPath)) {
        $imgPath = $rootPath . '/assets/img/perfil/' . $imgFileName;
    }
    
    if (file_exists($imgPath)) {
        // Adiciona timestamp para cache busting
        $profileImgPath = BASE_URL . 'assets/img/perfil/' . $imgFileName . '?v=' . filemtime($imgPath);
        $profileImg = $profileImgPath;
    }
}

$profileBg = !empty($user['profile_bg']) ? htmlspecialchars($user['profile_bg']) : null;
$userName = htmlspecialchars($user['name'] ?? 'Usuário');
$userEmail = htmlspecialchars($user['email'] ?? '');
$userPhone = htmlspecialchars($user['phone'] ?? '');
$memberSince = !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : date('d/m/Y');
?>

<div class="navbar-space"></div>

<style>
    .profile-social-layout {
        padding-top: 0;
        padding-bottom: 40px;
    }

    /* Cover Photo / Background */
    .profile-cover {
        width: 100%;
        height: 350px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .profile-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-cover-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 150px;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    }

    /* Profile Picture */
    .profile-picture-container {
        position: relative;
        margin-top: -80px;
        margin-bottom: 20px;
        z-index: 10;
    }

    .profile-picture {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        border: 5px solid #1a1a1a;
        background: #2a2a2a;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin: 0 auto;
    }

    .profile-picture img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-picture .bi-person-circle {
        font-size: 140px;
        color: var(--accent-blue);
    }

    /* Profile Info Card */
    .profile-info-card {
        background: #1a1a1a;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    .profile-header-info {
        text-align: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #333;
    }

    .profile-name {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: #fff;
    }

    .profile-email {
        color: #adb5bd;
        font-size: 1rem;
        margin-bottom: 15px;
    }

    .profile-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .profile-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .profile-detail-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-detail-item i {
        font-size: 1.2rem;
        color: var(--accent-blue);
        width: 24px;
    }

    .profile-detail-item .label {
        color: #adb5bd;
        font-size: 0.9rem;
    }

    .profile-detail-item .value {
        color: #fff;
        font-weight: 500;
    }

    /* Feed de Pedidos */
    .profile-feed {
        max-width: 800px;
        margin: 0 auto;
    }

    .feed-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #333;
    }

    .feed-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feed-item {
        background: #1a1a1a;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .feed-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .feed-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .feed-item-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feed-item-date {
        color: #adb5bd;
        font-size: 0.9rem;
    }

    .feed-item-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .feed-item-stat {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .feed-item-stat-label {
        color: #adb5bd;
        font-size: 0.85rem;
    }

    .feed-item-stat-value {
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .feed-item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #333;
    }

    .empty-feed {
        text-align: center;
        padding: 60px 20px;
        background: #1a1a1a;
        border-radius: 15px;
    }

    .empty-feed-icon {
        font-size: 5rem;
        color: #444;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .profile-cover {
            height: 250px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
        }

        .profile-picture-container {
            margin-top: -60px;
        }

        .profile-name {
            font-size: 1.4rem;
        }

        .profile-details {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="profile-social-layout">
    <!-- Cover Photo -->
    <div class="profile-cover">
        <?php if ($profileBg): ?>
            <?php 
            // Usa o script PHP para servir a imagem (garante acesso mesmo se .htaccess falhar)
            $bgUrl = BASE_URL . 'serve-upload.php?file=' . urlencode(ltrim($profileBg, '/'));
            ?>
            <img src="<?php echo $bgUrl; ?>" alt="Capa do perfil">
        <?php endif; ?>
        <div class="profile-cover-overlay"></div>
    </div>

    <div class="container">
        <!-- Profile Picture -->
        <div class="profile-picture-container">
            <div class="profile-picture">
                <?php if ($profileImg): ?>
                    <img src="<?php echo $profileImg; ?>" alt="Foto de perfil">
                <?php else: ?>
                    <i class="bi bi-person-circle"></i>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Info Card -->
        <div class="profile-info-card">
            <div class="profile-header-info">
                <h1 class="profile-name"><?php echo $userName; ?></h1>
                <p class="profile-email">
                    <i class="bi bi-envelope me-2"></i><?php echo $userEmail; ?>
                </p>
                <div class="profile-actions">
                    <a href="<?php echo BASE_URL; ?>perfil/editar" class="btn btn-custom">
                        <i class="bi bi-pencil me-2"></i>Editar Perfil
                    </a>
                    <a href="<?php echo BASE_URL; ?>pedidos" class="btn btn-outline-secondary">
                        <i class="bi bi-bag me-2"></i>Ver Todos os Pedidos
                    </a>
                </div>
            </div>

            <div class="profile-details">
                <?php if ($userPhone): ?>
                <div class="profile-detail-item">
                    <i class="bi bi-telephone"></i>
                    <div>
                        <div class="label">Telefone</div>
                        <div class="value"><?php echo $userPhone; ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($user['endereco']) || !empty($user['cidade'])): ?>
                <div class="profile-detail-item">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <div class="label">Endereço</div>
                        <div class="value">
                            <?php 
                            $addressParts = array_filter([
                                $user['endereco'] ?? '',
                                $user['cidade'] ?? '',
                                $user['estado'] ?? '',
                                !empty($user['cep']) ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $user['cep']) : ''
                            ]);
                            echo htmlspecialchars(implode(', ', $addressParts));
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="profile-detail-item">
                    <i class="bi bi-calendar-check"></i>
                    <div>
                        <div class="label">Membro desde</div>
                        <div class="value"><?php echo $memberSince; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feed de Pedidos -->
        <div class="profile-feed">
            <div class="feed-header">
                <h2 class="feed-title">
                    <i class="bi bi-bag-heart"></i>
                    Meus Pedidos
                </h2>
                <?php if (!empty($orders)): ?>
                <span class="badge bg-primary" style="font-size: 0.9rem;">
                    <?php echo count($orders); ?> pedido(s)
                </span>
                <?php endif; ?>
            </div>

            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <?php 
                    $items = json_decode($order['items'] ?? '[]', true);
                    $itemCount = count($items);
                    $orderTotal = number_format($order['total'] ?? 0, 2, ',', '.');
                    $orderDate = date('d/m/Y', strtotime($order['created_at']));
                    $orderTime = date('H:i', strtotime($order['created_at']));
                    
                    $statusClass = match($order['status']) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'production_complete' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    };

                    $statusLabel = match($order['status']) {
                        'pending' => 'Pendente',
                        'processing' => 'Em Produção',
                        'production_complete' => 'Produção Completa',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                        default => ucfirst($order['status'])
                    };
                    ?>
                    <div class="feed-item">
                        <div class="feed-item-header">
                            <h3 class="feed-item-title">
                                <i class="bi bi-receipt-cutoff"></i>
                                Pedido #<?php echo $order['id']; ?>
                            </h3>
                            <span class="feed-item-date">
                                <?php echo $orderDate; ?> às <?php echo $orderTime; ?>
                            </span>
                        </div>

                        <div class="feed-item-content">
                            <div class="feed-item-stat">
                                <span class="feed-item-stat-label">Itens</span>
                                <span class="feed-item-stat-value"><?php echo $itemCount; ?> item(ns)</span>
                            </div>
                            <div class="feed-item-stat">
                                <span class="feed-item-stat-label">Total</span>
                                <span class="feed-item-stat-value">R$ <?php echo $orderTotal; ?></span>
                            </div>
                            <div class="feed-item-stat">
                                <span class="feed-item-stat-label">Status</span>
                                <span class="badge bg-<?php echo $statusClass; ?>" style="font-size: 0.9rem; width: fit-content;">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </div>
                        </div>

                        <div class="feed-item-footer">
                            <a href="<?php echo BASE_URL; ?>pedido/<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Ver Detalhes
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-feed">
                    <i class="bi bi-bag-x empty-feed-icon"></i>
                    <h4 class="text-white mb-3">Nenhum pedido encontrado</h4>
                    <p class="text-white-50 mb-4">Você ainda não realizou nenhum pedido.</p>
                    <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom">
                        <i class="bi bi-shop me-2"></i>Explorar Produtos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
