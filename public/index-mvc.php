<?php
// Iniciar buffer cedo para prevenir 'headers already sent' em caso de aviso anterior
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }
/**
 * Front Controller - Entry Point
 * 
 * Arquivo de entrada principal da aplicação MVC
 * 
 * @category Bootstrap
 * @package  Batrip
 */

// Define o diretório raiz (somente se ainda não definido)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Carrega autoloader
require_once ROOT_PATH . '/autoload.php';

// Carrega configurações (deve vir antes de db.php para definir constantes)
require_once ROOT_PATH . '/config/config.php';

// Carrega conexão com banco
require_once ROOT_PATH . '/includes/db.php';

// Inicia sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cria objeto Request
$request = new \App\Core\Request();

// Carrega as rotas
require_once ROOT_PATH . '/config/Routes.php';
$router = new \App\Core\Router($request);
Routes::register($router);

// Passa o Request para o Router
if (!isset($router->request)) {
    $routerReflection = new ReflectionObject($router);
    if ($routerReflection->hasProperty('request')) {
        $requestProperty = $routerReflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($router, $request);
    }
}

// Despacha a requisição
try {
    $router->dispatch($request);
} catch (Exception $e) {
    // Verifica se a requisição é AJAX/JSON
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isJson = strpos($accept, 'application/json') !== false
        || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
    
    // Log do erro
    error_log('Router::dispatch - Erro: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    
    // Limpar qualquer output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Se for requisição AJAX, retorna JSON
    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => DEBUG ? $e->getMessage() : 'Erro interno do servidor',
            'error' => DEBUG ? [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // Em produção, mostrar página de erro personalizada
    if (DEBUG) {
        echo "<h1>Erro</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Erro interno do servidor</h1>";
    }
}
