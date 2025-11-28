<?php
/**
 * Script para executar migration de colunas da tabela orders
 * Execute via navegador: http://localhost:8080/run-order-migration.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration: Adicionar Colunas na Tabela Orders</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .skip { color: #ff0; }
        h1 { color: #fff; }
    </style>
</head>
<body>
    <h1>=== Migration: Adicionar Colunas na Tabela Orders ===</h1>
    <pre>
<?php

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
            echo "<span class='skip'>✓ Coluna '{$columnName}' já existe. Pulando...</span>\n";
            $skipped++;
            continue;
        }
    } catch (PDOException $e) {
        echo "<span class='error'>✗ Erro ao verificar coluna '{$columnName}': " . htmlspecialchars($e->getMessage()) . "</span>\n";
        $errors++;
        continue;
    }
    
    // Adicionar coluna
    try {
        $sql = "ALTER TABLE orders ADD COLUMN `{$columnName}` {$columnDefinition}";
        $pdo->exec($sql);
        echo "<span class='success'>✓ Coluna '{$columnName}' adicionada com sucesso!</span>\n";
        $added++;
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        // Se a coluna já existe, ignorar o erro (código 1060 ou mensagem específica)
        if ($errorCode == 1060 || 
            strpos($errorMsg, 'Duplicate column name') !== false || 
            strpos($errorMsg, '1060') !== false ||
            stripos($errorMsg, 'already exists') !== false ||
            stripos($errorMsg, 'duplicate') !== false) {
            echo "<span class='skip'>✓ Coluna '{$columnName}' já existe. Pulando...</span>\n";
            $skipped++;
        } else {
            echo "<span class='error'>✗ Erro ao adicionar coluna '{$columnName}' (Código: {$errorCode}): " . htmlspecialchars($errorMsg) . "</span>\n";
            $errors++;
        }
    }
}

echo "\n=== Resumo ===\n";
echo "<span class='success'>Colunas adicionadas: {$added}</span>\n";
echo "<span class='skip'>Colunas já existentes (puladas): {$skipped}</span>\n";
if ($errors > 0) {
    echo "<span class='error'>Erros: {$errors}</span>\n";
}

if ($errors === 0) {
    echo "\n<span class='success'>✓ Migration concluída com sucesso!</span>\n";
    echo "\nVocê pode fechar esta página e tentar finalizar o pedido novamente.\n";
} else {
    echo "\n<span class='error'>✗ Migration concluída com erros. Verifique os logs acima.</span>\n";
}
?>
    </pre>
</body>
</html>
