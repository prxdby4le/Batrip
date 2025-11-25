<?php
// Head global para todas as páginas
// Inicia buffer para evitar "headers already sent" por saída acidental (ex: BOM)
if (function_exists('ob_get_level') && ob_get_level() === 0) {
    ob_start();
}
// Calcula automaticamente o caminho base
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '';

// Base href: prefixo antes de /public ("/" ou "/Batrip/")
$scriptName = str_replace('\\', '/', $scriptName);
$pos = strpos($scriptName, '/public/');
if ($pos === false) {
    $pos = strpos($scriptName, '/public');
}
$prefix = ($pos !== false) ? substr($scriptName, 0, $pos) : '';
$baseHref = ($prefix === '') ? '/' : rtrim($prefix, '/') . '/';

// Determina quantos níveis subir baseado na estrutura do projeto
if (strpos($requestUri, '/public/produtos/') !== false || strpos($scriptName, '/produtos/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/registros/') !== false || strpos($scriptName, '/registros/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/checkout/') !== false || strpos($scriptName, '/checkout/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/adm/') !== false || strpos($scriptName, '/adm/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/') !== false || strpos($scriptName, '/public/') !== false) {
    $basePath = '../';
} else {
    $basePath = '';
}

// Torna baseHref/basePath acessível globalmente para outros includes
$GLOBALS['baseHref'] = $baseHref;
$GLOBALS['basePath'] = $basePath;

// Cabeçalhos de segurança básicos
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: no-referrer-when-downgrade");
    // CSP básica (sem jsDelivr); mantemos Google Fonts e cdnjs se necessário
    $csp = "default-src 'self'; script-src 'self' https://sdk.mercadopago.com https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; connect-src 'self' https://viacep.com.br; frame-ancestors 'self';";
    header("Content-Security-Policy: $csp");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES); ?>">
    <link rel="icon" href="assets/materials/batrip%20symbol.png" type="image/x-icon">
    <link href="assets/css/bootstrap-css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons removed (self-hosted SVGs via icon-helper are used instead) -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <script>
        // Configuração global de caminhos para JavaScript
        window.BATRIP_CONFIG = {
            basePath: '<?php echo addslashes($basePath); ?>',
            baseHref: '<?php echo addslashes($baseHref); ?>'
        };
    </script>
    <!-- Utilitários JavaScript Compartilhados -->
    <script src="<?php echo htmlspecialchars($baseHref, ENT_QUOTES); ?>assets/js/utils.js"></script>
 </head>
