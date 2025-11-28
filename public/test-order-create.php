<?php
/**
 * Script de teste para verificar se Order::create está funcionando
 * Execute: http://localhost:8080/test-order-create.php
 */

// Definir constantes necessárias
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8080/');
}

// Carregar autoloader
require_once ROOT_PATH . '/autoload.php';

// Carregar configurações
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/config.php';

// Carregar banco de dados
require_once ROOT_PATH . '/includes/db.php';

use App\Models\Order;

header('Content-Type: text/plain; charset=utf-8');

echo "=== Teste do Order::create ===\n\n";

// Verificar colunas existentes
$stmt = $pdo->query("SHOW COLUMNS FROM orders");
$columns = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $columns[] = $row['Field'];
}

echo "Colunas na tabela orders:\n";
foreach ($columns as $col) {
    echo "  - {$col}\n";
}

echo "\n=== Testando Order::create ===\n\n";

$orderModel = new Order();

// Dados de teste
$testData = [
    'user_id' => 1,
    'customer_name' => 'Teste',
    'customer_email' => 'teste@teste.com',
    'customer_phone' => '123456789',
    'shipping_address' => 'Rua Teste, 123',
    'shipping_city' => 'São Paulo',
    'shipping_state' => 'SP',
    'shipping_zipcode' => '01234567',
    'shipping_method' => 'PAC',
    'shipping_cost' => 10.50,
    'payment_method' => 'pix',
    'endereco' => json_encode(['endereco' => 'Rua Teste', 'numero' => '123']),
    'frete' => json_encode(['opcao' => 'PAC', 'preco' => 10.50]),
    'items' => json_encode([['id' => 1, 'title' => 'Produto Teste', 'qty' => 1]]),
    'subtotal' => 100.00,
    'shipping' => 10.50, // Coluna 'shipping' também existe na tabela
    'total' => 110.50,
    'status' => 'pending'
];

echo "Dados de teste:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Tentando criar pedido...\n";

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar colunas NOT NULL que precisam de valores
echo "Verificando colunas NOT NULL...\n";
$stmt = $pdo->query("SHOW COLUMNS FROM orders WHERE `Null` = 'NO' AND `Default` IS NULL");
$requiredColumns = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $requiredColumns[] = $row['Field'];
}
echo "Colunas obrigatórias (NOT NULL sem DEFAULT):\n";
foreach ($requiredColumns as $col) {
    $value = $testData[$col] ?? 'NÃO DEFINIDO';
    echo "  - {$col}: " . (isset($testData[$col]) ? var_export($value, true) : 'FALTANDO') . "\n";
}
echo "\n";

try {
    echo "Chamando Order::create()...\n";
    $orderId = $orderModel->create($testData);
    
    echo "Resultado: " . var_export($orderId, true) . "\n\n";
    
    if ($orderId) {
        echo "✓ Pedido criado com sucesso! ID: {$orderId}\n";
        
        // Verificar se o pedido foi criado
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            echo "\nPedido encontrado no banco:\n";
            echo "  ID: {$order['id']}\n";
            echo "  User ID: {$order['user_id']}\n";
            echo "  Status: {$order['status']}\n";
            echo "  Total: {$order['total']}\n";
            echo "  Created At: {$order['created_at']}\n";
            
            // Limpar pedido de teste
            $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
            echo "\n✓ Pedido de teste removido.\n";
        } else {
            echo "\n✗ Pedido não encontrado no banco após criação.\n";
        }
    } else {
        echo "✗ Falha ao criar pedido (retornou false/null/0).\n";
        echo "\nVerificando último erro do PDO:\n";
        $errorInfo = $pdo->errorInfo();
        if ($errorInfo[0] !== '00000') {
            echo "  Código: {$errorInfo[0]}\n";
            echo "  Mensagem: {$errorInfo[2]}\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Exceção ao criar pedido:\n";
    echo "  Mensagem: " . $e->getMessage() . "\n";
    echo "  Arquivo: " . $e->getFile() . "\n";
    echo "  Linha: " . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n";
}

