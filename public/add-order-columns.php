<?php
/**
 * Script para adicionar colunas na tabela orders
 * Execute: http://localhost:8080/add-order-columns.php
 */

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Adicionando colunas na tabela orders ===\n\n";

$columns = [
    'customer_name' => "VARCHAR(255) NULL AFTER user_id",
    'customer_email' => "VARCHAR(255) NULL AFTER customer_name",
    'customer_phone' => "VARCHAR(20) NULL AFTER customer_email",
    'shipping_address' => "VARCHAR(500) NULL AFTER customer_phone",
    'shipping_city' => "VARCHAR(100) NULL AFTER shipping_address",
    'shipping_state' => "VARCHAR(2) NULL AFTER shipping_city",
    'shipping_zipcode' => "VARCHAR(10) NULL AFTER shipping_state",
    'shipping_method' => "VARCHAR(100) NULL AFTER shipping_zipcode",
    'shipping_cost' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shipping_method",
    'payment_method' => "VARCHAR(50) NULL DEFAULT 'pix' AFTER shipping_cost"
];

$added = 0;
$skipped = 0;
$errors = 0;

foreach ($columns as $columnName => $columnDefinition) {
    // Verificar se a coluna já existe
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'orders'
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$columnName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo "✓ Coluna '{$columnName}' já existe.\n";
            $skipped++;
            continue;
        }
    } catch (PDOException $e) {
        echo "✗ Erro ao verificar coluna '{$columnName}': " . $e->getMessage() . "\n";
        $errors++;
        continue;
    }
    
    // Adicionar coluna
    try {
        $sql = "ALTER TABLE orders ADD COLUMN `{$columnName}` {$columnDefinition}";
        $pdo->exec($sql);
        echo "✓ Coluna '{$columnName}' adicionada com sucesso!\n";
        $added++;
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        if ($errorCode == 1060 || strpos($errorMsg, 'Duplicate') !== false) {
            echo "✓ Coluna '{$columnName}' já existe.\n";
            $skipped++;
        } else {
            echo "✗ Erro ao adicionar coluna '{$columnName}': " . $errorMsg . "\n";
            $errors++;
        }
    }
}

echo "\n=== Resumo ===\n";
echo "Colunas adicionadas: {$added}\n";
echo "Colunas já existentes: {$skipped}\n";
echo "Erros: {$errors}\n";

if ($errors === 0) {
    echo "\n✓ Concluído com sucesso!\n";
} else {
    echo "\n✗ Concluído com erros.\n";
}

