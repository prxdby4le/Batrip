<?php

session_start();
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

if (!$cepDestino) {
    echo json_encode(['error' => 'CEP destino não informado']);
    exit;
}


// SuperFrete API
$token = $_ENV['SUPERFRETE_TOKEN'] ?? getenv('SUPERFRETE_TOKEN') ?? '';
if (!$token) {
    echo json_encode(['error' => 'Token da SuperFrete não configurado']);
    exit;
}

// Montar produtos (exemplo fixo, ideal buscar do carrinho)
$products = [
    [
        'quantity' => 1,
        'height' => 10,
        'length' => 20,
        'width' => 15,
        'weight' => (float)$peso
    ]
];
// Se quiser buscar do carrinho, substitua o bloco acima por um foreach nos itens do carrinho

$postData = [
    'from' => [ 'postal_code' => $cepOrigem ],
    'to' => [ 'postal_code' => $cepDestino ],
    // 'services' => '1,2', // Removido para trazer todos os serviços disponíveis
    'options' => [
        'own_hand' => false,
        'receipt' => false,
        'insurance_value' => (float)$valor,
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

