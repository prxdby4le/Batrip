<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Helpers\CartHelper;
use App\Helpers\Logger;

/**
 * CheckoutController - Processo de finalização de compra
 */
class CheckoutController extends Controller
{
    private Order $orderModel;
    
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
        $this->orderModel = new Order();
    }
    
    /**
     * Página de checkout
     */
    public function index(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/checkout';
            $_SESSION['error'] = 'Você precisa estar logado para finalizar a compra';
            $this->redirect('/login');
            return;
        }
        
        // Verifica se tem itens no carrinho
        $cart = CartHelper::getCart();
        
        if (empty($cart)) {
            $_SESSION['error'] = 'Seu carrinho está vazio';
            $this->redirect('/checkout/carrinho');
            return;
        }
        
        // Recupera frete selecionado (se houver)
        $shipping = $_SESSION['shipping'] ?? null;
        $shippingCost = $shipping['cost'] ?? 0.0;

        // Salva total com frete
        $orderSubtotal = CartHelper::getTotal();
        $orderTotal = $orderSubtotal + $shippingCost;

        // Prefill endereço caso tenha vindo da página de frete
        $shippingInput = $_SESSION['shipping_input'] ?? [];

        $data = [
            'pageTitle' => 'Finalizar Compra - Batrip',
            'cart' => $cart,
            'total' => $orderSubtotal,
            'shipping' => $shipping,
            'shippingCost' => $shippingCost,
            'grandTotal' => $orderTotal,
            'prefill' => $shippingInput,
            'layout' => 'main',
            // Passa dados do usuário logado para prefill adicional se necessário
            'user' => [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
            ],
        ];
        
        $this->view('checkout.index', $data);
    }
    
    /**
     * Processa o pedido
     */
    public function process(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/checkout';
            $_SESSION['error'] = 'Você precisa estar logado para finalizar a compra';
            $this->redirect('/login');
            return;
        }
        
        if (!$this->request->isPost()) {
            $this->redirect('/checkout');
            return;
        }
        
            // CSRF
            $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
            if (!$this->validateCsrf($token)) {
                $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
                $this->redirect('/checkout');
                return;
            }
        
        // Validação básica
        $name = $this->request->post('name');
        $email = $this->request->post('email');
        $phone = $this->request->post('phone');
        $address = $this->request->post('address');
        $city = $this->request->post('city');
        $state = $this->request->post('state');
        $zipcode = $this->request->post('zipcode');
        $paymentMethod = $this->request->post('payment_method');
        
        $errors = [];
        
        if (empty($name)) $errors[] = 'Nome é obrigatório';
        if (empty($email)) $errors[] = 'Email é obrigatório';
        if (empty($phone)) $errors[] = 'Telefone é obrigatório';
        if (empty($address)) $errors[] = 'Endereço é obrigatório';
        if (empty($city)) $errors[] = 'Cidade é obrigatória';
        if (empty($state)) $errors[] = 'Estado é obrigatório';
        if (empty($zipcode)) $errors[] = 'CEP é obrigatório';
        if (empty($paymentMethod)) $errors[] = 'Forma de pagamento é obrigatória';
        
        $cart = CartHelper::getCart();
        if (empty($cart)) {
            $errors[] = 'Carrinho está vazio';
        }

        // Inclui frete selecionado (opcional)
        $shipping = $_SESSION['shipping'] ?? null;
        $shippingCost = $shipping['cost'] ?? 0.0;
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/checkout');
            return;
        }
        
        // Cria pedido
        $userId = $_SESSION['user_id'] ?? null;
        $subtotal = CartHelper::getTotal();
        $shipping = $_SESSION['shipping'] ?? null;
        $shippingCost = $shipping['cost'] ?? 0.0;
        $orderTotal = $subtotal + $shippingCost;
        
        // Prepara dados do endereço como JSON (compatibilidade com estrutura antiga)
        $enderecoData = [
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zipcode' => $zipcode
        ];
        
        // Prepara dados do frete como JSON (compatibilidade com estrutura antiga)
        $freteData = [
            'method' => $shipping['method'] ?? null,
            'cost' => $shippingCost
        ];
        
        try {
            Logger::info('Iniciando criação de pedido', [
                'user_id' => $userId,
                'payment_method' => $paymentMethod,
                'total' => $orderTotal
            ]);
            
            $orderId = $this->orderModel->create([
                'user_id' => $userId,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'shipping_address' => $address,
                'shipping_city' => $city,
                'shipping_state' => $state,
                'shipping_zipcode' => $zipcode,
                'shipping_method' => $shipping['method'] ?? null,
                'shipping_cost' => $shippingCost,
                'payment_method' => $paymentMethod,
                'endereco' => json_encode($enderecoData),
                'frete' => json_encode($freteData),
                'items' => json_encode($cart),
                'subtotal' => $subtotal,
                'shipping' => $shippingCost,
                'total' => $orderTotal,
                'status' => 'pending'
            ]);
            
            if ($orderId) {
                Logger::info('Pedido criado com sucesso', [
                    'order_id' => $orderId,
                    'user_id' => $userId
                ]);
                
                // Limpa carrinho e frete
                CartHelper::clear();
                unset($_SESSION['shipping'], $_SESSION['shipping_quotes']);
                
                // Salva ID do pedido na sessão
                $_SESSION['last_order_id'] = $orderId;
                
                $this->redirect('/checkout/success');
            } else {
                throw new \Exception('Falha ao obter ID do pedido criado');
            }
        } catch (\Exception $e) {
            Logger::error('Erro ao criar pedido', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $_SESSION['error'] = 'Erro ao processar pedido: ' . $e->getMessage();
            $this->redirect('/checkout');
        }
    }
    
    /**
     * Página de sucesso
     */
    public function success(): void
    {
        // Tentar obter order_id da sessão ou do parâmetro GET
        $orderId = $_SESSION['last_order_id'] ?? $this->request->get('order') ?? null;
        
        error_log("CheckoutController::success - Order ID: " . ($orderId ?? 'NULL'));
        error_log("CheckoutController::success - Session last_order_id: " . ($_SESSION['last_order_id'] ?? 'NÃO DEFINIDO'));
        
        if (!$orderId) {
            Logger::error('Acesso à página de sucesso sem order_id', [
                'session' => $_SESSION,
                'get' => $_GET
            ]);
            $_SESSION['error'] = 'Pedido não encontrado na sessão';
            $this->redirect('/');
            return;
        }
        
        $order = $this->orderModel->getFullDetails($orderId);
        
        if (!$order) {
            Logger::error('Pedido não encontrado no banco', ['order_id' => $orderId]);
            $_SESSION['error'] = 'Pedido não encontrado';
            $this->redirect('/');
            return;
        }
        
        // Limpa da sessão após buscar o pedido
        unset($_SESSION['last_order_id']);
        
        $data = [
            'pageTitle' => 'Pedido Confirmado - Batrip',
            'order' => $order,
            'order_id' => $orderId,
            'layout' => 'main'
        ];
        
        $this->view('checkout.success', $data);
    }
    
    /**
     * Página do carrinho (checkout)
     */
    /**
     * Exibe carrinho de compras (primeiro passo do checkout)
     *
     * @return void
     */
    public function cart(): void
    {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $cartItems = CartHelper::getCart();
        $total = CartHelper::getTotal();
        $count = CartHelper::getItemCount();
        
        $data = [
            'pageTitle' => 'Carrinho | Batrip',
            'cartItems' => $cartItems,
            'cart' => $cartItems, // Alias para compatibilidade
            'total' => $total,
            'count' => $count
        ];
        
        $this->view('checkout.cart', $data);
    }
    
    /**
     * Formulário de endereço de entrega
     */
    public function address(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/checkout/endereco';
            $this->redirect('/login');
            return;
        }
        
        require_once ROOT_PATH . '/includes/cart-functions.php';
        $cart = get_cart();
        
        if (empty($cart)) {
            $_SESSION['error'] = 'Seu carrinho está vazio';
            $this->redirect('/checkout/carrinho');
            return;
        }
        
        $endereco = $_SESSION['checkout_endereco'] ?? [];
        
        $data = [
            'pageTitle' => 'Endereço de Entrega | Batrip',
            'endereco' => $endereco,
            'layout' => 'main'
        ];
        
        $this->view('checkout.address', $data);
    }
    
    /**
     * Salva endereço de entrega
     */
    public function saveAddress(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        if (!$this->request->isPost()) {
            $this->redirect('/checkout/endereco');
            return;
        }
        
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        if (!$this->validateCsrf($token)) {
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect('/checkout/endereco');
            return;
        }
        
        $_SESSION['checkout_endereco'] = [
            'cep' => trim($this->request->post('cep') ?? ''),
            'endereco' => trim($this->request->post('endereco') ?? ''),
            'numero' => trim($this->request->post('numero') ?? ''),
            'bairro' => trim($this->request->post('bairro') ?? ''),
            'cidade' => trim($this->request->post('cidade') ?? ''),
            'uf' => trim($this->request->post('uf') ?? ''),
            'complemento' => trim($this->request->post('complemento') ?? ''),
            'comentario' => trim($this->request->post('comentario') ?? '')
        ];
        
        $this->redirect('/checkout/frete');
    }
    
    /**
     * Página de escolha de frete
     */
    public function shipping(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/checkout/frete';
            $this->redirect('/login');
            return;
        }
        
        require_once ROOT_PATH . '/includes/cart-functions.php';
        $cart = get_cart();
        
        if (empty($cart)) {
            $_SESSION['error'] = 'Seu carrinho está vazio';
            $this->redirect('/checkout/carrinho');
            return;
        }
        
        if (!isset($_SESSION['checkout_endereco'])) {
            $_SESSION['error'] = 'Preencha o endereço de entrega primeiro';
            $this->redirect('/checkout/endereco');
            return;
        }
        
        $endereco = $_SESSION['checkout_endereco'];
        $subtotal = get_cart_subtotal();
        $frete_selecionado = $_SESSION['checkout_frete']['opcao'] ?? '';
        $frete_valor = isset($_SESSION['checkout_frete']['preco']) ? (float)$_SESSION['checkout_frete']['preco'] : 0.0;
        
        $data = [
            'pageTitle' => 'Escolha o Frete | Batrip',
            'endereco' => $endereco,
            'subtotal' => $subtotal,
            'frete_selecionado' => $frete_selecionado,
            'frete_valor' => $frete_valor,
            'layout' => 'main'
        ];
        
        $this->view('checkout.shipping', $data);
    }
    
    /**
     * Salva frete selecionado
     */
    public function saveShipping(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        if (!$this->request->isPost()) {
            $this->redirect('/checkout/frete');
            return;
        }
        
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        if (!$this->validateCsrf($token)) {
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect('/checkout/frete');
            return;
        }
        
        $frete_opcao = $this->request->post('frete') ?? '';
        $frete_preco = (float)($this->request->post('frete_valor') ?? 0);
        $frete_prazo = $this->request->post('frete_prazo') ?? '';
        
        $_SESSION['checkout_frete'] = [
            'opcao' => $frete_opcao,
            'preco' => $frete_preco,
            'prazo' => $frete_prazo
        ];
        
        $this->redirect('/checkout/pagamento');
    }
    
    /**
     * API para calcular frete
     */
    public function calculateShipping(): void
    {
        // Limpar buffer antes de enviar JSON
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        $cepDestino = '';
        if (isset($_SESSION['checkout_endereco']['cep'])) {
            $cepDestinoRaw = $_SESSION['checkout_endereco']['cep'] ?? '';
            $cepDestino = preg_replace('/\D/', '', $cepDestinoRaw);
        } elseif (isset($_GET['cep'])) {
            $cepDestinoRaw = $_GET['cep'] ?? '';
            $cepDestino = preg_replace('/\D/', '', $cepDestinoRaw);
        }
        
        // Validação inicial do CEP destino
        if (empty($cepDestino) || strlen($cepDestino) !== 8 || !preg_match('/^\d{8}$/', $cepDestino)) {
            error_log("SuperFrete: CEP destino inválido na primeira validação - '{$cepDestino}' (comprimento: " . strlen($cepDestino) . ")");
            echo json_encode([
                'error' => 'CEP destino inválido. Por favor, verifique o CEP informado.',
                'debug' => [
                    'cep_destino' => $cepDestino,
                    'cep_destino_length' => strlen($cepDestino),
                    'cep_destino_is_numeric' => ctype_digit($cepDestino)
                ]
            ]);
            exit;
        }
        
        // Carrega .env se existir
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
            if (file_exists(ROOT_PATH . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
                $dotenv->load();
            }
        }
        
        $token = $_ENV['SUPERFRETE_TOKEN'] ?? getenv('SUPERFRETE_TOKEN') ?? '';
        if (!$token) {
            echo json_encode(['error' => 'Token da SuperFrete não configurado']);
            exit;
        }
        
        // Buscar itens do carrinho
        require_once ROOT_PATH . '/includes/cart-functions.php';
        require_once ROOT_PATH . '/includes/db.php';
        
        $cart = get_cart();
        if (empty($cart)) {
            echo json_encode(['error' => 'Carrinho vazio']);
            exit;
        }
        
        // Calcular peso total e valor total
        $pesoTotal = 0.0;
        $valorTotal = 0.0;
        $totalItens = 0;
        
        foreach ($cart as $item) {
            $qty = (int)($item['qty'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            
            // Peso padrão por item: 0.5kg (pode ser ajustado)
            // Se houver campo weight no produto, usar ele
            $pesoItem = 0.5;
            $pesoTotal += $pesoItem * $qty;
            
            $valorTotal += $price * $qty;
            $totalItens += $qty;
        }
        
        // Garantir peso mínimo de 0.3kg
        if ($pesoTotal < 0.3) {
            $pesoTotal = 0.3;
        }
        
        // CEP de origem (configurável via .env ou padrão)
        $cepOrigemEnv = $_ENV['SUPERFRETE_CEP_ORIGEM'] ?? getenv('SUPERFRETE_CEP_ORIGEM') ?? '';
        
        // Limpar e validar CEP de origem
        if (!empty($cepOrigemEnv)) {
            $cepOrigem = preg_replace('/\D/', '', $cepOrigemEnv);
            // Validar se tem 8 dígitos após limpar
            if (strlen($cepOrigem) !== 8 || !preg_match('/^\d{8}$/', $cepOrigem)) {
                error_log("SuperFrete: CEP de origem inválido do ENV ({$cepOrigemEnv} -> {$cepOrigem}), usando padrão");
                $cepOrigem = '04696906'; // CEP padrão: São Paulo - SP
            }
        } else {
            $cepOrigem = '04696906'; // CEP padrão: São Paulo - SP
        }
        
        // Log do CEP de origem que será usado
        Logger::info('SuperFrete CEP Origem', [
            'cep_origem_env' => $cepOrigemEnv ?? 'não configurado',
            'cep_origem_final' => $cepOrigem
        ]);
        
        // Dimensões padrão (pode ser ajustado ou buscado do produto)
        // Como não temos dimensões no banco, usamos valores padrão por item
        // Cada item: 20cm x 15cm x 5cm (mínimos aceitos pela API)
        // Valores mínimos da API: height >= 2, length >= 16, width >= 11
        $alturaUnitaria = max(5, 2); // cm (mínimo 2cm)
        $comprimentoBase = max(20, 16); // cm (mínimo 16cm)
        $larguraBase = max(15, 11); // cm (mínimo 11cm)
        
        // Serviços de entrega desejados (OBRIGATÓRIO segundo a documentação)
        // 1: PAC, 2: SEDEX, 17: Mini Envios, 3: Jadlog, 31: Loggi
        // Configurável via .env ou usar padrão (PAC e SEDEX)
        $services = $_ENV['SUPERFRETE_SERVICES'] ?? getenv('SUPERFRETE_SERVICES') ?? '1,2';
        $services = trim($services); // Remove espaços
        
        // Validar formato dos serviços (deve ser uma string com números separados por vírgula)
        if (!preg_match('/^[\d,]+$/', $services)) {
            error_log("SuperFrete: Formato de serviços inválido ({$services}), usando padrão");
            $services = '1,2'; // PAC e SEDEX como padrão
        }
        
        // Preparar produtos para SuperFrete
        // A API espera um array de produtos
        // Vamos enviar um único produto agregado com todas as dimensões e peso totais
        // IMPORTANTE: As dimensões mínimas são height >= 2, length >= 16, width >= 11, weight >= 0.1
        $products = [[
            'quantity' => 1,
            'height' => max($alturaUnitaria, 2), // Mínimo 2cm
            'length' => max($comprimentoBase, 16), // Mínimo 16cm
            'width' => max($larguraBase, 11), // Mínimo 11cm
            'weight' => max($pesoTotal, 0.1) // Mínimo 0.1kg
        ]];
        
        // Validar dimensões antes de enviar
        if ($products[0]['height'] < 2 || $products[0]['length'] < 16 || $products[0]['width'] < 11) {
            error_log("SuperFrete: Dimensões inválidas - " . json_encode($products[0]));
            echo json_encode([
                'error' => 'Dimensões do produto inválidas para cálculo de frete',
                'debug' => ['products' => $products],
                'simulado' => false
            ]);
            exit;
        }
        
        if ($products[0]['weight'] < 0.1) {
            error_log("SuperFrete: Peso inválido - " . $products[0]['weight']);
            echo json_encode([
                'error' => 'Peso do produto inválido para cálculo de frete',
                'debug' => ['weight' => $products[0]['weight']],
                'simulado' => false
            ]);
            exit;
        }
        
        // Validar ambos os CEPs antes de continuar
        $cepOrigemValido = (strlen($cepOrigem) === 8 && preg_match('/^\d{8}$/', $cepOrigem));
        $cepDestinoValido = (strlen($cepDestino) === 8 && preg_match('/^\d{8}$/', $cepDestino));
        
        if (!$cepOrigemValido || !$cepDestinoValido) {
            $erros = [];
            if (!$cepOrigemValido) {
                $erros[] = "CEP de origem inválido: '{$cepOrigem}' (comprimento: " . strlen($cepOrigem) . ")";
            }
            if (!$cepDestinoValido) {
                $erros[] = "CEP de destino inválido: '{$cepDestino}' (comprimento: " . strlen($cepDestino) . ")";
            }
            
            error_log("SuperFrete: Validação de CEPs falhou - " . implode(', ', $erros));
            
            // Se o CEP de origem estiver inválido, usar padrão
            if (!$cepOrigemValido) {
                $cepOrigem = '04696906';
                error_log("SuperFrete: Corrigido CEP de origem para padrão: {$cepOrigem}");
                $cepOrigemValido = true;
            }
            
            // Se após corrigir o CEP de origem, ainda houver erro no destino, reportar
            if (!$cepDestinoValido) {
                echo json_encode([
                    'error' => 'CEP de destino inválido. O CEP deve conter exatamente 8 dígitos numéricos.',
                    'debug' => [
                        'cep_origem' => $cepOrigem,
                        'cep_destino' => $cepDestino,
                        'cep_destino_length' => strlen($cepDestino),
                        'cep_destino_is_numeric' => ctype_digit($cepDestino),
                        'cep_destino_valid' => false,
                        'cep_origem_length' => strlen($cepOrigem),
                        'cep_origem_valid' => $cepOrigemValido,
                        'erros' => $erros
                    ],
                    'simulado' => false
                ]);
                exit;
            }
        }
        
        // Montar requisição conforme documentação da SuperFrete
        // Campo 'services' é OBRIGATÓRIO
        $postData = [
            'from' => [
                'postal_code' => $cepOrigem
            ],
            'to' => [
                'postal_code' => $cepDestino
            ],
            'services' => $services, // OBRIGATÓRIO: códigos dos serviços separados por vírgula
            'options' => [
                'own_hand' => false,
                'receipt' => false,
                'insurance_value' => max((float)$valorTotal, 0.01), // Mínimo 0.01
                'use_insurance_value' => false
            ],
            'products' => $products
        ];
        
        // Log da requisição para debug
        Logger::info('SuperFrete Request Data', [
            'post_data' => $postData,
            'json_encoded' => json_encode($postData)
        ]);
        
        // Fazer requisição para SuperFrete
        $ch = curl_init('https://sandbox.superfrete.com/api/v0/calculator');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'User-Agent: Batrip (integracao@superfrete.com)',
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Log para debug
        Logger::info('Cálculo de frete SuperFrete', [
            'cep_destino' => $cepDestino,
            'cep_origem' => $cepOrigem,
            'peso_total' => $pesoTotal,
            'valor_total' => $valorTotal,
            'total_itens' => $totalItens,
            'http_code' => $httpCode,
            'response_length' => strlen($response)
        ]);
        
        if ($curlError) {
            error_log("SuperFrete CURL Error: " . $curlError);
            echo json_encode([
                'error' => 'Erro na requisição: ' . $curlError,
                'simulado' => false
            ]);
            exit;
        }
        
        $json = json_decode($response, true);
        
        if ($httpCode !== 200) {
            // Tentar extrair mensagem de erro da resposta
            $errorMessage = "Erro na API SuperFrete (HTTP {$httpCode})";
            $errorDetails = [];
            
            if (is_array($json)) {
                if (isset($json['message'])) {
                    $errorMessage = $json['message'];
                } elseif (isset($json['error'])) {
                    $errorMessage = is_string($json['error']) ? $json['error'] : json_encode($json['error']);
                } elseif (isset($json['errors']) && is_array($json['errors'])) {
                    $errorMessage = implode(', ', $json['errors']);
                }
                $errorDetails = $json;
            } elseif (is_string($response) && !empty($response)) {
                $errorDetails['raw_response'] = substr($response, 0, 500);
            }
            
            error_log("SuperFrete HTTP Error {$httpCode}: " . $errorMessage);
            error_log("SuperFrete Full Response: " . substr($response, 0, 1000));
            error_log("SuperFrete Request Data: " . json_encode($postData));
            
            echo json_encode([
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $errorDetails,
                'request_preview' => [
                    'cep_origem' => $cepOrigem,
                    'cep_destino' => $cepDestino,
                    'products_count' => count($products),
                    'first_product' => $products[0] ?? null
                ],
                'simulado' => false
            ]);
            exit;
        }
        
        // Processar resposta
        // A API pode retornar um array de serviços ou um objeto com erro
        if (!is_array($json)) {
            $errorMsg = is_string($json) ? $json : 'Resposta inválida da API SuperFrete';
            error_log("SuperFrete Invalid Response: " . $response);
            echo json_encode([
                'error' => $errorMsg,
                'debug' => ['response' => substr($response, 0, 500)],
                'simulado' => false
            ]);
            exit;
        }
        
        // Verificar se é um array de serviços ou um objeto com erro
        $isArrayOfServices = !empty($json) && is_array($json) && isset($json[0]) && is_array($json[0]);
        
        if ($isArrayOfServices) {
            $result = [];
            foreach ($json as $servico) {
                if (!is_array($servico)) {
                    continue;
                }
                
                // Ignorar serviços com erro explícito
                if (!empty($servico['error']) || !empty($servico['erro'])) {
                    error_log("SuperFrete Service Error: " . json_encode($servico));
                    continue;
                }
                
                $nome = $servico['name'] ?? $servico['service_name'] ?? $servico['nome'] ?? 'Desconhecido';
                
                // Tentar diferentes campos para o preço
                $preco = null;
                if (isset($servico['price']) && is_numeric($servico['price'])) {
                    $preco = (float)$servico['price'];
                } elseif (isset($servico['price_cents']) && is_numeric($servico['price_cents'])) {
                    $preco = (float)$servico['price_cents'] / 100; // Converter centavos para reais
                } elseif (isset($servico['valor']) && is_numeric($servico['valor'])) {
                    $preco = (float)$servico['valor'];
                }
                
                // Tentar diferentes campos para o prazo
                $prazo = null;
                if (isset($servico['delivery_time']) && is_numeric($servico['delivery_time'])) {
                    $prazo = (int)$servico['delivery_time'];
                } elseif (isset($servico['prazo']) && is_numeric($servico['prazo'])) {
                    $prazo = (int)$servico['prazo'];
                } elseif (isset($servico['days']) && is_numeric($servico['days'])) {
                    $prazo = (int)$servico['days'];
                }
                
                // Só adicionar se tiver preço válido
                if ($preco !== null && $preco > 0) {
                    $result[$nome] = [
                        'valor' => number_format($preco, 2, '.', ''),
                        'prazo' => $prazo ?? 0,
                        'erro' => ''
                    ];
                } else {
                    error_log("SuperFrete Service sem preço válido: " . json_encode($servico));
                }
            }
            
            // Se não encontrou nenhum serviço válido, retornar erro com debug
            if (empty($result)) {
                error_log("SuperFrete: Nenhum serviço válido encontrado. Resposta: " . substr($response, 0, 1000));
                echo json_encode([
                    'error' => 'Nenhuma opção de frete disponível para este CEP. Verifique se o CEP está correto ou entre em contato com o suporte.',
                    'debug' => [
                        'response_preview' => substr($response, 0, 500),
                        'json_structure' => is_array($json) ? array_keys($json[0] ?? []) : 'not_array',
                        'total_services' => count($json),
                        'cep_origem' => $cepOrigem,
                        'cep_destino' => $cepDestino
                    ],
                    'simulado' => false
                ]);
                exit;
            }
            
            echo json_encode([
                'result' => $result,
                'cepDestino' => $cepDestino,
                'simulado' => false
            ]);
        } else {
            // Se não retornou no formato esperado, pode ser um erro
            $errorMsg = $json['message'] ?? $json['error'] ?? $json['errors'] ?? 'Resposta inválida da API SuperFrete';
            
            if (is_array($errorMsg)) {
                $errorMsg = json_encode($errorMsg);
            }
            
            error_log("SuperFrete Response Error: " . $errorMsg);
            error_log("SuperFrete Full Response: " . substr($response, 0, 1000));
            
            echo json_encode([
                'error' => is_string($errorMsg) ? $errorMsg : 'Erro ao calcular frete. Tente novamente.',
                'debug' => [
                    'json_keys' => is_array($json) ? array_keys($json) : 'not_array',
                    'response_preview' => substr($response, 0, 500)
                ],
                'simulado' => false
            ]);
        }
        exit;
    }
    
    /**
     * Página de pagamento
     */
    public function payment(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/checkout/pagamento';
            $this->redirect('/login');
            return;
        }
        
        require_once ROOT_PATH . '/includes/cart-functions.php';
        $cart = get_cart();
        
        if (empty($cart)) {
            $_SESSION['error'] = 'Seu carrinho está vazio';
            $this->redirect('/checkout/carrinho');
            return;
        }
        
        if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
            $_SESSION['error'] = 'Preencha o endereço e escolha o frete primeiro';
            $this->redirect('/checkout/endereco');
            return;
        }
        
        $subtotal = get_cart_subtotal();
        $frete = isset($_SESSION['checkout_frete']['preco']) && is_numeric($_SESSION['checkout_frete']['preco']) ? (float)$_SESSION['checkout_frete']['preco'] : 0.0;
        $total = $subtotal + $frete;
        
        // Carrega .env para Mercado Pago
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
            if (file_exists(ROOT_PATH . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
                $dotenv->load();
            }
        }
        
        $mp_access_token = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? $_SERVER['MERCADOPAGO_ACCESS_TOKEN'] ?? getenv('MERCADOPAGO_ACCESS_TOKEN');
        $mp_public_key = $_ENV['MERCADOPAGO_PUBLIC_KEY'] ?? $_SERVER['MERCADOPAGO_PUBLIC_KEY'] ?? getenv('MERCADOPAGO_PUBLIC_KEY');
        
        $data = [
            'pageTitle' => 'Pagamento | Batrip',
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total' => $total,
            'mp_public_key' => $mp_public_key,
            'layout' => 'main'
        ];
        
        $this->view('checkout.payment', $data);
    }
    
    /**
     * Processa pagamento (API JSON)
     */
    public function processPayment(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Não autenticado']);
            exit;
        }
        
        if (!$this->request->isPost() || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Requisição inválida']);
            exit;
        }
        
        require_once ROOT_PATH . '/includes/cart-functions.php';
        $cart = get_cart();
        
        if (empty($cart)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Carrinho vazio']);
            exit;
        }
        
        if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Endereço ou frete não configurado']);
            exit;
        }
        
        // Carrega .env
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
            if (file_exists(ROOT_PATH . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
                $dotenv->load();
            }
        }
        
        $mp_access_token = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? $_SERVER['MERCADOPAGO_ACCESS_TOKEN'] ?? getenv('MERCADOPAGO_ACCESS_TOKEN');
        if (!$mp_access_token) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Access token do Mercado Pago não configurado']);
            exit;
        }
        
        \MercadoPago\SDK::setAccessToken($mp_access_token);
        
        $input = json_decode(file_get_contents('php://input'), true);
        $metodo = $input['metodo'] ?? 'cartao';
        
        $subtotal = get_cart_subtotal();
        $frete = isset($_SESSION['checkout_frete']['preco']) && is_numeric($_SESSION['checkout_frete']['preco']) ? (float)$_SESSION['checkout_frete']['preco'] : 0.0;
        $total = $subtotal + $frete;
        
        // Monta itens do carrinho
        $items = [];
        foreach ($cart as $item) {
            $items[] = [
                'title' => $item['name'] ?? 'Produto',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => (float)($item['price'] ?? 0),
                'currency_id' => 'BRL'
            ];
        }
        $items[] = [
            'title' => 'Frete',
            'quantity' => 1,
            'unit_price' => (float)$frete,
            'currency_id' => 'BRL'
        ];
        
        if ($metodo === 'cartao') {
            if (empty($input['token']) || empty($input['paymentMethodId']) || empty($input['docNumber'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Dados de pagamento incompletos.']);
                exit;
            }
            $payment = new \MercadoPago\Payment();
            $payment->transaction_amount = (float)$total;
            $payment->token = $input['token'];
            $payment->description = 'Compra Batrip';
            $payment->installments = 1;
            $payment->payment_method_id = $input['paymentMethodId'];
            $payment->payer = [
                'email' => $input['email'] ?? 'comprador@batrip.com',
                'identification' => [
                    'type' => $input['docType'] ?? 'CPF',
                    'number' => $input['docNumber']
                ]
            ];
            $payment->metadata = ['items' => $items];
            $payment->save();
            if ($payment->status === 'approved') {
                $_SESSION['checkout_pagamento'] = [
                    'metodo' => 'cartao',
                    'status' => $payment->status,
                    'id' => $payment->id,
                    'email' => $input['email'] ?? 'comprador@batrip.com',
                    'valor' => $total,
                    'raw' => json_encode($payment)
                ];
                echo json_encode(['status' => 'success', 'redirect' => '/checkout/finalizar']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pagamento não aprovado: ' . ($payment->status_detail ?? 'Erro desconhecido')]);
            }
            exit;
        } elseif ($metodo === 'boleto') {
            if (empty($input['name']) || empty($input['email']) || empty($input['docNumber'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Dados de boleto incompletos.']);
                exit;
            }
            $payment = new \MercadoPago\Payment();
            $payment->transaction_amount = (float)$total;
            $payment->description = 'Compra Batrip';
            $payment->payment_method_id = 'bolbradesco';
            $payment->payer = [
                'email' => $input['email'],
                'first_name' => $input['name'],
                'identification' => [
                    'type' => 'CPF',
                    'number' => $input['docNumber']
                ]
            ];
            $payment->metadata = ['items' => $items];
            $payment->save();
            if ($payment->status === 'pending' && isset($payment->transaction_details->external_resource_url)) {
                $_SESSION['checkout_pagamento'] = [
                    'metodo' => 'boleto',
                    'status' => $payment->status,
                    'id' => $payment->id,
                    'email' => $input['email'],
                    'valor' => $total,
                    'raw' => json_encode($payment)
                ];
                echo json_encode(['status' => 'success', 'redirect' => $payment->transaction_details->external_resource_url]);
            } else {
                Logger::error('Erro ao gerar boleto', ['payment' => $payment]);
                echo json_encode(['status' => 'error', 'message' => 'Não foi possível gerar o boleto.']);
            }
            exit;
        } elseif ($metodo === 'pix') {
            if (empty($input['name']) || empty($input['email']) || empty($input['docNumber'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Dados de Pix incompletos.']);
                exit;
            }
            
            // Apenas salva os dados do pagador na sessão para gerar o QR Code na revisão
            $_SESSION['checkout_pagamento'] = [
                'metodo' => 'pix',
                'name' => $input['name'],
                'email' => $input['email'],
                'docNumber' => $input['docNumber'],
                'valor' => $total,
                'status' => 'pending', // Será atualizado na revisão quando o QR Code for gerado
                'items' => $items // Salva itens para usar na criação do pagamento
            ];
            
            echo json_encode([
                'status' => 'success',
                'redirect' => '/checkout/revisao' // Redireciona para revisão onde o QR Code será gerado
            ]);
            exit;
        }
        
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Método de pagamento inválido.']);
        exit;
    }
    
    /**
     * Retorna a chave pública do Mercado Pago (API)
     */
    public function getMpPublicKey(): void
    {
        header('Content-Type: application/json');
        
        // Carrega .env se existir
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
            if (file_exists(ROOT_PATH . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
                $dotenv->load();
            }
        }
        
        $mp_public_key = $_ENV['MERCADOPAGO_PUBLIC_KEY'] ?? $_SERVER['MERCADOPAGO_PUBLIC_KEY'] ?? getenv('MERCADOPAGO_PUBLIC_KEY');
        
        if ($mp_public_key) {
            echo json_encode(['public_key' => $mp_public_key]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Chave pública do Mercado Pago não configurada']);
        }
        exit;
    }
    
    /**
     * Página de revisão do pedido
     */
    public function review(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/checkout/revisao';
            $this->redirect('/login');
            return;
        }
        
        require_once ROOT_PATH . '/includes/cart-functions.php';
        require_once ROOT_PATH . '/includes/db.php';
        
        // MODO DE TESTE: Permitir revisão sem pagamento (sempre ativo para testes)
        // Para desativar em produção, remova ou comente esta linha
        $isTestMode = true; // Sempre permitir finalizar sem pagamento para testes
        
        if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
            $_SESSION['error'] = 'Complete todas as etapas do checkout';
            $this->redirect('/checkout/endereco');
            return;
        }
        
        // Em modo de teste, não exigir pagamento
        // if (!$isTestMode && !isset($_SESSION['checkout_pagamento'])) {
        //     $_SESSION['error'] = 'Complete todas as etapas do checkout';
        //     $this->redirect('/checkout/pagamento');
        //     return;
        // }
        
        $cart = get_cart();
        if (empty($cart)) {
            $_SESSION['error'] = 'Seu carrinho está vazio';
            $this->redirect('/checkout/carrinho');
            return;
        }
        
        $cart_items = [];
        $subtotal = 0;
        foreach ($cart as $item) {
            $productId = isset($item['id']) ? (int)$item['id'] : 0;
            if ($productId > 0) {
                try {
                    $stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND active = 1');
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();
                    if ($product) {
                        $quantity = isset($item['qty']) ? (int)$item['qty'] : 1;
                        $size = isset($item['size']) ? trim($item['size']) : 'M';
                        $item_subtotal = $product['price'] * $quantity;
                        $subtotal += $item_subtotal;
                        $cart_items[] = [
                            'id' => $product['id'],
                            'title' => $product['title'],
                            'price' => (float)$product['price'],
                            'quantity' => $quantity,
                            'size' => $size,
                            'subtotal' => $item_subtotal
                        ];
                    }
                } catch (\PDOException $e) {
                    Logger::error('Erro ao buscar produto na revisão', ['product_id' => $productId, 'error' => $e->getMessage()]);
                }
            }
        }
        
        $frete = $_SESSION['checkout_frete']['preco'];
        $total = $subtotal + $frete;
        
        $pagamento = $_SESSION['checkout_pagamento'] ?? [];
        $metodo = $pagamento['metodo'] ?? '';
        
        // Se o método for PIX e ainda não tiver QR Code gerado, gerar agora
        $pixQrCode = null;
        $pixCopyPaste = null;
        
        if ($metodo === 'pix') {
            // Verifica se já tem QR Code gerado
            if (isset($pagamento['pix_qr']) && isset($pagamento['pix_copy'])) {
                // QR Code já foi gerado anteriormente
                $pixQrCode = $pagamento['pix_qr'];
                $pixCopyPaste = $pagamento['pix_copy'];
            } else {
                // Precisa gerar o QR Code agora
                try {
                    // Carrega .env se existir
                    if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
                        require_once ROOT_PATH . '/vendor/autoload.php';
                        if (file_exists(ROOT_PATH . '/.env')) {
                            $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
                            $dotenv->load();
                        }
                    }
                    
                    $mp_access_token = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? getenv('MERCADOPAGO_ACCESS_TOKEN') ?? '';
                    if ($mp_access_token) {
                        \MercadoPago\SDK::setAccessToken($mp_access_token);
                        
                        // Preparar itens para o pagamento
                        $items = [];
                        foreach ($cart_items as $item) {
                            $items[] = [
                                'title' => $item['title'],
                                'quantity' => $item['quantity'],
                                'unit_price' => (float)$item['price'],
                                'currency_id' => 'BRL'
                            ];
                        }
                        $items[] = [
                            'title' => 'Frete',
                            'quantity' => 1,
                            'unit_price' => (float)$frete,
                            'currency_id' => 'BRL'
                        ];
                        
                        // Criar pagamento PIX
                        $payment = new \MercadoPago\Payment();
                        $payment->transaction_amount = (float)$total;
                        $payment->description = 'Compra Batrip';
                        $payment->payment_method_id = 'pix';
                        $payment->payer = [
                            'email' => $pagamento['email'] ?? '',
                            'first_name' => $pagamento['name'] ?? '',
                            'identification' => [
                                'type' => 'CPF',
                                'number' => $pagamento['docNumber'] ?? ''
                            ]
                        ];
                        $payment->metadata = ['items' => $items];
                        $payment->save();
                        
                        if ($payment->status === 'pending' && isset($payment->point_of_interaction->transaction_data->qr_code_base64)) {
                            $pixQrCode = $payment->point_of_interaction->transaction_data->qr_code_base64;
                            $pixCopyPaste = $payment->point_of_interaction->transaction_data->qr_code;
                            
                            // Atualizar sessão com o QR Code gerado
                            $_SESSION['checkout_pagamento']['pix_qr'] = $pixQrCode;
                            $_SESSION['checkout_pagamento']['pix_copy'] = $pixCopyPaste;
                            $_SESSION['checkout_pagamento']['id'] = $payment->id;
                            $_SESSION['checkout_pagamento']['status'] = $payment->status;
                            $_SESSION['checkout_pagamento']['raw'] = json_encode($payment);
                            
                            Logger::info('QR Code PIX gerado na revisão', [
                                'payment_id' => $payment->id,
                                'total' => $total
                            ]);
                        } else {
                            Logger::error('Erro ao gerar QR Code PIX na revisão', [
                                'payment_status' => $payment->status ?? null,
                                'payment_error' => $payment->error ?? null
                            ]);
                            $_SESSION['error'] = 'Erro ao gerar QR Code do PIX. Tente novamente.';
                        }
                    }
                } catch (\Exception $e) {
                    Logger::error('Exceção ao gerar QR Code PIX na revisão', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $_SESSION['error'] = 'Erro ao gerar QR Code do PIX: ' . $e->getMessage();
                }
            }
        }
        
        $data = [
            'pageTitle' => 'Revisão do Pedido | Batrip',
            'cart_items' => $cart_items,
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total' => $total,
            'pix_qr_code' => $pixQrCode,
            'pix_copy_paste' => $pixCopyPaste,
            'layout' => 'main'
        ];
        
        $this->view('checkout.review', $data);
    }
    
    /**
     * Finaliza o pedido
     */
    public function finalize(): void
    {
        // Log inicial para debug
        error_log("CheckoutController::finalize - Método chamado");
        error_log("CheckoutController::finalize - REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));
        error_log("CheckoutController::finalize - POST data: " . json_encode($_POST));
        error_log("CheckoutController::finalize - isPost(): " . ($this->request->isPost() ? 'true' : 'false'));
        
        if (!isset($_SESSION['user_id'])) {
            error_log("CheckoutController::finalize - Usuário não logado");
            $_SESSION['redirect_after_login'] = '/checkout/finalizar';
            $this->redirect('/login');
            return;
        }
        
        if (!$this->request->isPost()) {
            error_log("CheckoutController::finalize - Não é POST, redirecionando para revisão");
            $this->redirect('/checkout/revisao');
            return;
        }
        
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        error_log("CheckoutController::finalize - CSRF token recebido: " . ($token ? 'SIM (' . substr($token, 0, 10) . '...)' : 'NÃO'));
        error_log("CheckoutController::finalize - CSRF token da sessão: " . (isset($_SESSION['csrf_token']) ? 'SIM (' . substr($_SESSION['csrf_token'], 0, 10) . '...)' : 'NÃO DEFINIDO'));
        error_log("CheckoutController::finalize - POST completo: " . json_encode($_POST));
        
        // Validar CSRF
        if (!$this->validateCsrf($token)) {
            error_log("CheckoutController::finalize - CSRF inválido");
            error_log("CheckoutController::finalize - Token recebido: " . var_export($token, true));
            error_log("CheckoutController::finalize - Token na sessão: " . var_export($_SESSION['csrf_token'] ?? 'NÃO DEFINIDO', true));
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect('/checkout/revisao');
            return;
        }
        
        error_log("CheckoutController::finalize - CSRF validado com sucesso");
        
        error_log("CheckoutController::finalize - Validações passaram, iniciando processamento");
        
        require_once ROOT_PATH . '/includes/cart-functions.php';
        require_once ROOT_PATH . '/includes/db.php';
        
        if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
            $_SESSION['error'] = 'Sessão de checkout incompleta';
            $this->redirect('/checkout/endereco');
            return;
        }
        
        $cart = get_cart();
        if (empty($cart)) {
            $_SESSION['error'] = 'Carrinho vazio';
            $this->redirect('/checkout/carrinho');
            return;
        }
        
        // MODO DE TESTE: Permitir finalizar sem pagamento (sempre ativo para testes)
        $isTestMode = true; // Sempre permitir finalizar sem pagamento para testes
        
        try {
            // Limpar qualquer output buffer antes de começar
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            Logger::info('Iniciando finalização do pedido', [
                'user_id' => $_SESSION['user_id'],
                'has_endereco' => isset($_SESSION['checkout_endereco']),
                'has_frete' => isset($_SESSION['checkout_frete']),
                'has_pagamento' => isset($_SESSION['checkout_pagamento'])
            ]);
            
            $pdo->beginTransaction();
            
            $subtotal = 0;
            $cart_items = [];
            foreach ($cart as $item) {
                $productId = isset($item['id']) ? (int)$item['id'] : 0;
                if ($productId > 0) {
                    $stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND active = 1');
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();
                    if ($product) {
                        $quantity = isset($item['qty']) ? (int)$item['qty'] : 1;
                        $size = isset($item['size']) ? trim($item['size']) : 'M';
                        $item_subtotal = $product['price'] * $quantity;
                        $subtotal += $item_subtotal;
                        $cart_items[] = [
                            'product_id' => $product['id'],
                            'title' => $product['title'],
                            'price' => (float)$product['price'],
                            'quantity' => $quantity,
                            'size' => $size
                        ];
                    }
                }
            }
            
            $frete = $_SESSION['checkout_frete']['preco'] ?? 0;
            $total = $subtotal + $frete;
            $endereco = $_SESSION['checkout_endereco'];
            $frete_data = $_SESSION['checkout_frete'];
            $endereco_json = json_encode($endereco);
            $frete_json = json_encode($frete_data);
            
            // Buscar dados do usuário
            $stmt = $pdo->prepare('SELECT name, email, phone FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            // Determinar método de pagamento (em modo de teste, usar 'teste' se não houver)
            $paymentMethod = 'teste';
            if (isset($_SESSION['checkout_pagamento']['metodo'])) {
                $paymentMethod = $_SESSION['checkout_pagamento']['metodo'];
            } elseif (!$isTestMode) {
                $paymentMethod = 'pix'; // Padrão para produção
            }
            
            $orderData = [
                'user_id' => $_SESSION['user_id'],
                'customer_name' => $user['name'] ?? '',
                'customer_email' => $user['email'] ?? '',
                'customer_phone' => $user['phone'] ?? '',
                'shipping_address' => ($endereco['endereco'] ?? '') . ', ' . ($endereco['numero'] ?? ''),
                'shipping_city' => $endereco['cidade'] ?? '',
                'shipping_state' => $endereco['uf'] ?? '',
                'shipping_zipcode' => $endereco['cep'] ?? '',
                'shipping_method' => $frete_data['opcao'] ?? '',
                'shipping_cost' => $frete,
                'payment_method' => $paymentMethod,
                'endereco' => $endereco_json,
                'frete' => $frete_json,
                'items' => json_encode($cart_items),
                'subtotal' => $subtotal,
                'shipping' => $frete, // Coluna 'shipping' também existe na tabela
                'total' => $total,
                'status' => 'pending'
            ];
            
            Logger::info('Dados do pedido preparados', [
                'order_data_keys' => array_keys($orderData),
                'subtotal' => $subtotal,
                'frete' => $frete,
                'total' => $total
            ]);
            
            $orderId = $this->orderModel->create($orderData);
            
            Logger::info('Resultado do create()', ['orderId' => $orderId, 'type' => gettype($orderId)]);
            
            if ($orderId === false || $orderId === null || $orderId === 0) {
                error_log("CheckoutController::finalize - create() retornou: " . var_export($orderId, true));
                throw new \Exception('Falha ao criar pedido: create() retornou ' . var_export($orderId, true));
            }
            
            $pdo->commit();
            
            // Salvar ID do pedido na sessão ANTES de limpar outras variáveis
            $_SESSION['last_order_id'] = $orderId;
            
            // Limpar sessão de checkout (mas manter last_order_id)
            unset($_SESSION['cart'], $_SESSION['checkout_endereco'], $_SESSION['checkout_frete'], $_SESSION['checkout_pagamento']);
            
            Logger::info('Pedido finalizado com sucesso', ['order_id' => $orderId]);
            error_log("CheckoutController::finalize - Pedido criado. ID: {$orderId}, last_order_id na sessão: " . ($_SESSION['last_order_id'] ?? 'NÃO DEFINIDO'));
            
            // Limpar output buffer novamente antes de redirecionar
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            error_log("CheckoutController::finalize - Redirecionando para /checkout/sucesso");
            error_log("CheckoutController::finalize - last_order_id na sessão: " . ($_SESSION['last_order_id'] ?? 'NÃO DEFINIDO'));
            
            // Redirecionar para página de sucesso usando header direto para garantir
            $redirectUrl = BASE_URL . 'checkout/sucesso';
            error_log("CheckoutController::finalize - URL de redirecionamento: " . $redirectUrl);
            
            // Garantir que não há output antes do header
            if (headers_sent($file, $line)) {
                error_log("CheckoutController::finalize - ERRO: Headers já foram enviados em {$file}:{$line}");
                // Tentar redirecionamento via JavaScript como fallback
                echo '<script>window.location.href = ' . json_encode($redirectUrl) . ';</script>';
                echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl) . '"></noscript>';
                exit;
            }
            
            header("Location: " . $redirectUrl, true, 302);
            exit; // Garantir que o script para aqui
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error('Erro ao finalizar pedido', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $_SESSION['error'] = 'Erro ao finalizar pedido: ' . $e->getMessage();
            $this->redirect('/checkout/revisao');
        }
    }
}
