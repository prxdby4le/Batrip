<?php
/**
 * Arquivo de teste para verificar se o MVC está funcionando
 * Acesse: http://localhost:8080/test-mvc.php
 */

// Define o diretório raiz
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Carrega autoloader
require_once ROOT_PATH . '/autoload.php';

// Carrega configurações
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/config.php';

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Teste MVC</title></head><body>";
echo "<h1>✅ Sistema MVC está funcionando!</h1>";
echo "<h2>Informações do Sistema:</h2>";
echo "<ul>";
echo "<li><strong>ROOT_PATH:</strong> " . ROOT_PATH . "</li>";
echo "<li><strong>BASE_URL:</strong> " . BASE_URL . "</li>";
echo "<li><strong>ASSETS_URL:</strong> " . ASSETS_URL . "</li>";
echo "<li><strong>VIEWS_PATH:</strong> " . VIEWS_PATH . "</li>";
echo "<li><strong>Arquivo atual:</strong> " . __FILE__ . "</li>";
echo "<li><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</li>";
echo "<li><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</li>";
echo "</ul>";

// Testa se os controllers existem
echo "<h2>Verificação de Controllers:</h2>";
echo "<ul>";
$controllers = [
    'HomeController' => 'App\\Controllers\\HomeController',
    'ProductController' => 'App\\Controllers\\ProductController',
    'CartController' => 'App\\Controllers\\CartController',
    'CheckoutController' => 'App\\Controllers\\CheckoutController',
    'AuthController' => 'App\\Controllers\\AuthController',
];

foreach ($controllers as $name => $class) {
    $exists = class_exists($class);
    $status = $exists ? "✅" : "❌";
    echo "<li>{$status} <strong>{$name}:</strong> " . ($exists ? "Existe" : "NÃO ENCONTRADO") . "</li>";
}

echo "</ul>";

// Testa se as views existem
echo "<h2>Verificação de Views:</h2>";
echo "<ul>";
$views = [
    'home/index.php',
    'products/show.php',
    'cart/index.php',
    'checkout/index.php',
];

foreach ($views as $view) {
    $path = VIEWS_PATH . '/' . str_replace('.', '/', $view);
    $exists = file_exists($path);
    $status = $exists ? "✅" : "❌";
    echo "<li>{$status} <strong>{$view}:</strong> " . ($exists ? "Existe" : "NÃO ENCONTRADO") . "</li>";
    if ($exists) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<small>Path: {$path}</small>";
    }
}
echo "</ul>";

// Testa conexão com banco
echo "<h2>Verificação de Banco de Dados:</h2>";
try {
    $pdo = \App\Core\Database::getInstance()->getConnection();
    echo "<p>✅ <strong>Conexão com banco:</strong> OK</p>";
    
    // Testa se a tabela orders tem as colunas necessárias
    $stmt = $pdo->query("SHOW COLUMNS FROM orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['customer_name', 'customer_email', 'shipping_address', 'payment_method'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "<p>✅ <strong>Colunas do pedido:</strong> Todas presentes</p>";
    } else {
        echo "<p>⚠️ <strong>Colunas faltando:</strong> " . implode(', ', $missingColumns) . "</p>";
        echo "<p><small>Execute a migration: database/migrations/20250101_add_order_fields.sql</small></p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro na conexão:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>Como verificar se está usando MVC:</h2>";
echo "<ol>";
echo "<li>Acesse <a href='" . BASE_URL . "'>" . BASE_URL . "</a></li>";
echo "<li>Abra o código-fonte da página (Ctrl+U)</li>";
echo "<li>Procure por comentários ou elementos únicos do MVC</li>";
echo "<li>Ou verifique o Network tab do DevTools - a requisição deve ir para <code>index-mvc.php</code></li>";
echo "</ol>";

echo "<hr>";
echo "<h2>Links de Teste:</h2>";
echo "<ul>";
echo "<li><a href='" . BASE_URL . "'>Home (/)</a></li>";
echo "<li><a href='" . BASE_URL . "produtos'>Produtos (/produtos)</a></li>";
echo "<li><a href='" . BASE_URL . "checkout'>Checkout (/checkout)</a></li>";
echo "<li><a href='" . BASE_URL . "adm'>Admin (/adm)</a></li>";
echo "</ul>";

echo "</body></html>";

