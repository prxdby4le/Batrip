<?php
/**
 * Script de Teste de Compatibilidade Linux
 * Verifica se o projeto está pronto para execução no Linux
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=" . str_repeat("=", 69) . "\n";
echo "TESTE DE COMPATIBILIDADE LINUX - PROJETO BATRIP\n";
echo "=" . str_repeat("=", 69) . "\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Testa autoloader
echo "1. Testando Autoloader...\n";
try {
    require_once __DIR__ . '/../autoload.php';
    $success[] = "Autoloader carregado";
    echo "   ✓ Autoloader carregado\n";
} catch (Exception $e) {
    $errors[] = "Erro ao carregar autoloader: " . $e->getMessage();
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
}

// 2. Testa carregamento de classes principais
echo "\n2. Testando Carregamento de Classes...\n";
$test_classes = [
    'App\Core\Controller',
    'App\Core\Router',
    'App\Core\Request',
    'App\Core\Model',
    'App\Models\Product',
    'App\Models\User',
    'App\Models\Order',
    'App\Helpers\CartHelper',
    'App\Helpers\CsrfHelper',
];

foreach ($test_classes as $class) {
    if (class_exists($class)) {
        echo "   ✓ $class\n";
        $success[] = "Classe $class carregada";
    } else {
        echo "   ✗ $class não encontrada\n";
        $errors[] = "Classe não encontrada: $class";
    }
}

// 3. Testa configurações
echo "\n3. Testando Configurações...\n";
$config_files = [
    'config/config.php',
    'config/Routes.php',
    'includes/db.php',
];

foreach ($config_files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        echo "   ✓ $file existe\n";
        $success[] = "Arquivo $file existe";
    } else {
        echo "   ✗ $file não encontrado\n";
        $errors[] = "Arquivo não encontrado: $file";
    }
}

// 4. Testa constantes
echo "\n4. Testando Constantes...\n";
if (defined('ROOT_PATH')) {
    echo "   ✓ ROOT_PATH definido: " . ROOT_PATH . "\n";
    $success[] = "ROOT_PATH definido";
} else {
    echo "   ⚠️  ROOT_PATH não definido\n";
    $warnings[] = "ROOT_PATH não definido";
}

if (defined('BASE_URL')) {
    echo "   ✓ BASE_URL definido: " . BASE_URL . "\n";
    $success[] = "BASE_URL definido";
} else {
    echo "   ⚠️  BASE_URL não definido (pode ser definido depois)\n";
    $warnings[] = "BASE_URL não definido";
}

// 5. Testa diretórios
echo "\n5. Testando Diretórios...\n";
$dirs = [
    'public/uploads',
    'public/uploads/products',
    'public/uploads/profile_bg',
    'public/assets',
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/../' . $dir;
    if (is_dir($path)) {
        $writable = is_writable($path);
        $status = $writable ? "✓" : "⚠️";
        $perm = substr(sprintf('%o', fileperms($path)), -4);
        echo "   $status $dir (perm: $perm)\n";
        if ($writable) {
            $success[] = "Diretório $dir é gravável";
        } else {
            $warnings[] = "Diretório $dir não é gravável";
        }
    } else {
        echo "   ✗ $dir não existe\n";
        $errors[] = "Diretório não existe: $dir";
    }
}

// 6. Testa case sensitivity
echo "\n6. Testando Case Sensitivity...\n";
$test_files = [
    'app/Core/Controller.php',
    'app/Models/Product.php',
    'config/Routes.php',
];

foreach ($test_files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        $basename = basename($path);
        // Verifica se o nome do arquivo começa com maiúscula (PascalCase)
        if (ctype_upper(substr($basename, 0, 1))) {
            echo "   ✓ $file (PascalCase)\n";
            $success[] = "Arquivo $file em PascalCase";
        } else {
            echo "   ⚠️  $file (não PascalCase)\n";
            $warnings[] = "Arquivo $file não está em PascalCase";
        }
    }
}

// 7. Testa conexão com banco (se possível)
echo "\n7. Testando Conexão com Banco...\n";
try {
    require_once __DIR__ . '/../includes/db.php';
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        echo "   ✓ Conexão PDO disponível\n";
        $success[] = "Conexão PDO disponível";
    } else {
        echo "   ⚠️  PDO não disponível (pode ser normal se banco não estiver rodando)\n";
        $warnings[] = "PDO não disponível";
    }
} catch (Exception $e) {
    echo "   ⚠️  Erro ao conectar: " . $e->getMessage() . " (pode ser normal se banco não estiver rodando)\n";
    $warnings[] = "Erro de conexão: " . $e->getMessage();
}

// Resumo
echo "\n" . str_repeat("=", 70) . "\n";
echo "RESUMO\n";
echo str_repeat("=", 70) . "\n";
echo "✓ Sucessos: " . count($success) . "\n";
echo "⚠️  Avisos: " . count($warnings) . "\n";
echo "✗ Erros: " . count($errors) . "\n\n";

if (count($errors) === 0) {
    echo "✅ PROJETO PRONTO PARA LINUX!\n";
    echo "   Todos os testes críticos passaram.\n";
    exit(0);
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
    exit(1);
}

