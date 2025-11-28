<?php
/**
 * Script para verificar e copiar arquivos de uploads/profile_bg/
 * de uploads/ para public/uploads/
 */

$rootPath = dirname(__DIR__);
$sourceDir = $rootPath . '/uploads/profile_bg';
$destDir = $rootPath . '/public/uploads/profile_bg';

echo "<h1>Correção de Alias /uploads/</h1>\n";
echo "<pre>\n";

// Verifica se o diretório de origem existe
if (is_dir($sourceDir)) {
    echo "✓ Diretório de origem encontrado: $sourceDir\n";
    
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
    
    // Busca todos os arquivos no diretório de origem
    $files = glob($sourceDir . '/*');
    $copiedCount = 0;
    
    if (empty($files)) {
        echo "\n⚠ Nenhum arquivo encontrado em: $sourceDir\n";
    } else {
        echo "\n=== Copiando arquivos ===\n";
        foreach ($files as $sourceFile) {
            if (is_file($sourceFile)) {
                $filename = basename($sourceFile);
                $destFile = $destDir . '/' . $filename;
                
                // Só copia se o arquivo de destino não existir ou for mais antigo
                if (!file_exists($destFile) || filemtime($sourceFile) > filemtime($destFile)) {
                    if (copy($sourceFile, $destFile)) {
                        echo "✓ Copiado: $filename (" . number_format(filesize($destFile)) . " bytes)\n";
                        $copiedCount++;
                    } else {
                        echo "✗ ERRO ao copiar: $filename\n";
                    }
                } else {
                    echo "⊘ Já existe (mais recente): $filename\n";
                }
            }
        }
    }
} else {
    echo "⚠ Diretório de origem não encontrado: $sourceDir\n";
    echo "  (Isso é normal se os arquivos já estão em public/uploads/)\n";
}

// Lista todos os arquivos no diretório de destino
echo "\n=== Arquivos em $destDir ===\n";
$destFiles = glob($destDir . '/*');
if (empty($destFiles)) {
    echo "  (nenhum arquivo)\n";
} else {
    foreach ($destFiles as $file) {
        if (is_file($file)) {
            echo "  - " . basename($file) . " (" . number_format(filesize($file)) . " bytes)\n";
        }
    }
}

// Verifica se o arquivo específico existe
$specificFile = 'bg_2_1764284202.jpeg';
$specificPath = $destDir . '/' . $specificFile;
if (file_exists($specificPath)) {
    echo "\n✓ Arquivo específico encontrado: $specificFile\n";
    echo "  Tamanho: " . number_format(filesize($specificPath)) . " bytes\n";
    echo "  URL: http://localhost:8080/uploads/profile_bg/$specificFile\n";
} else {
    echo "\n⚠ Arquivo específico NÃO encontrado: $specificFile\n";
}

echo "\n✓ Processo concluído! Total de arquivos copiados: $copiedCount\n";
echo "</pre>\n";
?>

