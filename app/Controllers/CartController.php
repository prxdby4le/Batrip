<?php
/**
 * Cart Controller
 * 
 * Gerencia carrinho de compras
 * 
 * @category Controllers
 * @package  Batrip
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Set;
use App\Helpers\CartHelper;

class CartController extends Controller
{
    /**
     * Product model
     *
     * @var Product
     */
    private Product $productModel;

    /**
     * Cart helper
     *
     * @var CartHelper
     */
    private CartHelper $cartHelper;

    /**
     * Construtor
     */
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
        $this->productModel = new Product();
        $this->cartHelper = new CartHelper();
    }

    /**
     * Exibe carrinho
     *
     * @return void
     */
    public function index(): void
    {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $cartItems = $this->cartHelper->getItems();
        $total = $this->cartHelper->getTotal();
        $count = $this->cartHelper->getCount();
        
        $data = [
            'pageTitle' => 'Carrinho | Batrip',
            'cartItems' => $cartItems,
            'cart' => $cartItems, // Alias para compatibilidade
            'total' => $total,
            'count' => $count
        ];
        
        $this->view('cart.index', $data);
    }

    /**
     * Adiciona item ao carrinho (AJAX)
     *
     * @return void
     */
    public function add(): void
    {
        // Bloqueia usuários não logados
        if (!isset($_SESSION['user_id'])) {
            // Se for requisição AJAX/JSON, responde com erro 401
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $isJson = strpos($accept, 'application/json') !== false
                || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

            if ($isJson) {
                $this->json([
                    'success' => false,
                    'message' => 'Você precisa estar logado para adicionar itens ao carrinho.',
                    'requires_login' => true,
                    'login_url' => BASE_URL . 'login',
                ], 401);
                return;
            }

            // Fluxo via formulário/GET normal: guarda redirect e envia para login
            $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'produtos');
            $_SESSION['error'] = 'Você precisa estar logado para adicionar produtos ao carrinho.';
            $this->redirect(BASE_URL . 'login');
            return;
        }

        // Verifica método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

            // Decodifica JSON (se houver)
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
        $isFormSubmission = !is_array($input); // Se não for JSON válido, tratamos como formulário
            // Caso não seja JSON, tenta usar POST convencional (form-data)
        if ($isFormSubmission) {
                $input = [
                    'id' => isset($_POST['id']) ? (int)$_POST['id'] : (int)($_POST['product_id'] ?? 0),
                    'size' => $_POST['size'] ?? 'M',
                    'qty' => isset($_POST['qty']) ? (int)$_POST['qty'] : (int)($_POST['quantity'] ?? 1),
                ];
            }

        // Valida CSRF
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        if (!$this->validateCsrf($token)) {
            if ($isFormSubmission) {
                $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
                $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'produtos');
                return;
            }
            $this->json(['success' => false, 'message' => 'CSRF token inválido'], 403);
            return;
        }

        // Valida dados
        if (!$input || !isset($input['id'])) {
            if ($isFormSubmission) {
                $_SESSION['error'] = 'Dados do produto inválidos.';
                $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'produtos');
                return;
            }
            $this->json(['success' => false, 'message' => 'Dados inválidos'], 400);
            return;
        }

                $productId = (int)($input['id'] ?? 0);
                $size = trim((string)($input['size'] ?? 'M'));
                if (!\App\Helpers\CartHelper::validateSize($size)) {
                    $size = 'M'; // Normaliza para um tamanho padrão
                }
            $qty = max(1, (int)($input['qty'] ?? 1));

        // Busca produto no banco
        $product = $this->productModel->getActiveById($productId);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Produto não encontrado'], 404);
            return;
        }

        // Prepara dados do produto
        $cartProduct = [
            'id' => $product['id'],
            'title' => $product['title'],
            'price' => (float)$product['price'],
            'size' => $size,
            'qty' => $qty,
            'image' => $product['image'] ?? ''
        ];

        // Adiciona ao carrinho
        $success = $this->cartHelper->add($cartProduct);
        
        if ($success) {
            if ($isFormSubmission) {
                $_SESSION['success'] = 'Produto adicionado ao carrinho!';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho!',
                'cart_count' => $this->cartHelper->getCount()
            ]);
        } else {
            if ($isFormSubmission) {
                $_SESSION['error'] = 'Erro ao adicionar produto ao carrinho.';
                $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'produtos');
                return;
            }
            $this->json(['success' => false, 'message' => 'Erro ao adicionar produto'], 500);
        }
    }

    /**
     * Atualiza quantidade no carrinho (AJAX)
     *
     * @return void
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        $isForm = !is_array($input);
        if ($isForm) {
            $input = [
                'index' => $_POST['index'] ?? ($_POST['item_index'] ?? -1),
                'qty' => $_POST['qty'] ?? ($_POST['quantity'] ?? 1),
                'csrf_token' => $_POST['csrf_token'] ?? ''
            ];
        }
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        if (!$this->validateCsrf($token)) {
            if ($isForm) {
                $_SESSION['error'] = 'CSRF inválido ao atualizar quantidade.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json(['success' => false, 'message' => 'CSRF token inválido'], 403);
            return;
        }
        $index = (int)$input['index'];
        $qty = (int)$input['qty'];
        if ($index < 0 || $qty < 1) {
            if ($isForm) {
                $_SESSION['error'] = 'Dados inválidos ao atualizar quantidade.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json(['success' => false, 'message' => 'Dados inválidos'], 400);
            return;
        }
        $success = $this->cartHelper->updateQuantity($index, $qty);
        if ($success) {
            if ($isForm) {
                $_SESSION['success'] = 'Quantidade atualizada!';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json([
                'success' => true,
                'message' => 'Quantidade atualizada!',
                'cart_count' => $this->cartHelper->getCount(),
                'cart_total' => $this->cartHelper->getTotal()
            ]);
        } else {
            if ($isForm) {
                $_SESSION['error'] = 'Erro ao atualizar quantidade.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json(['success' => false, 'message' => 'Erro ao atualizar'], 500);
        }
    }

    /**
     * Remove item do carrinho (AJAX)
     *
     * @return void
     */
    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        $isForm = !is_array($input);
        if ($isForm) {
            $input = [
                'index' => $_POST['index'] ?? ($_POST['item_index'] ?? -1),
                'csrf_token' => $_POST['csrf_token'] ?? ''
            ];
        }
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        if (!$this->validateCsrf($token)) {
            if ($isForm) {
                $_SESSION['error'] = 'CSRF inválido ao remover item.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json(['success' => false, 'message' => 'CSRF token inválido'], 403);
            return;
        }
        $index = (int)$input['index'];
        if ($index < 0) {
            if ($isForm) {
                $_SESSION['error'] = 'Índice inválido para remoção.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json(['success' => false, 'message' => 'Dados inválidos'], 400);
            return;
        }
        $success = $this->cartHelper->remove($index);
        if ($success) {
            if ($isForm) {
                $_SESSION['success'] = 'Produto removido.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json([
                'success' => true,
                'message' => 'Produto removido!',
                'cart_count' => $this->cartHelper->getCount(),
                'cart_total' => $this->cartHelper->getTotal()
            ]);
        } else {
            if ($isForm) {
                $_SESSION['error'] = 'Erro ao remover produto.';
                $this->redirect(BASE_URL . 'cart');
                return;
            }
            $this->json(['success' => false, 'message' => 'Erro ao remover'], 500);
        }
    }

    /**
     * Limpa carrinho
     *
     * @return void
     */
    public function clear(): void
    {
        // Aceita POST ou GET para fallback, mas recomenda-se POST.
        $this->cartHelper->clear();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // JSON? tenta detectar Accept; se for fetch JSON devolve json
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $isJson = strpos($accept, 'application/json') !== false;
            if ($isJson) {
                $this->json(['success' => true, 'message' => 'Carrinho limpo', 'cart_count' => 0, 'cart_total' => 0]);
                return;
            }
            $_SESSION['success'] = 'Carrinho limpo.';
            $this->redirect(BASE_URL . 'cart');
            return;
        }
        $_SESSION['success'] = 'Carrinho limpo.';
        $this->redirect(BASE_URL . 'cart');
    }
    
    /**
     * Adiciona conjunto ao carrinho (AJAX)
     *
     * @return void
     */
    public function addSet(): void
    {
        try {
            // Limpar qualquer output buffer antes de processar
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Bloqueia usuários não logados
            if (!isset($_SESSION['user_id'])) {
                $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
                $isJson = strpos($accept, 'application/json') !== false
                    || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

                if ($isJson) {
                    $this->json([
                        'success' => false,
                        'message' => 'Você precisa estar logado para adicionar itens ao carrinho.',
                        'requires_login' => true,
                        'login_url' => BASE_URL . 'login',
                    ], 401);
                    return;
                }

                $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'conjuntos');
                $_SESSION['error'] = 'Você precisa estar logado para adicionar conjuntos ao carrinho.';
                $this->redirect(BASE_URL . 'login');
                return;
            }

            // Verifica método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
                return;
            }

            // Decodifica JSON (se houver)
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            $isFormSubmission = !is_array($input);
            
            if ($isFormSubmission) {
                $input = [
                    'id' => isset($_POST['id']) ? (int)$_POST['id'] : (int)($_POST['set_id'] ?? 0),
                    'qty' => isset($_POST['qty']) ? (int)$_POST['qty'] : 1,
                ];
            }

            // Valida CSRF
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
            if (!$this->validateCsrf($token)) {
                if ($isFormSubmission) {
                    $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'conjuntos');
                    return;
                }
                $this->json(['success' => false, 'message' => 'CSRF token inválido'], 403);
                return;
            }

            // Valida dados
            if (!$input || !isset($input['id'])) {
                if ($isFormSubmission) {
                    $_SESSION['error'] = 'Dados do conjunto inválidos.';
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'conjuntos');
                    return;
                }
                $this->json(['success' => false, 'message' => 'Dados inválidos'], 400);
                return;
            }

            $setId = (int)($input['id'] ?? 0);
            $qty = max(1, (int)($input['qty'] ?? 1));

            if ($setId <= 0) {
                $this->json(['success' => false, 'message' => 'ID do conjunto inválido'], 400);
                return;
            }

            // Busca conjunto no banco
            $setModel = new Set();
            $set = $setModel->getActiveById($setId);
            
            if (!$set) {
                if ($isFormSubmission) {
                    $_SESSION['error'] = 'Conjunto não encontrado ou inativo.';
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'conjuntos');
                    return;
                }
                $this->json(['success' => false, 'message' => 'Conjunto não encontrado'], 404);
                return;
            }

            // Prepara dados do conjunto
            $cartSet = [
                'id' => $set['id'],
                'title' => $set['title'],
                'price' => (float)$set['price'],
                'image' => $set['image'] ?? ''
            ];

            // Adiciona ao carrinho
            $success = $this->cartHelper->addSet($cartSet, $qty);
            
            if ($success) {
                if ($isFormSubmission) {
                    $_SESSION['success'] = 'Conjunto adicionado ao carrinho!';
                    $this->redirect(BASE_URL . 'cart');
                    return;
                }
                $this->json([
                    'success' => true,
                    'message' => 'Conjunto adicionado ao carrinho!',
                    'cart_count' => $this->cartHelper->getCount()
                ]);
            } else {
                if ($isFormSubmission) {
                    $_SESSION['error'] = 'Erro ao adicionar conjunto ao carrinho.';
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'conjuntos');
                    return;
                }
                $this->json(['success' => false, 'message' => 'Erro ao adicionar conjunto'], 500);
            }
        } catch (\Throwable $e) {
            // Log do erro
            error_log('CartController::addSet - Erro: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Sempre retorna JSON mesmo em caso de erro
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            $isDebug = defined('DEBUG') && DEBUG;
            $this->json([
                'success' => false,
                'message' => 'Erro ao processar requisição: ' . ($isDebug ? $e->getMessage() : 'Erro interno do servidor')
            ], 500);
        }
    }

    /**
     * Retorna HTML do cart sidebar (para AJAX)
     *
     * @return void
     */
    public function sidebar(): void
    {
        // Limpar qualquer output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Define variáveis globais necessárias para o cart-sidebar.php
        if (!defined('BASE_URL')) {
            require_once ROOT_PATH . '/config/config.php';
        }
        
        // Define $base para o cart-sidebar.php (relativo ou absoluto)
        $base = BASE_URL;
        $GLOBALS['baseHref'] = $base;
        
        // Carregar autoloader para CartHelper
        require_once ROOT_PATH . '/vendor/autoload.php';
        
        // Incluir helpers necessários
        require_once ROOT_PATH . '/includes/cart-functions.php';
        require_once ROOT_PATH . '/includes/icon-helper.php';
        
        // Incluir o arquivo de sidebar (usando o legado para manter compatibilidade)
        require_once ROOT_PATH . '/includes/cart-sidebar.php';
        exit;
    }

    /**
     * Handler de carrinho (compatibilidade com sistema antigo)
     *
     * @return void
     */
    public function handler(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        // Decodifica JSON
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        
        if (!is_array($input)) {
            $input = $_POST;
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $this->add();
                break;
            case 'add_set':
            case 'addSet':
                $this->addSet();
                break;
            case 'update':
                $this->update();
                break;
            case 'remove':
                $this->remove();
                break;
            case 'clear':
                $this->clear();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Ação inválida'], 400);
        }
    }
}
