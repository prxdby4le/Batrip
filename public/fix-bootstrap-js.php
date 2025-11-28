<?php
/**
 * Script para copiar arquivos JavaScript do Bootstrap para public/assets/js/bootstrap-js/
 */

$rootPath = dirname(__DIR__);
$sourceFile = $rootPath . '/assets/js/bootstrap-js/bootstrap.bundle.min.js';
$destDir = $rootPath . '/public/assets/js/bootstrap-js';
$destFile = $destDir . '/bootstrap.bundle.min.js';

echo "<h1>Correção de Arquivos JavaScript do Bootstrap</h1>\n";
echo "<pre>\n";

// Verifica se o arquivo de origem existe
if (!file_exists($sourceFile)) {
    echo "ERRO: Arquivo de origem não encontrado: $sourceFile\n";
    exit(1);
}

echo "✓ Arquivo de origem encontrado: $sourceFile\n";
echo "  Tamanho: " . number_format(filesize($sourceFile)) . " bytes\n\n";

// Cria o diretório de destino se não existir
if (!is_dir($destDir)) {
    if (mkdir($destDir, 0755, true)) {
        echo "✓ Diretório criado: $destDir\n";
    } else {
        echo "ERRO: Não foi possível criar o diretório: $destDir\n";
        exit(1);
    }
} else {
    echo "✓ Diretório já existe: $destDir\n";
}

// Copia o arquivo
if (copy($sourceFile, $destFile)) {
    echo "✓ Arquivo copiado com sucesso: $destFile\n";
    echo "  Tamanho: " . number_format(filesize($destFile)) . " bytes\n";
} else {
    echo "ERRO: Não foi possível copiar o arquivo.\n";
    exit(1);
}

// Verifica se o arquivo foi copiado corretamente
if (file_exists($destFile)) {
    echo "\n✓ VERIFICAÇÃO: Arquivo existe em: $destFile\n";
    echo "  Tamanho: " . number_format(filesize($destFile)) . " bytes\n";
    echo "  Permissões: " . substr(sprintf('%o', fileperms($destFile)), -4) . "\n";
} else {
    echo "\nERRO: Arquivo não foi encontrado após a cópia.\n";
    exit(1);
}

// Lista todos os arquivos no diretório bootstrap-js
echo "\n=== Arquivos em $destDir ===\n";
$files = glob($destDir . '/*');
foreach ($files as $file) {
    if (is_file($file)) {
        echo "  - " . basename($file) . " (" . number_format(filesize($file)) . " bytes)\n";
    }
}

echo "\n✓ Processo concluído com sucesso!\n";
echo "</pre>\n";
?>

