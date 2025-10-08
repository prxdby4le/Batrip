<?php
/**
 * Funções de carrinho de compras
 * 
 * Gerencia todas as operações do carrinho usando sessões PHP.
 * Sessão deve ser iniciada antes de incluir este arquivo.
 */

// Carregar configurações
require_once __DIR__ . '/config.php';

/**
 * Obtém o carrinho atual da sessão
 * @return array Array de itens do carrinho
 */
function get_cart() {
    return isset($_SESSION[CART_SESSION_KEY]) ? $_SESSION[CART_SESSION_KEY] : array();
}

/**
 * Define o carrinho na sessão
 * @param array $cart Array de itens do carrinho
 */
function set_cart($cart) {
    $_SESSION[CART_SESSION_KEY] = $cart;
}

/**
 * Adiciona um item ao carrinho
 * Se item já existir (mesmo ID e tamanho), incrementa quantidade.
 * Respeita limite máximo de quantidade.
 * 
 * @param array $product Array com dados do produto (id, title, size, price, qty)
 * @return bool True se adicionado com sucesso, false se exceder limite
 */
function add_to_cart($product) {
    $cart = get_cart();
    $idx = -1;
    $productId = isset($product['id']) ? (int)$product['id'] : 0;
    $size = isset($product['size']) ? trim($product['size']) : 'M';
    
    // Buscar se já existe item com mesmo ID e tamanho
    foreach ($cart as $i => $item) {
        $itemId = isset($item['id']) ? (int)$item['id'] : 0;
        $itemSize = isset($item['size']) ? trim($item['size']) : '';
        
        if ($productId > 0 && $itemId === $productId && $itemSize === $size) {
            $idx = $i;
            break;
        }
    }
    
    $addQty = isset($product['qty']) ? max(MIN_CART_QTY, (int)$product['qty']) : MIN_CART_QTY;
    
    if ($idx !== -1) {
        // Item já existe - incrementar quantidade respeitando máximo
        $newQty = $cart[$idx]['qty'] + $addQty;
        if ($newQty > MAX_CART_QTY) {
            $cart[$idx]['qty'] = MAX_CART_QTY; // Limitar ao máximo
            set_cart($cart);
            return false; // Indica que atingiu limite
        }
        $cart[$idx]['qty'] = $newQty;
    } else {
        // Novo item - validar quantidade
        if ($addQty > MAX_CART_QTY) {
            $addQty = MAX_CART_QTY;
        }
        $product['qty'] = $addQty;
        $cart[] = $product;
    }
    
    set_cart($cart);
    return true;
}

function remove_from_cart($productId, $size) {
    $cart = get_cart();
    $productId = (int)$productId;
    $size = trim($size);
    
    $cart = array_filter($cart, function($item) use ($productId, $size) {
        $itemId = isset($item['id']) ? (int)$item['id'] : 0;
        $itemSize = isset($item['size']) ? trim($item['size']) : '';
        return !($itemId === $productId && $itemSize === $size);
    });
    set_cart(array_values($cart));
}

function update_cart_item_qty($productId, $size, $qty) {
    $cart = get_cart();
    $productId = (int)$productId;
    $size = trim($size);
    $qty = max(0, (int)$qty);
    
    foreach ($cart as &$item) {
        $itemId = isset($item['id']) ? (int)$item['id'] : 0;
        $itemSize = isset($item['size']) ? trim($item['size']) : '';
        
        if ($itemId === $productId && $itemSize === $size) {
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

/**
 * Obtém o CEP do usuário armazenado na sessão
 * @return string CEP formatado ou padrão
 */
function get_user_cep() {
    return isset($_SESSION[CEP_SESSION_KEY]) ? $_SESSION[CEP_SESSION_KEY] : DEFAULT_CEP;
}

/**
 * Define o CEP do usuário na sessão
 * @param string $cep CEP a armazenar (será sanitizado)
 */
function set_user_cep($cep) {
    $_SESSION[CEP_SESSION_KEY] = preg_replace('/\D/', '', $cep);
}

/**
 * Valida se o tamanho do produto é válido
 * @param string $size Tamanho a validar (P, M, G, GG, etc)
 * @return bool True se válido, false caso contrário
 */
function validate_product_size($size) {
    return in_array(strtoupper(trim($size)), VALID_PRODUCT_SIZES);
}

/**
 * Valida se a quantidade é válida
 * @param int $qty Quantidade a validar
 * @param int $min Quantidade mínima (padrão: constante MIN_CART_QTY)
 * @param int $max Quantidade máxima (padrão: constante MAX_CART_QTY)
 * @return bool True se válido, false caso contrário
 */
function validate_product_qty($qty, $min = MIN_CART_QTY, $max = MAX_CART_QTY) {
    $qty = (int)$qty;
    return $qty >= $min && $qty <= $max;
}
