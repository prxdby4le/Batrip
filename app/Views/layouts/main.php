<?php
/**
 * Layout Principal - Batrip E-commerce
 * 
 * Variáveis disponíveis:
 * $pageTitle - Título da página (padrão: Batrip)
 * $content - Conteúdo da view
 * $data - Dados passados do controller
 */

// Buffer de saída para evitar "headers already sent"
if (function_exists('ob_get_level') && ob_get_level() === 0) {
    ob_start();
}

// Cabeçalhos de segurança
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: no-referrer-when-downgrade");
    // CSP: permite imagens de localhost e data URIs
    // Nota: CSP está sendo definido no .htaccess, não precisa definir aqui
}

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
    <link href="assets/css/icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    
    <!-- Config para JavaScript -->
    <script>
        window.BATRIP_CONFIG = {
            baseUrl: '<?php echo addslashes(BASE_URL); ?>',
            assetsUrl: '<?php echo addslashes(ASSETS_URL); ?>'
        };
    </script>
    <?php
    // Garante CSRF token disponível no front
    if (!isset($_SESSION['csrf_token'])) {
        try { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); } catch (\Throwable $e) { $_SESSION['csrf_token'] = md5(uniqid('', true)); }
    }
    ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
    
    <!-- Utilitários JavaScript -->
    <script src="assets/js/utils.js"></script>
</head>
<body>
    <?php 
    // Usar includes originais para manter compatibilidade total
    require_once ROOT_PATH . '/includes/auth.php';
    require_once ROOT_PATH . '/includes/icon-helper.php';
    
    // Garantir que $pdo esteja disponível globalmente
    if (!isset($GLOBALS['pdo']) && isset($pdo)) {
        $GLOBALS['pdo'] = $pdo;
    } elseif (!isset($pdo) && isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
    }
    
    // Definir baseHref para compatibilidade
    $baseHref = BASE_URL;
    $GLOBALS['baseHref'] = $baseHref;
    ?>
    <?php include ROOT_PATH . '/includes/nav.php'; ?>
    <?php include ROOT_PATH . '/includes/cart-sidebar.php'; ?>
    
    <!-- Conteúdo Principal -->
    <main>
        <?php echo $content; ?>
    </main>
    
    <?php include ROOT_PATH . '/includes/footer.php'; ?>
    <?php include ROOT_PATH . '/includes/scripts.php'; ?>
    
    <!-- Inicializa carrosséis Bootstrap -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa todos os carrosséis
        const carousels = document.querySelectorAll('.carousel');
        carousels.forEach(function(carousel) {
            if (window.bootstrap && window.bootstrap.Carousel) {
                new bootstrap.Carousel(carousel, {
                    interval: 5000,
                    wrap: true
                });
            }
        });
    });
    </script>
    
    <!-- Scripts adicionais da página -->
    <?php if (isset($scripts)): ?>
        <?php echo $scripts; ?>
    <?php endif; ?>
</body>
</html>
