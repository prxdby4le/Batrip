<?php
// Script para remover imagens órfãs do banco de dados (product_images sem produto correspondente)
require_once __DIR__ . '/../includes/db.php';

try {
    $sql = "DELETE FROM product_images WHERE product_id NOT IN (SELECT id FROM products)";
    $count = $pdo->exec($sql);
    echo "Imagens órfãs removidas: $count\n";
} catch (Throwable $e) {
    echo "Erro ao remover imagens órfãs: " . $e->getMessage() . "\n";
    exit(1);
}
