<?php
require_once __DIR__ . '/../includes/auth.php';
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
        if ($title && $size && $price > 0 && $img) {
            add_to_cart([
                'title' => $title,
                'size'  => $size,
                'price' => $price,
                'img'   => $img,
            ]);
        }
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
header('Location: ' . $back);
exit;
