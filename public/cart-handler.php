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
        case 'add':
            // Adicionar produto ao carrinho
            $product = [
                'id' => (int)($input['id'] ?? 0),
                'title' => trim($input['title'] ?? ''),
                'price' => (float)($input['price'] ?? 0),
                'size' => trim($input['size'] ?? 'M'),
                'qty' => max(1, (int)($input['qty'] ?? 1)),
                'image' => trim($input['image'] ?? '')
            ];
            
            log_debug("Produto preparado", ['product' => $product], 'cart');
            
            // Se tiver ID, buscar dados reais do produto no banco PRIMEIRO
            if ($product['id'] > 0) {
                try {
                    require_once __DIR__ . '/../includes/db.php';
                    
                    if (!isset($pdo)) {
                        throw new Exception('Conexão com banco de dados não disponível');
                    }
                    
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
            
            log_cart_action('add', $product['id'], [
                'title' => $product['title'],
                'size' => $product['size'],
                'qty' => $product['qty'],
                'price' => $product['price'],
                'cart_count' => $cartCount,
                'limit_reached' => !$success
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
            
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
