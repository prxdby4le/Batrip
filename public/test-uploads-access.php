<?php
/**
 * Script para testar acesso aos arquivos em /uploads/
 */

$testFile = '/var/www/html/public/uploads/profile_bg/bg_2_1764301891.jpg';

echo "<h1>Teste de Acesso a /uploads/</h1>\n";
echo "<pre>\n";

echo "Arquivo físico: $testFile\n";
echo "Existe: " . (file_exists($testFile) ? 'SIM' : 'NÃO') . "\n";

if (file_exists($testFile)) {
    echo "Tamanho: " . number_format(filesize($testFile)) . " bytes\n";
    echo "Permissões: " . substr(sprintf('%o', fileperms($testFile)), -4) . "\n";
    echo "Legível: " . (is_readable($testFile) ? 'SIM' : 'NÃO') . "\n";
}

echo "\nURL esperada: http://localhost:8080/uploads/profile_bg/bg_2_1764301891.jpg\n";
echo "Caminho relativo ao DocumentRoot: /uploads/profile_bg/bg_2_1764301891.jpg\n";

echo "\n=== Teste de leitura direta ===\n";
if (file_exists($testFile)) {
    $content = file_get_contents($testFile);
    if ($content !== false) {
        echo "✓ Arquivo pode ser lido diretamente (" . strlen($content) . " bytes)\n";
        echo "Tipo MIME detectado: " . mime_content_type($testFile) . "\n";
    } else {
        echo "✗ ERRO ao ler arquivo\n";
    }
}

echo "\n=== Verificação de .htaccess ===\n";
$htaccess = '/var/www/html/public/.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    if (strpos($content, '^/uploads/') !== false) {
        echo "✓ Regra para /uploads/ encontrada no .htaccess\n";
    } else {
        echo "✗ Regra para /uploads/ NÃO encontrada no .htaccess\n";
    }
} else {
    echo "✗ .htaccess não encontrado\n";
}

echo "\n✓ Teste concluído!\n";
echo "</pre>\n";
?>

