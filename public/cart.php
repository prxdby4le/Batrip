<?php
/**
 * DEPRECATED: Este arquivo é mantido apenas para compatibilidade com formulários legados.
 * Use cart-handler.php para novas implementações (suporta AJAX e JSON).
 * 
 * Este handler redireciona requisições POST tradicionais para o cart-handler.php
 * convertendo os dados para o formato esperado.
 * 
 * @deprecated Use cart-handler.php
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart-functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(403);
    die('CSRF token inválido.');
}

// Compatibilidade: processar ação e redirecionar
$action = $_POST['action'] ?? '';
$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';

try {
    switch ($action) {
        case 'remove':
            $productId = isset($_POST['remove_id']) ? (int)$_POST['remove_id'] : 0;
            $size  = $_POST['remove_size'] ?? '';
            if ($productId > 0 && $size !== '') {
                remove_from_cart($productId, $size);
            }
            break;
            
        case 'add':
            // Add item from product pages
            $productId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = trim($_POST['title'] ?? '');
            $size  = trim($_POST['size'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $qty   = max(1, (int)($_POST['qty'] ?? 1));
            
            // Validações
            if (!validate_product_size($size)) {
                throw new Exception('Tamanho inválido');
            }
            if (!validate_product_qty($qty)) {
                throw new Exception('Quantidade inválida (1-10)');
            }
            
            if ($productId > 0 && $size && $price > 0) {
                add_to_cart([
                    'id'    => $productId,
                    'title' => $title,
                    'size'  => $size,
                    'price' => $price,
                    'qty'   => $qty,
                ]);
            }
            
            // Check redirect mode
            $redirectMode = $_POST['redirect'] ?? '';
            if ($redirectMode === 'cart') {
                header('Location: checkout/carrinho.php');
                exit;
            }
            
            // Open cart sidebar
            $openCart = isset($_POST['open_cart']) && $_POST['open_cart'] == '1';
            if ($openCart) {
                if (strpos($back, '#') === false) {
                    $back .= (strpos($back, '?') === false ? '?' : '&') . 'openCart=1#openCart';
                } else {
                    $back .= (strpos($back, '?') === false ? '?' : '&') . 'openCart=1';
                }
            }
            break;
            
        case 'updateQty':
            $productId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $size  = $_POST['size'] ?? '';
            $qty   = max(1, (int)($_POST['qty'] ?? 1));
            
            if (!validate_product_qty($qty)) {
                throw new Exception('Quantidade inválida (1-10)');
            }
            
            if ($productId > 0 && $size) {
                update_cart_item_qty($productId, $size, $qty);
            }
            break;
            
        default:
            // no-op
    }
    log_cart_action($action, ['success' => true, 'legacy_mode' => true]);
} catch (Exception $e) {
    log_error("Erro em cart.php (DEPRECATED)", [
        'action' => $action,
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => $e->getLine()
    ]);
    // Continua para redirecionar mesmo com erro
}

// Redirect back
header('Location: ' . $back);
exit;
