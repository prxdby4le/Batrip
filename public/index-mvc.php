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

// Carrega configurações
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/config.php';

// Inicia sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cria objeto Request
$request = new \App\Core\Request();

// Carrega as rotas
require_once ROOT_PATH . '/config/routes.php';
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
    // Em produção, mostrar página de erro personalizada
    if (DEBUG) {
        echo "<h1>Erro</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Erro interno do servidor</h1>";
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }
}
