<?php
// Handler para operações do carrinho via AJAX
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart-functions.php';
require_once __DIR__ . '/../includes/logger.php';

header('Content-Type: application/json');

$startTime = microtime(true);
log_debug("cart-handler.php iniciado", ['session_id' => session_id()], 'cart');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Decodificar JSON do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

// VALIDAÇÃO CSRF - CRÍTICO
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token inválido']);
    exit;
}

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$action = $input['action'];

try {
    log_info("Ação de carrinho recebida", ['action' => $action, 'input' => $input], 'cart');
    
    switch ($action) {
        case 'add_set':
            // Adiciona todos os itens de um conjunto ao carrinho com tamanhos escolhidos
            require_once __DIR__ . '/../includes/db.php';
            $setId = (int)($input['set_id'] ?? 0);
            if ($setId <= 0) { throw new Exception('Conjunto inválido'); }
            $setQty = max(1, (int)($input['set_qty'] ?? 1));
            if ($setQty > 10) { $setQty = 10; }

            // Validar conjunto ativo
            $stmt = $pdo->prepare('SELECT id FROM sets WHERE id = ? AND active = 1');
            $stmt->execute([$setId]);
            if (!$stmt->fetch()) { throw new Exception('Conjunto não encontrado ou inativo'); }

            // Carregar composição do conjunto
            $stmt = $pdo->prepare('SELECT si.product_id, si.quantity, p.title, p.price, p.image, p.sizes, p.active
                                   FROM set_items si JOIN products p ON p.id = si.product_id
                                   WHERE si.set_id = ?');
            $stmt->execute([$setId]);
            $composition = $stmt->fetchAll();
            if (!$composition) { throw new Exception('Conjunto sem itens'); }

            // Mapa product_id -> dados
            $byId = [];
            foreach ($composition as $row) { $byId[(int)$row['product_id']] = $row; }

            // Itens enviados: [{product_id, size}] — qty vem da composição
            $items = $input['items'] ?? [];
            if (!is_array($items) || empty($items)) { throw new Exception('Selecione os tamanhos dos itens do conjunto'); }

            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                $size = strtoupper(trim((string)($it['size'] ?? '')));
                if ($pid <= 0 || $size === '' || !isset($byId[$pid])) {
                    throw new Exception('Item inválido do conjunto');
                }
                $row = $byId[$pid];
                if ((int)$row['active'] !== 1) { throw new Exception('Produto do conjunto inativo'); }
                // Validar tamanho conforme produto.sizes (ou default)
                $sizesStr = (string)($row['sizes'] ?? '');
                $allowed = array_filter(array_map('trim', explode(',', $sizesStr ?: 'P,M,G,GG')));
                $allowed = array_map('strtoupper', $allowed);
                if (!in_array($size, $allowed, true)) {
                    throw new Exception('Tamanho inválido para o produto ' . $row['title']);
                }
                $qty = max(1, (int)$row['quantity']) * $setQty;
                // Montar item do carrinho
                $product = [
                    'id' => (int)$pid,
                    'title' => (string)$row['title'],
                    'price' => (float)$row['price'],
                    'size' => $size,
                    'qty' => $qty,
                    'image' => (string)($row['image'] ?? ''),
                ];
                if (empty($product['title']) || $product['price'] <= 0) {
                    throw new Exception('Dados de item inválidos');
                }
                if (!validate_product_qty($product['qty'])) {
                    throw new Exception('Quantidade inválida no conjunto');
                }
                add_to_cart($product);
            }
            echo json_encode([
                'success' => true,
                'message' => 'Conjunto adicionado ao carrinho',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
        case 'add':
            // Adicionar item ao carrinho (produto padrão ou conjunto)
            $itemType = isset($input['item_type']) ? strtolower(trim((string)$input['item_type'])) : 'product';
            $qty = max(1, (int)($input['qty'] ?? 1));
            require_once __DIR__ . '/../includes/db.php';

            if ($itemType === 'set') {
                // Adicionar conjunto: validar no banco e montar item especial
                $setId = (int)($input['id'] ?? 0);
                if ($setId <= 0) { throw new Exception('Conjunto inválido'); }
                $stmt = $pdo->prepare('SELECT id, title, price, image FROM sets WHERE id = ? AND active = 1');
                $stmt->execute([$setId]);
                $row = $stmt->fetch();
                if (!$row) { throw new Exception('Conjunto não encontrado ou inativo'); }
                // Offset para não colidir com IDs de produto no carrinho
                $offset = 1000000; // 1 milhão
                $product = [
                    'id' => $offset + (int)$row['id'],
                    'title' => (string)$row['title'],
                    'price' => (float)$row['price'],
                    'size' => 'SET', // tamanho simbólico
                    'qty' => $qty,
                    'image' => (string)($row['image'] ?? ''),
                    'type' => 'set',
                    'set_id' => (int)$row['id'],
                ];
                if (empty($product['title']) || $product['price'] <= 0) {
                    throw new Exception('Dados do conjunto inválidos');
                }
                if (!validate_product_qty($product['qty'])) {
                    throw new Exception('Quantidade deve estar entre ' . MIN_CART_QTY . ' e ' . MAX_CART_QTY);
                }
                $success = add_to_cart($product);
                $cartCount = get_cart_count();
                log_cart_action('add_set', $product['id'], [ 'title' => $product['title'], 'qty' => $product['qty'], 'price' => $product['price'], 'cart_count' => $cartCount, 'limit_reached' => !$success ]);
                echo json_encode(['success' => true, 'message' => 'Conjunto adicionado ao carrinho', 'cart_count' => $cartCount, 'cart_subtotal' => get_cart_subtotal() ]);
                break;
            } else {
                // Fluxo padrão de produto
                $product = [
                    'id' => (int)($input['id'] ?? 0),
                    'title' => trim($input['title'] ?? ''),
                    'price' => (float)($input['price'] ?? 0),
                    'size' => trim($input['size'] ?? 'M'),
                    'qty' => $qty,
                    'image' => trim($input['image'] ?? '')
                ];
                log_debug("Produto preparado", ['product' => $product], 'cart');
                // Se tiver ID, buscar dados reais do produto no banco PRIMEIRO
                if ($product['id'] > 0) {
                    try {
                        if (!isset($pdo)) { throw new Exception('Conexão com banco de dados não disponível'); }
                        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
                        $stmt->execute([$product['id']]);
                        $dbProduct = $stmt->fetch();
                        if ($dbProduct) {
                            $product['title'] = $dbProduct['title'];
                            $product['price'] = (float)$dbProduct['price'];
                            $product['image'] = $dbProduct['image'];
                            log_debug("Produto atualizado do banco", ['title' => $product['title'], 'price' => $product['price']], 'cart');
                        } else {
                            log_warning("Produto não encontrado no banco", ['product_id' => $product['id']], 'cart');
                            throw new Exception('Produto não encontrado ou inativo');
                        }
                    } catch (PDOException $e) {
                        log_error("Erro ao buscar produto no banco", ['product_id' => $product['id'], 'error' => $e->getMessage()], 'cart');
                        throw new Exception('Erro ao validar produto no banco de dados');
                    }
                }
                // Validações básicas DEPOIS de buscar do banco
                if (empty($product['title']) || $product['price'] <= 0) {
                    log_warning("Validação de produto falhou", ['product' => $product], 'cart');
                    throw new Exception('Dados do produto inválidos');
                }
                // Validar tamanho
                if (!validate_product_size($product['size'])) {
                    log_warning("Tamanho inválido", ['size' => $product['size']], 'cart');
                    throw new Exception('Tamanho inválido. Tamanhos válidos: P, M, G, GG');
                }
                // Validar quantidade
                if (!validate_product_qty($product['qty'])) {
                    log_warning("Quantidade inválida", ['qty' => $product['qty']], 'cart');
                    throw new Exception('Quantidade deve estar entre ' . MIN_CART_QTY . ' e ' . MAX_CART_QTY);
                }
                $success = add_to_cart($product);
                $cartCount = get_cart_count();
                log_cart_action('add', $product['id'], [ 'title' => $product['title'], 'size' => $product['size'], 'qty' => $product['qty'], 'price' => $product['price'], 'cart_count' => $cartCount, 'limit_reached' => !$success ]);
                echo json_encode([ 'success' => true, 'message' => 'Produto adicionado ao carrinho', 'cart_count' => get_cart_count(), 'cart_subtotal' => get_cart_subtotal() ]);
                break;
            }
            
        case 'remove':
            // Remover produto do carrinho
            $productId = (int)($input['id'] ?? $input['product_id'] ?? 0);
            $size = trim($input['size'] ?? '');
            
            if ($productId <= 0 || empty($size)) {
                throw new Exception('ID do produto e tamanho são obrigatórios');
            }
            
            remove_from_cart($productId, $size);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto removido do carrinho',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
            
        case 'update':
        case 'update_qty':
            // Atualizar quantidade
            $productId = (int)($input['id'] ?? $input['product_id'] ?? 0);
            $size = trim($input['size'] ?? '');
            $qty = max(0, (int)($input['qty'] ?? $input['quantity'] ?? 1));
            
            if ($productId <= 0 || empty($size)) {
                throw new Exception('ID do produto e tamanho são obrigatórios');
            }
            
            if ($qty === 0) {
                remove_from_cart($productId, $size);
            } else {
                update_cart_item_qty($productId, $size, $qty);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $qty === 0 ? 'Produto removido' : 'Quantidade atualizada',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal(),
                'cart' => get_cart()
            ]);
            break;
            
        case 'update_size':
            // Atualizar tamanho do item no carrinho
            $productId = (int)($input['id'] ?? 0);
            $oldSize = trim($input['old_size'] ?? '');
            $newSize = trim($input['new_size'] ?? '');
            if ($productId <= 0 || empty($oldSize) || empty($newSize)) {
                throw new Exception('ID do produto, tamanho antigo e novo são obrigatórios');
            }
            // Atualizar pelo título, pois a função espera title
            $cart = get_cart();
            $title = '';
            foreach ($cart as $item) {
                if ((int)$item['id'] === $productId && $item['size'] === $oldSize) {
                    $title = $item['title'];
                    break;
                }
            }
            if (!$title) throw new Exception('Item não encontrado no carrinho');
            update_cart_item_size($title, $oldSize, $newSize);
            echo json_encode([
                'success' => true,
                'message' => 'Tamanho atualizado',
                'cart' => get_cart(),
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
        case 'clear':
            // Limpar carrinho
            set_cart([]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Carrinho limpo',
                'cart_count' => 0,
                'cart_subtotal' => 0
            ]);
            break;
            
        case 'get':
            // Retornar dados do carrinho
            echo json_encode([
                'success' => true,
                'cart' => get_cart(),
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    log_exception($e, ['action' => $action ?? 'unknown', 'input' => $input ?? []]);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Log de performance
    log_performance('cart-handler', $startTime, ['action' => $action ?? 'unknown']);
}
?>
