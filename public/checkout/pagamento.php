<?php
// Buffer cedo para evitar 'headers already sent' por BOM/whitespace acidental
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }

$pageTitle = 'Pagamento | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Carrega variáveis do .env se existir (usando vlucas/phpdotenv)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenvPath = __DIR__ . '/../../';
    if (file_exists($dotenvPath . '.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
        $dotenv->load();
    }
}

// Base simples para links relativos a partir de /public/checkout/
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';

// Verificar se endereço e frete foram preenchidos
if (!isset($_SESSION['checkout_endereco']) || !isset($_SESSION['checkout_frete'])) {
    header('Location: endereco.php');
    exit;
}

// Verificar se há itens no carrinho
$cart = get_cart();
if (empty($cart)) {
    header('Location: ' . $base . 'index.php');
    exit;
}

$subtotal = get_cart_subtotal();
$frete = $_SESSION['checkout_frete']['preco'];
$total = $subtotal + $frete;

// Mercado Pago integração
require_once __DIR__ . '/../../vendor/autoload.php';
$mp_access_token = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? $_SERVER['MERCADOPAGO_ACCESS_TOKEN'] ?? getenv('MERCADOPAGO_ACCESS_TOKEN');
if (!$mp_access_token) {
    die('Access token do Mercado Pago não configurado. Defina MERCADOPAGO_ACCESS_TOKEN no .env.');
}
\MercadoPago\SDK::setAccessToken($mp_access_token);

// Se for POST JSON (checkout transparente)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $metodo = $input['metodo'] ?? 'cartao';
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
        $payment->metadata = [ 'items' => $items ];
        $payment->save();
        if ($payment->status === 'approved') {
            unset($_SESSION['cart']);
            echo json_encode(['status' => 'success', 'redirect' => $base . 'revisao.php']);
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
        $payment->metadata = [ 'items' => $items ];
        $payment->save();
        if ($payment->status === 'pending' && isset($payment->transaction_details->external_resource_url)) {
            unset($_SESSION['cart']);
            echo json_encode(['status' => 'success', 'redirect' => $payment->transaction_details->external_resource_url]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Não foi possível gerar o boleto.']);
        }
        exit;
    } elseif ($metodo === 'pix') {
        if (empty($input['name']) || empty($input['email']) || empty($input['docNumber'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Dados de Pix incompletos.']);
            exit;
        }
        $payment = new \MercadoPago\Payment();
        $payment->transaction_amount = (float)$total;
        $payment->description = 'Compra Batrip';
        $payment->payment_method_id = 'pix';
        $payment->payer = [
            'email' => $input['email'],
            'first_name' => $input['name'],
            'identification' => [
                'type' => 'CPF',
                'number' => $input['docNumber']
            ]
        ];
        $payment->metadata = [ 'items' => $items ];
        $payment->save();
        if ($payment->status === 'pending' && isset($payment->point_of_interaction->transaction_data->qr_code_base64)) {
            unset($_SESSION['cart']);
            echo json_encode([
                'status' => 'success',
                'pix_qr' => $payment->point_of_interaction->transaction_data->qr_code_base64,
                'pix_copy' => $payment->point_of_interaction->transaction_data->qr_code,
                'redirect' => $base . 'revisao.php'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Não foi possível gerar o Pix.']);
        }
        exit;
    }
    // fallback
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Método de pagamento inválido.']);
    exit;
}

include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base, ENT_QUOTES) ?>index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="carrinho.php">Carrinho</a></li>
                    <li class="breadcrumb-item"><a href="endereco.php">Endereço</a></li>
                    <li class="breadcrumb-item"><a href="frete.php">Frete</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pagamento</li>
                </ol>
            </nav>
            
            <h2 class="section-title mb-4"><?= icon('credit-card', 'icon') ?> Pagamento</h2>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="alert alert-info">
                        <?= icon('info-circle', 'icon me-2') ?>
                        <strong>Modo de Demonstração:</strong> O pagamento será simulado. Em produção, integração com gateway de pagamento será implementada.
                    </div>
                    
                    <!-- Checkout Transparente Mercado Pago -->
                    <form id="mp-checkout-form" method="POST" autocomplete="off">
                        <div class="card bg-dark text-light mb-3">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Escolha o método de pagamento</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="metodo" id="metodo-cartao" value="cartao" checked>
                                            <label class="form-check-label" for="metodo-cartao">Cartão de Crédito</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="metodo" id="metodo-boleto" value="boleto">
                                            <label class="form-check-label" for="metodo-boleto">Boleto</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="metodo" id="metodo-pix" value="pix">
                                            <label class="form-check-label" for="metodo-pix">Pix</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="mp-card-form">
                                    <div class="mb-3">
                                        <label for="cardNumber" class="form-label">Número do cartão</label>
                                        <input type="text" class="form-control" id="cardNumber" data-checkout="cardNumber" maxlength="19">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="cardExpiration" class="form-label">Validade (MM/AA)</label>
                                            <input type="text" class="form-control" id="cardExpiration" data-checkout="cardExpiration" maxlength="5">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="securityCode" class="form-label">CVV</label>
                                            <input type="text" class="form-control" id="securityCode" data-checkout="securityCode" maxlength="4">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cardholderName" class="form-label">Nome impresso no cartão</label>
                                        <input type="text" class="form-control" id="cardholderName" data-checkout="cardholderName">
                                    </div>
                                    <div class="mb-3">
                                        <label for="docNumber" class="form-label">CPF do titular</label>
                                        <input type="text" class="form-control" id="docNumber" data-checkout="docNumber" maxlength="14">
                                    </div>
                                    <input type="hidden" name="paymentMethodId" id="paymentMethodId">
                                    <input type="hidden" name="token" id="token">
                                </div>
                                <div id="mp-boleto-form" style="display:none;">
                                    <div class="mb-3">
                                        <label for="boletoName" class="form-label">Nome completo</label>
                                        <input type="text" class="form-control" id="boletoName">
                                    </div>
                                    <div class="mb-3">
                                        <label for="boletoEmail" class="form-label">E-mail</label>
                                        <input type="email" class="form-control" id="boletoEmail">
                                    </div>
                                    <div class="mb-3">
                                        <label for="boletoCPF" class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="boletoCPF" maxlength="14">
                                    </div>
                                </div>
                                <div id="mp-pix-form" style="display:none;">
                                    <div class="mb-3">
                                        <label for="pixName" class="form-label">Nome completo</label>
                                        <input type="text" class="form-control" id="pixName">
                                    </div>
                                    <div class="mb-3">
                                        <label for="pixEmail" class="form-label">E-mail</label>
                                        <input type="email" class="form-control" id="pixEmail">
                                    </div>
                                    <div class="mb-3">
                                        <label for="pixCPF" class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="pixCPF" maxlength="14">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <a href="frete.php" class="btn btn-outline-secondary">
                                <?= icon('arrow-left', 'icon me-2') ?>Voltar
                            </a>
                            <button type="submit" class="btn btn-custom flex-fill" id="pay-btn">
                                Pagar<?= icon('arrow-right', 'icon ms-2') ?>
                            </button>
                        </div>
                    </form>
                    <!-- Fim Checkout Transparente -->
                </div>
                
                <div class="col-lg-4">
                    <div class="card bg-dark text-light">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">Resumo do Pedido</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frete (<?= htmlspecialchars($_SESSION['checkout_frete']['opcao']) ?>):</span>
                                <strong>R$ <?= number_format($frete, 2, ',', '.') ?></strong>
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="fs-5">Total:</strong>
                                <strong class="fs-5 text-success">R$ <?= number_format($total, 2, ',', '.') ?></strong>
                            </div>
                            <div class="small text-muted">
                                <?= icon('shield', 'icon me-1') ?>
                                Ambiente de teste seguro
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
</script>
<!-- Mercado Pago JS SDK -->
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
let mp;
fetch('/checkout/mp-public-key.php')
    .then(r => r.json())
    .then(data => {
        if (data.public_key) {
            mp = new MercadoPago(data.public_key, {locale: 'pt-BR'});
            window.MercadoPagoObj = mp;
        }
    });
</script>
<script src="/assets/js/mp-checkout.js"></script>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>

