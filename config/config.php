<?php
/**
 * Configurações Globais do Projeto Batrip
 * 
 * Define constantes e configurações globais da aplicação
 */

// Define ROOT_PATH se ainda não estiver definido
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Ambiente da aplicação
if (!defined('APP_ENV')) {
    $appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'production');
    define('APP_ENV', $appEnv);
}

// ENVIRONMENT removido - não utilizado (use APP_ENV diretamente)

// Base URL - detecta automaticamente ou usa variável de ambiente
if (!defined('BASE_URL')) {
    $baseUrl = getenv('BASE_URL') ?: ($_ENV['BASE_URL'] ?? '');
    
    if (empty($baseUrl)) {
        // Detecta automaticamente
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Remove /public do caminho
        $pos = strpos($scriptName, '/public/');
        if ($pos !== false) {
            $basePath = substr($scriptName, 0, $pos);
        } else {
            $basePath = '';
        }
        
        $baseUrl = $protocol . '://' . $host . ($basePath ? rtrim($basePath, '/') . '/' : '/');
    }
    
    define('BASE_URL', rtrim($baseUrl, '/') . '/');
}

// Assets URL
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . 'assets/');
}

// Chave da sessão do carrinho
if (!defined('CART_SESSION_KEY')) {
    define('CART_SESSION_KEY', 'batrip_cart');
}

// Tempo de vida da sessão (em segundos)
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 7200); // 2 horas
}

// Configurações de upload
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', ROOT_PATH . '/public/uploads/');
}

// Alias para compatibilidade
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', UPLOAD_DIR);
}

// Configurações de imagens de produtos
if (!defined('IMAGES_PER_PRODUCT_MAX')) {
    define('IMAGES_PER_PRODUCT_MAX', 12); // Máximo de imagens por produto
}

if (!defined('IMAGE_MAX_UPLOAD_MB')) {
    define('IMAGE_MAX_UPLOAD_MB', 5); // Tamanho máximo de upload em MB
}

if (!defined('IMAGE_ACCEPT_MAX_DIM')) {
    define('IMAGE_ACCEPT_MAX_DIM', 6000); // Dimensão máxima aceita (largura ou altura)
}

if (!defined('IMAGE_DOWNSCALE_MAX')) {
    define('IMAGE_DOWNSCALE_MAX', 3000); // Dimensão máxima após redimensionamento
}

if (!defined('IMAGE_MEDIUM_MAX')) {
    define('IMAGE_MEDIUM_MAX', 1024); // Dimensão máxima para versão medium
}

if (!defined('IMAGE_LARGE_MAX')) {
    define('IMAGE_LARGE_MAX', 1600); // Dimensão máxima para versão large
}

// Timezone
if (!defined('TIMEZONE')) {
    date_default_timezone_set('America/Sao_Paulo');
    define('TIMEZONE', 'America/Sao_Paulo');
}

