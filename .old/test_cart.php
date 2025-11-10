<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart-functions.php';
require_once __DIR__ . '/../includes/db.php';

echo "<h1>Teste do Carrinho</h1>";

echo "<h2>Session ID: " . session_id() . "</h2>";
echo "<h3>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "</h3>";

echo "<h2>Carrinho Atual:</h2>";
$cart = get_cart();
echo "<pre>";
print_r($cart);
echo "</pre>";

echo "<h2>Contador: " . get_cart_count() . "</h2>";
echo "<h2>Subtotal: R$ " . number_format(get_cart_subtotal(), 2, ',', '.') . "</h2>";

// Teste de adicionar produto
echo "<hr>";
echo "<h2>Teste: Adicionar Produto</h2>";

$testProduct = [
    'id' => 999,
    'title' => 'Produto Teste',
    'price' => 99.90,
    'size' => 'M',
    'qty' => 1
];

add_to_cart($testProduct);
echo "<p>Produto teste adicionado!</p>";

$cart = get_cart();
echo "<pre>";
print_r($cart);
echo "</pre>";

echo "<h2>Novo Contador: " . get_cart_count() . "</h2>";
echo "<h2>Novo Subtotal: R$ " . number_format(get_cart_subtotal(), 2, ',', '.') . "</h2>";

// Buscar produtos reais do banco
echo "<hr>";
echo "<h2>Produtos no Banco:</h2>";
try {
    $stmt = $pdo->prepare('SELECT id, title, price, active FROM products LIMIT 5');
    $stmt->execute();
    $products = $stmt->fetchAll();
    echo "<pre>";
    print_r($products);
    echo "</pre>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro ao buscar produtos: " . $e->getMessage() . "</p>";
}
