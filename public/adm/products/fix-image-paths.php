<?php
// Script para corrigir caminhos de imagens inválidos no banco de dados para products e product_images
// Execute manualmente uma vez (ex: php fix-image-paths.php)

require_once __DIR__ . '/../../../includes/db.php';

function fix_path($url) {
    $url = trim((string)$url);
    if ($url === '') return '';
    // Se já for URL externa ou já estiver correto, retorna como está
    if (preg_match('~^https?://~i', $url) || strpos($url, 'assets/img/uploads/') === 0) return $url;
    // Se for só o nome do arquivo, tenta corrigir
    if (preg_match('~^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif|webp|svg)$~i', $url)) {
        return 'assets/img/uploads/' . $url;
    }
    // Se for só o nome com subpasta uploads
    if (preg_match('~(?:^|/)uploads/([a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif|webp|svg))$~i', $url, $m)) {
        return 'assets/img/uploads/' . $m[1];
    }
    // Se for só o nome com subpasta img
    if (preg_match('~(?:^|/)img/([a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif|webp|svg))$~i', $url, $m)) {
        return 'assets/img/uploads/' . $m[1];
    }
    // Se for só o nome com subpasta assets/img
    if (preg_match('~(?:^|/)assets/img/([a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif|webp|svg))$~i', $url, $m)) {
        return 'assets/img/uploads/' . $m[1];
    }
    // Se for só o nome com subpasta assets/img/uploads
    if (preg_match('~(?:^|/)assets/img/uploads/([a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif|webp|svg))$~i', $url, $m)) {
        return 'assets/img/uploads/' . $m[1];
    }
    // Se não conseguir corrigir, retorna original
    return $url;
}

// Corrige products.image
$stmt = $pdo->query('SELECT id, image FROM products');
$updates = 0;
foreach ($stmt as $row) {
    $fixed = fix_path($row['image']);
    if ($fixed !== $row['image']) {
        $upd = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
        $upd->execute([$fixed, $row['id']]);
        $updates++;
        echo "Corrigido products.id={$row['id']}: {$row['image']} => $fixed\n";
    }
}

// Corrige product_images.url
$stmt = $pdo->query('SELECT id, url FROM product_images');
foreach ($stmt as $row) {
    $fixed = fix_path($row['url']);
    if ($fixed !== $row['url']) {
        $upd = $pdo->prepare('UPDATE product_images SET url = ? WHERE id = ?');
        $upd->execute([$fixed, $row['id']]);
        $updates++;
        echo "Corrigido product_images.id={$row['id']}: {$row['url']} => $fixed\n";
    }
}

echo "Total de caminhos corrigidos: $updates\n";
