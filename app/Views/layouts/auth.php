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
</head>
<body class="auth-page">
    <!-- Logo simples no topo -->
    <div class="container py-4">
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
        <p class="text-muted">© 2025 BATRIP. Todos os direitos reservados.</p>
    </footer>
    
    <!-- Bootstrap Bundle JS -->
    <script src="assets/js/bootstrap-js/bootstrap.bundle.min.js"></script>
</body>
</html>
