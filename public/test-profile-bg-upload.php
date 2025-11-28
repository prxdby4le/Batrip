<?php
/**
 * Script de teste para verificar upload de profile_bg
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';

$destDir = ROOT_PATH . '/public/uploads/profile_bg/';
$destDir = str_replace(['//', '\\'], '/', $destDir);

echo "<h1>Teste de Upload de Profile Background</h1>\n";
echo "<pre>\n";

echo "ROOT_PATH: " . ROOT_PATH . "\n";
echo "DestDir: $destDir\n";
echo "Diretório existe: " . (is_dir($destDir) ? 'SIM' : 'NÃO') . "\n";

if (!is_dir($destDir)) {
    if (mkdir($destDir, 0777, true)) {
        echo "✓ Diretório criado com sucesso\n";
    } else {
        echo "✗ ERRO ao criar diretório\n";
    }
} else {
    echo "✓ Diretório já existe\n";
}

if (is_dir($destDir)) {
    echo "Permissões: " . substr(sprintf('%o', fileperms($destDir)), -4) . "\n";
    echo "Gravável: " . (is_writable($destDir) ? 'SIM' : 'NÃO') . "\n";
}

// Lista arquivos existentes
echo "\n=== Arquivos existentes ===\n";
$files = glob($destDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
if (empty($files)) {
    echo "Nenhum arquivo encontrado\n";
} else {
    foreach ($files as $file) {
        echo "  - " . basename($file) . " (" . number_format(filesize($file)) . " bytes)\n";
    }
}

// Testa se consegue criar um arquivo de teste
echo "\n=== Teste de escrita ===\n";
$testFile = $destDir . 'test_' . time() . '.txt';
if (file_put_contents($testFile, 'test')) {
    echo "✓ Arquivo de teste criado: " . basename($testFile) . "\n";
    unlink($testFile);
    echo "✓ Arquivo de teste removido\n";
} else {
    echo "✗ ERRO ao criar arquivo de teste\n";
}

// Verifica o Alias do Apache
echo "\n=== Verificação de Alias ===\n";
echo "URL esperada: http://localhost:8080/uploads/profile_bg/\n";
echo "Caminho físico: $destDir\n";

echo "\n✓ Teste concluído!\n";
echo "</pre>\n";
?>

