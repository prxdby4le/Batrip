<?php
// Handler para operações do carrinho via AJAX
require_once '../includes/auth.php';
require_once '../includes/cart-functions.php';

header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Decodificar JSON do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$action = $input['action'];

try {
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
            
            // Validações básicas
            if (empty($product['title']) || $product['price'] <= 0) {
                throw new Exception('Dados do produto inválidos');
            }
            
            // Se tiver ID, buscar dados reais do produto no banco
            if ($product['id'] > 0) {
                try {
                    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
                    $stmt->execute([$product['id']]);
                    $dbProduct = $stmt->fetch();
                    
                    if ($dbProduct) {
                        $product['title'] = $dbProduct['title'];
                        $product['price'] = (float)$dbProduct['price'];
                        $product['image'] = $dbProduct['image'];
                    }
                } catch (PDOException $e) {
                    error_log("Erro ao buscar produto: " . $e->getMessage());
                }
            }
            
            add_to_cart($product);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
            
        case 'remove':
            // Remover produto do carrinho
            $title = trim($input['title'] ?? '');
            $size = trim($input['size'] ?? '');
            
            if (empty($title) || empty($size)) {
                throw new Exception('Título e tamanho são obrigatórios');
            }
            
            remove_from_cart($title, $size);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto removido do carrinho',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => get_cart_subtotal()
            ]);
            break;
            
        case 'update_qty':
            // Atualizar quantidade
            $title = trim($input['title'] ?? '');
            $size = trim($input['size'] ?? '');
            $qty = max(0, (int)($input['qty'] ?? 1));
            
            if (empty($title) || empty($size)) {
                throw new Exception('Título e tamanho são obrigatórios');
            }
            
            if ($qty === 0) {
                remove_from_cart($title, $size);
            } else {
                update_cart_item_qty($title, $size, $qty);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $qty === 0 ? 'Produto removido' : 'Quantidade atualizada',
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
    error_log("Erro no cart-handler: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
