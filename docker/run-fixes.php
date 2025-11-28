<?php
/**
 * Script de Execução Automática de Correções e Migrações
 * 
 * Este script executa automaticamente todos os arquivos de correção e migração
 * que anteriormente precisavam ser acessados manualmente via navegador.
 * 
 * Executado automaticamente durante a inicialização do container Docker.
 */

// Define ROOT_PATH se necessário
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Carrega configurações
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/includes/db.php';

// Lista de scripts a serem executados (em ordem)
$fixScripts = [
    'fix-orders-table.php',
    'fix-profile-bg-uploads.php',
    'fix-profile-images.php',
    'fix-uploads-alias.php',
    'fix-bootstrap-js.php',
    'migrate-profile-bg.php',
    'run-order-migration.php',
    'add-order-columns.php',
];

$publicPath = ROOT_PATH . '/public';
$executed = [];
$errors = [];

echo "🔧 Executando scripts de correção e migração...\n\n";

foreach ($fixScripts as $script) {
    $scriptPath = $publicPath . '/' . $script;
    
    if (!file_exists($scriptPath)) {
        echo "⚠️  Script não encontrado: {$script}\n";
        continue;
    }
    
    echo "▶️  Executando: {$script}...\n";
    
    try {
        // Captura output do script
        ob_start();
        
        // Suprime headers HTML se o script tentar enviar
        @header_remove();
        
        // Inclui o script (ele será executado)
        include $scriptPath;
        
        $output = ob_get_clean();
        
        // Remove tags HTML e limpa o output
        $output = strip_tags($output);
        $output = preg_replace('/\s+/', ' ', $output);
        $output = trim($output);
        
        if (!empty($output) && strlen($output) > 10) {
            // Mostra apenas primeiras 200 caracteres se muito longo
            $displayOutput = strlen($output) > 200 ? substr($output, 0, 200) . '...' : $output;
            echo "   Output: " . $displayOutput . "\n";
        }
        
        $executed[] = $script;
        echo "   ✅ Concluído\n\n";
        
    } catch (Exception $e) {
        $errors[] = [
            'script' => $script,
            'error' => $e->getMessage()
        ];
        ob_end_clean();
        echo "   ❌ Erro: " . $e->getMessage() . "\n\n";
    } catch (Error $e) {
        $errors[] = [
            'script' => $script,
            'error' => $e->getMessage()
        ];
        ob_end_clean();
        echo "   ❌ Erro: " . $e->getMessage() . "\n\n";
    } catch (Throwable $e) {
        $errors[] = [
            'script' => $script,
            'error' => $e->getMessage()
        ];
        ob_end_clean();
        echo "   ❌ Erro: " . $e->getMessage() . "\n\n";
    }
}

// Resumo
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Resumo da execução:\n";
echo "   ✅ Executados com sucesso: " . count($executed) . "\n";
echo "   ❌ Erros: " . count($errors) . "\n\n";

if (!empty($executed)) {
    echo "Scripts executados:\n";
    foreach ($executed as $script) {
        echo "   ✓ {$script}\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "Scripts com erro:\n";
    foreach ($errors as $error) {
        echo "   ✗ {$error['script']}: {$error['error']}\n";
    }
    echo "\n";
}

echo "✅ Execução de correções concluída.\n";

