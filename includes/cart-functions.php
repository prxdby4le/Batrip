<?php
// Funções de carrinho em PHP usando sessão
if (session_status() === PHP_SESSION_NONE) session_start();

function get_cart() {
    return isset($_SESSION['batrip_cart']) ? $_SESSION['batrip_cart'] : array();
}

function set_cart($cart) {
    $_SESSION['batrip_cart'] = $cart;
}

function add_to_cart($product) {
    $cart = get_cart();
    $idx = -1;
    foreach ($cart as $i => $item) {
        if ($item['title'] === $product['title'] && $item['size'] === $product['size']) {
            $idx = $i;
            break;
        }
    }
    if ($idx !== -1) {
        $cart[$idx]['qty'] += 1;
    } else {
        $product['qty'] = 1;
        $cart[] = $product;
    }
    set_cart($cart);
}

function remove_from_cart($title, $size) {
    $cart = get_cart();
    $cart = array_filter($cart, function($item) use ($title, $size) {
        return !($item['title'] === $title && $item['size'] === $size);
    });
    set_cart(array_values($cart));
}

function update_cart_item_qty($title, $size, $qty) {
    $cart = get_cart();
    foreach ($cart as &$item) {
        if ($item['title'] === $title && $item['size'] === $size) {
            $item['qty'] = $qty;
            break;
        }
    }
    set_cart($cart);
}

function update_cart_item_size($title, $oldSize, $newSize) {
    $cart = get_cart();
    $idx = -1;
    foreach ($cart as $i => $item) {
        if ($item['title'] === $title && $item['size'] === $oldSize) {
            $idx = $i;
            break;
        }
    }
    if ($idx !== -1) {
        // Se já existe um item com o novo tamanho, soma as quantidades
        foreach ($cart as $j => $item) {
            if ($item['title'] === $title && $item['size'] === $newSize && $j !== $idx) {
                $cart[$j]['qty'] += $cart[$idx]['qty'];
                array_splice($cart, $idx, 1);
                set_cart($cart);
                return;
            }
        }
        $cart[$idx]['size'] = $newSize;
        set_cart($cart);
    }
}

function get_cart_count() {
    $cart = get_cart();
    $count = 0;
    foreach ($cart as $item) $count += $item['qty'];
    return $count;
}

function get_cart_subtotal() {
    $cart = get_cart();
    $subtotal = 0;
    foreach ($cart as $item) $subtotal += $item['qty'] * $item['price'];
    return $subtotal;
}

function calcular_frete($cep) {
    if (preg_match('/^0[1-9]/', $cep)) return 0;
    if (preg_match('/^1[0-9]/', $cep) || preg_match('/^2[0-9]/', $cep)) return 20;
    if (preg_match('/^3[0-9]/', $cep) || preg_match('/^4[0-9]/', $cep)) return 30;
    if (preg_match('/^5[0-9]/', $cep) || preg_match('/^6[0-9]/', $cep) || preg_match('/^7[0-9]/', $cep)) return 40;
    return 50;
}

function get_user_cep() {
    return isset($_SESSION['batrip_cep']) ? $_SESSION['batrip_cep'] : '00000-000';
}

function set_user_cep($cep) {
    $_SESSION['batrip_cep'] = preg_replace('/\D/', '', $cep);
}
