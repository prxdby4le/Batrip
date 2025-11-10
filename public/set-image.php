<?php
// Servidor de imagens para conjuntos: /set-image.php?id=ID&size=thumb|medium|large
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$size = isset($_GET['size']) ? strtolower(trim($_GET['size'])) : '';
if (!in_array($size, ['thumb', 'medium', 'large', ''], true)) { $size = ''; }

if ($id <= 0) {
    http_response_code(400);
    echo 'ID inválido';
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT image FROM sets WHERE id = ? AND active = 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); echo 'Imagem não encontrada'; exit; }
    $basePath = __DIR__ . '/../';
    $rel = ltrim((string)$row['image'], '/');
    $origPath = realpath($basePath . $rel);

    $servePath = $origPath;
    if ($size !== '') {
        // Tentar subpastas: assets/img/sets/{size}/<filename>
        $dir = dirname($rel);
        $filename = basename($rel);
        $candidate = realpath($basePath . $dir . '/' . $size . '/' . $filename);
        if ($candidate && is_file($candidate)) {
            $servePath = $candidate;
        }
    }

    if (!$servePath || !is_file($servePath)) {
        http_response_code(404);
        echo 'Arquivo não encontrado';
        exit;
    }

    $ext = strtolower(pathinfo($servePath, PATHINFO_EXTENSION));
    $mime = 'image/jpeg';
    if ($ext === 'png') $mime = 'image/png';
    if ($ext === 'webp') $mime = 'image/webp';

    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    readfile($servePath);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erro ao carregar imagem';
}
