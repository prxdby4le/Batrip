<?php
/**
 * Script para servir arquivos de upload diretamente
 * Fallback caso o .htaccess não esteja funcionando corretamente
 */

// Pega o caminho do arquivo da query string
$filePath = $_GET['file'] ?? '';

if (empty($filePath)) {
    http_response_code(400);
    die('File parameter required');
}

// Remove qualquer tentativa de path traversal
$filePath = str_replace(['..', '\\'], '', $filePath);
$filePath = ltrim($filePath, '/');

// Constrói o caminho completo
$fullPath = __DIR__ . '/' . $filePath;

// Valida que o arquivo está dentro do diretório permitido
$realPath = realpath($fullPath);
$allowedDir = realpath(__DIR__);
if (!$realPath || strpos($realPath, $allowedDir) !== 0) {
    http_response_code(403);
    die('Access denied');
}

if (!file_exists($realPath) || !is_file($realPath)) {
    http_response_code(404);
    die('File not found');
}

// Determina o tipo MIME
$mimeType = mime_content_type($realPath);
if (!$mimeType) {
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
    ];
    $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
}

// Envia o arquivo
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: public, max-age=31536000');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($realPath)) . ' GMT');
readfile($realPath);
exit;
?>

