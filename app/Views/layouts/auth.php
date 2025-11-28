<?php
/**
 * Layout de Autenticação - Login/Register
 * 
 * Layout limpo para páginas de autenticação
 */

$pageTitle = $pageTitle ?? 'Batrip';
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
    <link href="assets/css/bootstrap-css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
    
    <!-- Estilos específicos para páginas de autenticação -->
    <style>
        /* Melhorar contraste e espaçamento nas páginas de autenticação */
        .auth-page .form-text {
            color: #6c757d !important; /* Contraste mínimo 4.5:1 em fundo branco */
            font-size: 0.875rem;
            margin-top: 0.5rem;
            line-height: 1.5;
        }
        
        .auth-page .text-muted {
            color: #6c757d !important; /* Contraste mínimo 4.5:1 */
        }
        
        .auth-page .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #212529;
        }
        
        .auth-page .card {
            margin-bottom: 2rem;
        }
        
        .auth-page .card-body {
            padding: 2.5rem !important;
        }
        
        @media (max-width: 576px) {
            .auth-page .card-body {
                padding: 1.5rem !important;
            }
        }
        
        .auth-page .mb-3 {
            margin-bottom: 1.25rem !important;
        }
        
        .auth-page .form-control {
            padding: 0.75rem;
            font-size: 1rem;
            margin-bottom: 0;
        }
        
        .auth-page .btn-custom {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .auth-page footer .text-muted {
            color: #adb5bd !important;
            font-size: 0.875rem;
        }
        
        /* Espaçamento superior aumentado */
        .auth-page .auth-logo-container {
            padding-top: 60px;
            padding-bottom: 20px;
        }
        
        /* Espaçamento entre seções */
        .auth-page main {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            padding: 40px 0 2rem 0;
        }
        
        .auth-page .container {
            max-width: 600px;
        }
    </style>
</head>
<body class="auth-page">
    <!-- Logo simples no topo -->
    <div class="container auth-logo-container">
        <div class="text-center mb-4">
            <a href="<?php echo BASE_URL; ?>">
                <img src="assets/materials/batrip-png-branco.png" alt="Batrip Logo" style="height: 60px;">
            </a>
        </div>
    </div>
    
    <!-- Conteúdo -->
    <main>
        <?php echo $content; ?>
    </main>
    
    <!-- Footer simples -->
    <footer class="text-center py-4 mt-5">
        <p style="color: #adb5bd; font-size: 0.875rem; margin: 0;">© 2025 BATRIP. Todos os direitos reservados.</p>
    </footer>
    
    <!-- Bootstrap Bundle JS -->
    <script src="assets/js/bootstrap-js/bootstrap.bundle.min.js"></script>
</body>
</html>
