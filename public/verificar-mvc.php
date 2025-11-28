<?php
/**
 * Página de verificação - Acesse: http://localhost:8080/verificar-mvc.php
 * 
 * Esta página mostra se o sistema está usando o MVC ou os arquivos legados
 */

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Verificação MVC - Batrip</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#1a1a1a;color:#fff;}";
echo "h1{color:#6cf;}h2{color:#9cf;border-bottom:2px solid #6cf;padding-bottom:10px;}";
echo ".ok{color:#4caf50;}.erro{color:#f44336;}.aviso{color:#ff9800;}";
echo "ul{line-height:1.8;}code{background:#333;padding:2px 6px;border-radius:3px;color:#6cf;}";
echo "a{color:#6cf;text-decoration:none;}a:hover{text-decoration:underline;}";
echo ".box{background:#2a2a2a;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #6cf;}";
echo "</style></head><body>";

echo "<h1>🔍 Verificação do Sistema MVC - Batrip</h1>";

// 1. Verifica qual arquivo está sendo executado
echo "<div class='box'>";
echo "<h2>1. Arquivo Atual em Execução</h2>";
$currentFile = basename($_SERVER['SCRIPT_NAME'] ?? 'unknown');
$isMVC = ($currentFile === 'index-mvc.php' || $currentFile === 'verificar-mvc.php');
echo "<p><strong>Arquivo:</strong> <code>{$currentFile}</code></p>";
echo "<p><strong>SCRIPT_NAME:</strong> <code>" . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</code></p>";
echo "<p><strong>REQUEST_URI:</strong> <code>" . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "</code></p>";
echo "</div>";

// 2. Verifica se o MVC está carregado
echo "<div class='box'>";
echo "<h2>2. Sistema MVC</h2>";

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

$mvcLoaded = false;
try {
    if (file_exists(ROOT_PATH . '/autoload.php')) {
        require_once ROOT_PATH . '/autoload.php';
        // Config já carregado se necessário
        $mvcLoaded = true;
        echo "<p class='ok'>✅ Autoloader carregado</p>";
        echo "<p class='ok'>✅ Configurações carregadas</p>";
        echo "<p><strong>BASE_URL:</strong> <code>" . BASE_URL . "</code></p>";
        echo "<p><strong>ROOT_PATH:</strong> <code>" . ROOT_PATH . "</code></p>";
    } else {
        echo "<p class='erro'>❌ Autoloader não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p class='erro'>❌ Erro ao carregar MVC: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// 3. Verifica controllers
if ($mvcLoaded) {
    echo "<div class='box'>";
    echo "<h2>3. Controllers Disponíveis</h2>";
    echo "<ul>";
    
    $controllers = [
        'HomeController' => 'App\\Controllers\\HomeController',
        'ProductController' => 'App\\Controllers\\ProductController',
        'CartController' => 'App\\Controllers\\CartController',
        'CheckoutController' => 'App\\Controllers\\CheckoutController',
        'AuthController' => 'App\\Controllers\\AuthController',
        'ProfileController' => 'App\\Controllers\\ProfileController',
    ];
    
    foreach ($controllers as $name => $class) {
        $exists = class_exists($class);
        $status = $exists ? "<span class='ok'>✅</span>" : "<span class='erro'>❌</span>";
        echo "<li>{$status} <strong>{$name}</strong>: " . ($exists ? "Disponível" : "NÃO ENCONTRADO") . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // 4. Verifica rotas
    echo "<div class='box'>";
    echo "<h2>4. Rotas Configuradas</h2>";
    try {
        require_once ROOT_PATH . '/config/Routes.php';
        echo "<p class='ok'>✅ Arquivo de rotas carregado</p>";
        echo "<p><small>Rotas definidas em: <code>config/Routes.php</code></small></p>";
    } catch (Exception $e) {
        echo "<p class='erro'>❌ Erro ao carregar rotas: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
    
    // 5. Verifica banco de dados
    echo "<div class='box'>";
    echo "<h2>5. Banco de Dados</h2>";
    try {
        $pdo = \App\Core\Database::getInstance()->getConnection();
        echo "<p class='ok'>✅ Conexão com banco estabelecida</p>";
        
        // Verifica colunas da tabela orders
        $stmt = $pdo->query("SHOW COLUMNS FROM orders");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['customer_name', 'customer_email', 'shipping_address', 'payment_method'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "<p class='ok'>✅ Todas as colunas necessárias existem na tabela orders</p>";
        } else {
            echo "<p class='aviso'>⚠️ Colunas faltando na tabela orders: <code>" . implode(', ', $missingColumns) . "</code></p>";
            echo "<p><small>Execute: <code>database/migrations/20250101_add_order_fields.sql</code></small></p>";
        }
    } catch (Exception $e) {
        echo "<p class='erro'>❌ Erro na conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
}

// 6. Instruções
echo "<div class='box'>";
echo "<h2>6. Como Verificar se Está Usando MVC</h2>";
echo "<ol>";
echo "<li><strong>Acesse a home:</strong> <a href='" . ($mvcLoaded ? BASE_URL : '/') . "' target='_blank'>" . ($mvcLoaded ? BASE_URL : '/') . "</a></li>";
echo "<li><strong>Abra o DevTools (F12)</strong> e vá na aba <strong>Network</strong></li>";
echo "<li><strong>Recarregue a página</strong> e verifique a primeira requisição</li>";
echo "<li><strong>Deve aparecer:</strong> <code>index-mvc.php</code> (✅ MVC) ou <code>index.php</code> (❌ Legado)</li>";
echo "<li><strong>Ou verifique o código-fonte:</strong> Procure por comentários como 'View: Home/Index'</li>";
echo "</ol>";
echo "</div>";

// 7. Links de teste
echo "<div class='box'>";
echo "<h2>7. Links para Testar</h2>";
$base = $mvcLoaded ? BASE_URL : '/';
echo "<ul>";
echo "<li><a href='{$base}' target='_blank'>Home (/)</a></li>";
echo "<li><a href='{$base}produtos' target='_blank'>Produtos (/produtos)</a></li>";
echo "<li><a href='{$base}checkout' target='_blank'>Checkout (/checkout)</a></li>";
echo "<li><a href='{$base}adm' target='_blank'>Admin (/adm)</a></li>";
echo "<li><a href='{$base}test-mvc.php' target='_blank'>Teste Completo MVC</a></li>";
echo "</ul>";
echo "</div>";

// 8. Diagnóstico
echo "<div class='box'>";
echo "<h2>8. Diagnóstico</h2>";

$diagnostics = [];

// Verifica .htaccess
if (file_exists(__DIR__ . '/.htaccess')) {
    $htaccess = file_get_contents(__DIR__ . '/.htaccess');
    if (strpos($htaccess, 'index-mvc.php') !== false) {
        $diagnostics[] = "<span class='ok'>✅ .htaccess configurado para usar index-mvc.php</span>";
    } else {
        $diagnostics[] = "<span class='erro'>❌ .htaccess não está configurado para index-mvc.php</span>";
    }
} else {
    $diagnostics[] = "<span class='erro'>❌ Arquivo .htaccess não encontrado</span>";
}

// Verifica se index-mvc.php existe
if (file_exists(__DIR__ . '/index-mvc.php')) {
    $diagnostics[] = "<span class='ok'>✅ index-mvc.php existe</span>";
} else {
    $diagnostics[] = "<span class='erro'>❌ index-mvc.php não encontrado</span>";
}

// Verifica mod_rewrite
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        $diagnostics[] = "<span class='ok'>✅ mod_rewrite está habilitado</span>";
    } else {
        $diagnostics[] = "<span class='aviso'>⚠️ mod_rewrite não está habilitado</span>";
    }
} else {
    $diagnostics[] = "<span class='aviso'>⚠️ Não foi possível verificar mod_rewrite</span>";
}

foreach ($diagnostics as $diag) {
    echo "<p>{$diag}</p>";
}

echo "</div>";

echo "<hr>";
echo "<p><small>Última atualização: " . date('d/m/Y H:i:s') . "</small></p>";
echo "</body></html>";

