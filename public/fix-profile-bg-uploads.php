<?php
/**
 * Script de correção: Copia arquivos de background de perfil para public/uploads/profile_bg/
 * Execute este arquivo uma vez via navegador para migrar os arquivos
 */

header('Content-Type: text/html; charset=utf-8');

$sourceDir = dirname(__DIR__) . '/uploads/profile_bg/';
$targetDir = __DIR__ . '/uploads/profile_bg/';

echo "<h2>Migração de Arquivos de Background de Perfil</h2>";
echo "<pre>";

// Criar diretório de destino
if (!is_dir($targetDir)) {
    if (mkdir($targetDir, 0777, true)) {
        echo "✓ Diretório criado: $targetDir\n\n";
    } else {
        echo "✗ Erro ao criar diretório: $targetDir\n\n";
        exit;
    }
} else {
    echo "✓ Diretório já existe: $targetDir\n\n";
}

// Verificar diretório de origem
if (!is_dir($sourceDir)) {
    echo "⚠ AVISO: Diretório de origem não encontrado: $sourceDir\n";
    echo "Nenhum arquivo para migrar.\n";
    exit;
}

echo "✓ Diretório de origem encontrado: $sourceDir\n\n";

// Buscar arquivos
$files = [];
$extensions = ['jpg', 'jpeg', 'png', 'webp'];
foreach ($extensions as $ext) {
    $pattern = $sourceDir . '*.' . $ext;
    $found = glob($pattern);
    if ($found) {
        $files = array_merge($files, $found);
    }
}

if (empty($files)) {
    echo "Nenhum arquivo para migrar.\n";
    exit;
}

echo "Encontrados " . count($files) . " arquivo(s) para migrar:\n\n";

$copied = 0;
$skipped = 0;
$errors = 0;

foreach ($files as $sourceFile) {
    $filename = basename($sourceFile);
    $targetFile = $targetDir . $filename;
    
    if (file_exists($targetFile)) {
        echo "[SKIP] $filename (já existe)\n";
        $skipped++;
        continue;
    }
    
    if (@copy($sourceFile, $targetFile)) {
        chmod($targetFile, 0644);
        echo "[OK] $filename\n";
        $copied++;
    } else {
        $error = error_get_last();
        echo "[ERRO] $filename - " . ($error['message'] ?? 'Erro desconhecido') . "\n";
        $errors++;
    }
}

echo "\n=== Resumo ===\n";
echo "Copiados: $copied\n";
echo "Ignorados: $skipped\n";
echo "Erros: $errors\n\n";

// Verificar arquivo específico
$specificFile = 'bg_2_1764283275.jpg';
$targetPath = $targetDir . $specificFile;
if (file_exists($targetPath)) {
    echo "✓ Arquivo específico existe: $specificFile\n";
    echo "  Caminho: /uploads/profile_bg/$specificFile\n";
    echo "  Tamanho: " . number_format(filesize($targetPath)) . " bytes\n";
    echo "\n✅ Migração concluída! O arquivo deve estar acessível agora.\n";
} else {
    echo "✗ Arquivo específico NÃO encontrado: $specificFile\n";
    echo "  Verifique se o arquivo existe na origem.\n";
}

echo "</pre>";
?>

