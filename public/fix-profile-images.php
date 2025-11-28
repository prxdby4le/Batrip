<?php
/**
 * Script para copiar imagens de perfil de assets/img/perfil/ para public/assets/img/perfil/
 */

$rootPath = dirname(__DIR__);
$sourceDir = $rootPath . '/assets/img/perfil';
$destDir = $rootPath . '/public/assets/img/perfil';

echo "<h1>Correção de Imagens de Perfil</h1>\n";
echo "<pre>\n";

// Verifica se o diretório de origem existe
if (!is_dir($sourceDir)) {
    echo "ERRO: Diretório de origem não encontrado: $sourceDir\n";
    exit(1);
}

echo "✓ Diretório de origem encontrado: $sourceDir\n\n";

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

// Busca todos os arquivos .jpg no diretório de origem
$files = glob($sourceDir . '/*.jpg');
$copiedCount = 0;

if (empty($files)) {
    echo "\n⚠ Nenhum arquivo .jpg encontrado em: $sourceDir\n";
} else {
    echo "\n=== Copiando arquivos ===\n";
    foreach ($files as $sourceFile) {
        $filename = basename($sourceFile);
        $destFile = $destDir . '/' . $filename;
        
        if (copy($sourceFile, $destFile)) {
            echo "✓ Copiado: $filename (" . number_format(filesize($destFile)) . " bytes)\n";
            $copiedCount++;
        } else {
            echo "✗ ERRO ao copiar: $filename\n";
        }
    }
}

// Lista todos os arquivos no diretório de destino
echo "\n=== Arquivos em $destDir ===\n";
$destFiles = glob($destDir . '/*.jpg');
if (empty($destFiles)) {
    echo "  (nenhum arquivo)\n";
} else {
    foreach ($destFiles as $file) {
        if (is_file($file)) {
            echo "  - " . basename($file) . " (" . number_format(filesize($file)) . " bytes)\n";
        }
    }
}

echo "\n✓ Processo concluído! Total de arquivos copiados: $copiedCount\n";
echo "</pre>\n";
?>

