<?php
/**
 * PSR-4 Autoloader
 * 
 * Carrega automaticamente classes do namespace App\
 * 
 * @category Core
 * @package  Batrip
 */

spl_autoload_register(function ($class) {
    // Namespace base do projeto
    $prefix = 'App\\';
    
    // Diretório base para o namespace
    $baseDir = __DIR__ . '/';
    
    // Verifica se a classe usa o namespace base
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Não é do nosso namespace, deixa outro autoloader tentar
        return;
    }
    
    // Pega o nome relativo da classe
    $relativeClass = substr($class, $len);
    
    // Substitui namespace separators por directory separators
    // Adiciona .php no final
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Se o arquivo existe, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Inicia sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constantes úteis
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', APP_ROOT . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
