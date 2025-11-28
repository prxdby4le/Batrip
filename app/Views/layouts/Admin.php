<?php
/**
 * Layout Admin - Painel Administrativo
 */

$pageTitle = $pageTitle ?? 'Admin - Batrip';
$user_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <base href="<?php echo BASE_URL; ?>">
    
    <link rel="icon" href="assets/materials/batrip%20symbol.png" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
    
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a1a 0%, #0a0a0a 100%);
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .admin-nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.05);
            border-left-color: var(--accent-blue);
        }
        .admin-content {
            padding: 30px;
            min-height: 100vh;
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(0,123,255,0.1) 0%, rgba(0,123,255,0.05) 100%);
            border: 1px solid rgba(0,123,255,0.2);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar p-0">
                <div class="p-4 text-center border-bottom border-secondary">
                    <img src="assets/materials/batrip-png-branco.png" alt="Batrip" style="height: 40px;">
                    <p class="text-muted small mt-2 mb-0">Admin Panel</p>
                </div>
                
                <nav class="mt-3">
                    <a href="<?php echo BASE_URL; ?>adm" class="admin-nav-link <?php echo $this->request->getPath() === '/adm' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>adm/produtos" class="admin-nav-link <?php echo strpos($this->request->getPath(), '/adm/produtos') === 0 ? 'active' : ''; ?>">
                        <i class="bi bi-box me-2"></i> Produtos
                    </a>
                    <a href="<?php echo BASE_URL; ?>adm/pedidos" class="admin-nav-link <?php echo strpos($this->request->getPath(), '/adm/pedidos') === 0 ? 'active' : ''; ?>">
                        <i class="bi bi-bag me-2"></i> Pedidos
                    </a>
                    <a href="<?php echo BASE_URL; ?>adm/usuarios" class="admin-nav-link <?php echo strpos($this->request->getPath(), '/adm/usuarios') === 0 ? 'active' : ''; ?>">
                        <i class="bi bi-people me-2"></i> Usuários
                    </a>
                    <hr class="border-secondary mx-3">
                    <a href="<?php echo BASE_URL; ?>" class="admin-nav-link">
                        <i class="bi bi-house me-2"></i> Voltar ao Site
                    </a>
                    <a href="<?php echo BASE_URL; ?>logout" class="admin-nav-link">
                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                    </a>
                </nav>
            </div>
            
            <!-- Content -->
            <div class="col-md-10 admin-content">
                <!-- Topbar -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($user_name); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>">Ver Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout">Sair</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Alerts -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php unset($_SESSION['errors']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
    <!-- Scripts adicionais -->
    <?php if (isset($scripts)): ?>
        <?php echo $scripts; ?>
    <?php endif; ?>
</body>
</html>
