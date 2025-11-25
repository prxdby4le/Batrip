<?php
header('Content-Type: application/json');
$publicKey = getenv('MERCADOPAGO_PUBLIC_KEY') ?: '';
echo json_encode(['public_key' => $publicKey]);
