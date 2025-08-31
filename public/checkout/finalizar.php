<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrinho.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(400);
    echo 'CSRF token inválido.';
    exit;
}

$cart = get_cart();
if (empty($cart)) { header('Location: ../index.php'); exit; }
$addr = $_SESSION['checkout_address'] ?? null;
if (!$addr) { header('Location: endereco.php'); exit; }

$subtotal = get_cart_subtotal();
$cep = preg_replace('/\D/','', $addr['cep'] ?? '');
$shipping = (float)calcular_frete($cep);
$total = $subtotal + $shipping;

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, subtotal, shipping, total, address) VALUES (?, ?, ?, ?, ?)');
    $addressJson = json_encode($addr, JSON_UNESCAPED_UNICODE);
    $stmt->execute([$_SESSION['user_id'], $subtotal, $shipping, $total, $addressJson]);
    $orderId = (int)$pdo->lastInsertId();

    $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, title, size, price, qty, image) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($cart as $it) {
        $stmtItem->execute([$orderId, $it['title'], $it['size'], $it['price'], $it['qty'], $it['img'] ?? null]);
    }
    $pdo->commit();

    // Limpa carrinho e etapas de checkout
    set_cart([]);
    unset($_SESSION['checkout_address'], $_SESSION['checkout_frete']);

    // Redireciona para uma página de sucesso simples
    header('Location: sucesso.php?id=' . $orderId);
    exit;
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Falha ao finalizar pedido: ' . $e->getMessage());
    http_response_code(500);
    echo 'Não foi possível finalizar o pedido.';
    exit;
}
