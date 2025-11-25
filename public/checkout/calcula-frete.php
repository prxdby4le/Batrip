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
$cepOrigem = '04696000'; // CEP de origem atualizado

if (!$cepDestino) {
    echo json_encode(['error' => 'CEP destino não informado']);
    exit;
}

// Parâmetros para SEDEX e PAC
$servicos = [
    'SEDEX' => '04162',
    'PAC' => '04669'
];
$result = [];
$debug = [];
foreach ($servicos as $nome => $codigo) {
    $postData = [
        'user' => '',
        'code' => '',
        'cartaoPostagem' => '',
        'servico' => $codigo,
        'cepOrigem' => (string)$cepOrigem,
        'cepDestino' => (string)$cepDestino,
        'peso' => (string)$peso,
        'comprimento' => '20',
        'largura' => '15',
        'altura' => '10'
    ];
    $ch = curl_init('https://ws.correios.digitalone.com.br/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    $debug[$nome] = $response;
    $json = json_decode($response, true);
    if (is_array($json) && isset($json['valor']) && isset($json['prazo'])) {
        $result[$nome] = [
            'valor' => $json['valor'],
            'prazo' => $json['prazo'],
            'erro' => $json['erro'] ?? ''
        ];
    } else {
        $result[$nome] = [
            'valor' => null,
            'prazo' => null,
            'erro' => $json['erro'] ?? $curlError
        ];
    }
}
echo json_encode([
    'result' => $result,
    'debug' => $debug
]);

