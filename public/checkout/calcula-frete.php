<?php
// Limpar qualquer output buffer antes de enviar JSON
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Carregar configurações antes de iniciar sessão
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/config.php';

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir header JSON antes de qualquer output
header('Content-Type: application/json');




// Recupera dados do frete
$cepDestino = '';
if (isset($_SESSION['checkout_endereco']['cep'])) {
    $cepDestino = preg_replace('/\D/', '', $_SESSION['checkout_endereco']['cep']);
} elseif (isset($_GET['cep'])) {
    $cepDestino = preg_replace('/\D/', '', $_GET['cep']);
}
$peso = $_GET['peso'] ?? 1; // em kg
$valor = $_GET['valor'] ?? 100; // valor declarado
$cepOrigem = '04696-906'; // CEP de origem atualizado

if (!$cepDestino || strlen($cepDestino) !== 8 || !preg_match('/^\d{8}$/', $cepDestino)) {
    echo json_encode(['error' => 'CEP destino inválido']);
    exit;
}

// Carrega .env se existir (sem quebrar se Dotenv não estiver disponível)
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
    if (file_exists(ROOT_PATH . '/.env') && class_exists('\Dotenv\Dotenv')) {
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
            $dotenv->load();
        } catch (Exception $e) {
            // Ignora erro de Dotenv, continua sem ele
        }
    }
}

// SuperFrete API
$token = $_ENV['SUPERFRETE_TOKEN'] ?? getenv('SUPERFRETE_TOKEN') ?? '';
if (!$token) {
    echo json_encode(['error' => 'Token da SuperFrete não configurado']);
    exit;
}

// Buscar itens do carrinho
require_once ROOT_PATH . '/includes/cart-functions.php';
$cart = get_cart();

if (empty($cart)) {
    echo json_encode(['error' => 'Carrinho vazio']);
    exit;
}

// Calcular peso total e valor total
$pesoTotal = 0.0;
$valorTotal = 0.0;
foreach ($cart as $item) {
    $qty = (int)($item['qty'] ?? 1);
    $price = (float)($item['price'] ?? 0);
    // Peso padrão por item: 0.5kg
    $pesoItem = 0.5;
    $pesoTotal += $pesoItem * $qty;
    $valorTotal += $price * $qty;
}

// Garantir peso mínimo de 0.3kg
if ($pesoTotal < 0.3) {
    $pesoTotal = 0.3;
}

// Montar produtos para API
$products = [
    [
        'quantity' => 1,
        'height' => 10,
        'length' => 20,
        'width' => 15,
        'weight' => $pesoTotal
    ]
];

$postData = [
    'from' => [ 'postal_code' => $cepOrigem ],
    'to' => [ 'postal_code' => $cepDestino ],
    'options' => [
        'own_hand' => false,
        'receipt' => false,
        'insurance_value' => max(100, $valorTotal),
        'use_insurance_value' => false
    ],
    'products' => $products
];

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
$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);
$json = json_decode($response, true);

if (is_array($json) && isset($json[0]['name'])) {
    $result = [];
    foreach ($json as $servico) {
        $nome = $servico['name'] ?? 'Desconhecido';
        $result[$nome] = [
            'valor' => $servico['price'] ?? null,
            'prazo' => $servico['delivery_time'] ?? null,
            'erro' => $servico['error'] ?? ''
        ];
    }
    echo json_encode(['result' => $result, 'debug' => $json, 'cepDestino' => $cepDestino, 'postData' => $postData]);
} else {
    // Se erro, simula opções de frete para não travar o usuário
    $simulado = [
        'SEDEX Simulado' => [
            'valor' => 39.90,
            'prazo' => 3,
            'erro' => ''
        ],
        'PAC Simulado' => [
            'valor' => 29.90,
            'prazo' => 7,
            'erro' => ''
        ],
        'Transportadora Simulada' => [
            'valor' => 49.90,
            'prazo' => 5,
            'erro' => ''
        ]
    ];
    echo json_encode([
        'result' => $simulado,
        'simulado' => true,
        'error' => $json['error'] ?? $curlError,
        'debug' => $json,
        'cepDestino' => $cepDestino,
        'postData' => $postData,
        'response' => $response
    ]);
}

