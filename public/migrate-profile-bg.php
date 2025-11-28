<?php
/**
 * Script de migração: Move arquivos de background de perfil de uploads/ para public/uploads/
 */

header('Content-Type: text/plain; charset=utf-8');

define('ROOT_PATH', dirname(__DIR__));

$sourceDir = ROOT_PATH . '/uploads/profile_bg/';
$targetDir = __DIR__ . '/uploads/profile_bg/';

echo "=== Migração de Arquivos de Background de Perfil ===\n\n";

// Criar diretório de destino se não existir
if (!is_dir($targetDir)) {
    if (mkdir($targetDir, 0777, true)) {
        echo "✓ Diretório criado: $targetDir\n";
    } else {
        echo "✗ Erro ao criar diretório: $targetDir\n";
        exit(1);
    }
} else {
    echo "✓ Diretório já existe: $targetDir\n";
}

// Verificar diretório de origem
if (!is_dir($sourceDir)) {
    echo "⚠ AVISO: Diretório de origem não encontrado: $sourceDir\n";
    echo "Nenhum arquivo para migrar.\n";
    exit(0);
}

echo "✓ Diretório de origem encontrado: $sourceDir\n\n";

// Listar arquivos no diretório de origem (método alternativo para compatibilidade)
$files = [];
$patterns = ['*.jpg', '*.jpeg', '*.png', '*.webp'];
foreach ($patterns as $pattern) {
    $found = glob($sourceDir . $pattern);
    if ($found) {
        $files = array_merge($files, $found);
    }
}

// Remove duplicatas
$files = array_unique($files);

if (empty($files)) {
    echo "Nenhum arquivo para migrar.\n";
    exit(0);
}

echo "Encontrados " . count($files) . " arquivo(s) para migrar:\n\n";

$copied = 0;
$skipped = 0;
$errors = 0;

foreach ($files as $sourceFile) {
    $filename = basename($sourceFile);
    $targetFile = $targetDir . $filename;
    
    // Se já existe no destino, verificar se precisa atualizar
    if (file_exists($targetFile)) {
        $sourceSize = filesize($sourceFile);
        $targetSize = filesize($targetFile);
        if ($sourceSize === $targetSize) {
            echo "  [SKIP] $filename (já existe no destino, tamanhos iguais)\n";
            $skipped++;
            continue;
        } else {
            echo "  [ATUALIZAR] $filename (tamanhos diferentes, substituindo)\n";
        }
    }
    
    // Copiar arquivo
    if (@copy($sourceFile, $targetFile)) {
        // Garantir permissões corretas
        chmod($targetFile, 0644);
        echo "  [OK] $filename\n";
        $copied++;
    } else {
        echo "  [ERRO] $filename (falha ao copiar: " . error_get_last()['message'] . ")\n";
        $errors++;
    }
}

echo "\n=== Resumo ===\n";
echo "Copiados: $copied\n";
echo "Ignorados: $skipped\n";
echo "Erros: $errors\n";

// Verificar arquivo específico mencionado no erro
$specificFile = $targetDir . 'bg_2_1764283275.jpg';
if (file_exists($specificFile)) {
    echo "\n✓ Arquivo específico existe: bg_2_1764283275.jpg\n";
    echo "  Caminho completo: $specificFile\n";
    echo "  Tamanho: " . filesize($specificFile) . " bytes\n";
    echo "  Permissões: " . substr(sprintf('%o', fileperms($specificFile)), -4) . "\n";
} else {
    echo "\n✗ Arquivo específico NÃO encontrado: bg_2_1764283275.jpg\n";
}

echo "\nMigração concluída!\n";
?>

