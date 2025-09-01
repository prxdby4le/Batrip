<?php
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart-functions.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(400);
    echo 'CSRF token inválido.';
    exit;
}

$action = $_POST['action'] ?? '';
switch ($action) {
    case 'remove':
        $title = $_POST['remove_title'] ?? '';
        $size  = $_POST['remove_size'] ?? '';
        if ($title !== '' && $size !== '') {
            remove_from_cart($title, $size);
        }
        break;
    case 'add':
        // Optional future: add item from product pages
        // Expected fields: title, size, price, img
        $title = trim($_POST['title'] ?? '');
        $size  = trim($_POST['size'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $img   = trim($_POST['img'] ?? '');
        // Normalize image path to start at assets/img/... when possible
        if ($img !== '') {
            if (preg_match('#assets/img/#', $img)) {
                $img = preg_replace('#^.*?(assets/img/)#', '$1', $img);
            } else {
                // remove leading ./ or ../ and leading slashes
                $img = ltrim(preg_replace('#^(\.\./|\./)+#', '', $img), '/');
            }
        }
    $qty   = max(1, (int)($_POST['qty'] ?? 1));
        if ($title && $size && $price > 0 && $img) {
            add_to_cart([
                'title' => $title,
                'size'  => $size,
                'price' => $price,
        'img'   => $img,
        'qty'   => $qty,
            ]);
        }
    // If request indicates to open cart after add, set a flag we will pass back on redirect
    $openCart = isset($_POST['open_cart']) && $_POST['open_cart'] == '1';
    $redirectMode = $_POST['redirect'] ?? '';
        break;
    case 'updateQty':
        $title = $_POST['title'] ?? '';
        $size  = $_POST['size'] ?? '';
        $qty   = max(1, (int)($_POST['qty'] ?? 1));
        if ($title && $size) {
            update_cart_item_qty($title, $size, $qty);
        }
        break;
    case 'updateSize':
        $title   = $_POST['title'] ?? '';
        $oldSize = $_POST['old_size'] ?? '';
        $newSize = $_POST['new_size'] ?? '';
        if ($title && $oldSize && $newSize) {
            update_cart_item_size($title, $oldSize, $newSize);
        }
        break;
    default:
        // no-op
}

// Redirect back to referer or home
$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// Decide redirect behavior
if (isset($redirectMode) && $redirectMode === 'cart') {
    // Send to cart page
    header('Location: checkout/carrinho.php');
    exit;
}

// Otherwise, remain on page and optionally open offcanvas
if (!empty($openCart)) {
    if (strpos($back, '#') === false) {
        $back .= (strpos($back, '?') === false ? '?' : '&') . 'openCart=1#openCart';
    } else {
        $back .= (strpos($back, '?') === false ? '?' : '&') . 'openCart=1';
    }
}
header('Location: ' . $back);
exit;
