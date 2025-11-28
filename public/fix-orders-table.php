<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';

echo "<pre>";
echo "=== Verificando e adicionando colunas na tabela orders ===\n\n";

// Verificar colunas existentes
$stmt = $pdo->query("SHOW COLUMNS FROM orders");
$existing = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing[] = $row['Field'];
}

echo "Colunas existentes: " . count($existing) . "\n";
echo implode(", ", $existing) . "\n\n";

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

foreach ($columns as $name => $def) {
    if (in_array($name, $existing)) {
        echo "✓ {$name} já existe\n";
        $skipped++;
        continue;
    }
    
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN `{$name}` {$def}");
        echo "✓ {$name} adicionada\n";
        $added++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || $e->getCode() == 1060) {
            echo "✓ {$name} já existe (duplicado)\n";
            $skipped++;
        } else {
            echo "✗ Erro em {$name}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
}

echo "\n=== Resumo ===\n";
echo "Adicionadas: {$added}\n";
echo "Já existiam: {$skipped}\n";
echo "Erros: {$errors}\n";

if ($errors === 0) {
    echo "\n✓ Concluído!\n";
} else {
    echo "\n✗ Concluído com erros.\n";
}

echo "</pre>";

