<?php
// DEBUG: Exibir todos os erros PHP na tela
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }


require_once __DIR__ . '/../../includes/auth.php';

// ...existing code...



require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// ...

// Verificar se endereço foi preenchido (não redireciona mais automaticamente para melhorar UX)
$missingAddress = !isset($_SESSION['checkout_endereco']);

// Verificar itens no carrinho (não redireciona mais automaticamente)
$cart = get_cart();
$cartEmpty = empty($cart);

// ...

// Redirecionamento seguro mesmo se cabeçalhos já tiverem sido enviados (ex.: BOM)
if (!function_exists('safe_redirect')) {
    function safe_redirect(string $url, int $code = 302): void {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $code);
            exit;
        }
        echo '<!doctype html><html><head>' .
             '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES) . '">' .
             '<script>location.replace(' . json_encode($url) . ')</script>' .
             '</head><body></body></html>';
        exit;
    }
}

require_once __DIR__ . '/../../includes/auth.php';
if (!is_logged_in()) {
    safe_redirect('/registros/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$pageTitle = 'Escolha o Frete | Batrip';
require_once __DIR__ . '/../../includes/cart-functions.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Base simples para links relativos a partir de /public/checkout/
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';


// Verificar se endereço foi preenchido (não redireciona mais automaticamente para melhorar UX)
$missingAddress = !isset($_SESSION['checkout_endereco']);

// Verificar itens no carrinho (não redireciona mais automaticamente)
$cart = get_cart();
$cartEmpty = empty($cart);

// DEBUG: Estado das variáveis críticas removido

// Processar seleção de frete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frete_opcao = $_POST['frete'] ?? 'SEDEX';
    $frete_valores = [
        'SEDEX' => ['preco' => 29.90, 'prazo' => '3 dias úteis'],
        'PAC' => ['preco' => 19.90, 'prazo' => '7 dias úteis'],
        'GRATIS' => ['preco' => 0.00, 'prazo' => '15 dias úteis']
    ];
    $_SESSION['checkout_frete'] = [
        'opcao' => $frete_opcao,
        'preco' => $frete_valores[$frete_opcao]['preco'],
        'prazo' => $frete_valores[$frete_opcao]['prazo']
    ];
    safe_redirect('pagamento.php');
}

$subtotal = get_cart_subtotal();
$frete_selecionado = $_SESSION['checkout_frete']['opcao'] ?? 'SEDEX';
$enderecoSess = $_SESSION['checkout_endereco'] ?? null;

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
                    <li class="breadcrumb-item active" aria-current="page">Frete</li>
                </ol>
            </nav>
            
            <h2 class="section-title mb-4"><?= icon('truck', 'icon') ?> Escolha o Frete</h2>

            <?php if ($missingAddress): ?>
            <div class="alert alert-warning d-flex align-items-start" role="alert">
                <div class="me-2"><?= icon('alert', 'icon') ?></div>
                <div>
                    Para escolher o frete, preencha seu endereço de entrega.
                    <a class="alert-link" href="endereco.php">Ir para Endereço</a>.
                </div>
            </div>
            <?php endif; ?>
            <?php if ($cartEmpty): ?>
            <div class="alert alert-warning d-flex align-items-start" role="alert">
                <div class="me-2"><?= icon('alert', 'icon') ?></div>
                <div>
                    Seu carrinho está vazio. Adicione produtos para calcular frete corretamente.
                    <a class="alert-link" href="<?= htmlspecialchars($base, ENT_QUOTES) ?>index.php">Ver produtos</a>.
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card bg-dark text-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Endereço de Entrega</h5>
                            <?php if ($enderecoSess): ?>
                                <p class="mb-1">
                                    <?= htmlspecialchars($enderecoSess['endereco'] ?? '') ?>, 
                                    <?= htmlspecialchars($enderecoSess['numero'] ?? '') ?>
                                    <?php if (!empty($enderecoSess['complemento'] ?? '')): ?>
                                        - <?= htmlspecialchars($enderecoSess['complemento']) ?>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1">
                                    <?= htmlspecialchars($enderecoSess['bairro'] ?? '') ?> - 
                                    <?= htmlspecialchars($enderecoSess['cidade'] ?? '') ?>/<?= htmlspecialchars($enderecoSess['uf'] ?? '') ?>
                                </p>
                                <p class="mb-0">CEP: <?= htmlspecialchars($enderecoSess['cep'] ?? '') ?></p>
                            <?php else: ?>
                                <p class="mb-0 text-muted">Endereço não definido.</p>
                            <?php endif; ?>
                            <a href="endereco.php" class="btn btn-sm btn-outline-light mt-2">
                                <?= icon('edit', 'icon me-1') ?>Alterar endereço
                            </a>
                        </div>
                    </div>
                    
                    <form method="POST" id="frete-form" class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Opções de Frete</label>
                            
                            <div class="card bg-secondary mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                         <input class="form-check-input" type="radio" name="frete" id="sedex" value="SEDEX" 
                             <?= ($missingAddress || $cartEmpty) ? 'disabled' : '' ?>
                                               <?= $frete_selecionado === 'SEDEX' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100 d-flex justify-content-between" for="sedex">
                                            <div>
                                                <strong>SEDEX</strong>
                                                <div class="small text-muted">Entrega em até 3 dias úteis</div>
                                            </div>
                                            <strong class="text-success">R$ 29,90</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card bg-secondary mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                         <input class="form-check-input" type="radio" name="frete" id="pac" value="PAC"
                             <?= ($missingAddress || $cartEmpty) ? 'disabled' : '' ?>
                                               <?= $frete_selecionado === 'PAC' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100 d-flex justify-content-between" for="pac">
                                            <div>
                                                <strong>PAC</strong>
                                                <div class="small text-muted">Entrega em até 7 dias úteis</div>
                                            </div>
                                            <strong class="text-success">R$ 19,90</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($subtotal >= 200): ?>
                            <div class="card bg-secondary mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                         <input class="form-check-input" type="radio" name="frete" id="gratis" value="GRATIS"
                             <?= ($missingAddress || $cartEmpty) ? 'disabled' : '' ?>
                                               <?= $frete_selecionado === 'GRATIS' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100 d-flex justify-content-between" for="gratis">
                                            <div>
                                                <strong>Frete Grátis</strong>
                                                <div class="small text-muted">Entrega em até 15 dias úteis</div>
                                                <span class="badge bg-success">Compras acima de R$ 200</span>
                                            </div>
                                            <strong class="text-success">GRÁTIS</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12 d-flex gap-2">
                            <a href="endereco.php" class="btn btn-outline-secondary">
                                <?= icon('arrow-left', 'icon me-2') ?>Voltar
                            </a>
                            <button type="submit" class="btn btn-custom flex-fill" <?= ($missingAddress || $cartEmpty) ? 'disabled' : '' ?>>
                                Continuar para Pagamento<?= icon('arrow-right', 'icon ms-2') ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="col-lg-4">
                    <div class="card bg-dark text-light">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">Resumo</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frete:</span>
                                <span class="text-muted">Selecione uma opção</span>
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-success">R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('frete-form');
    const sedexRadio = document.getElementById('sedex');
    const pacRadio = document.getElementById('pac');
    const sedexLabel = document.querySelector('label[for="sedex"] .text-success');
    const pacLabel = document.querySelector('label[for="pac"] .text-success');
    const resultado = document.createElement('div');
    resultado.id = 'resultado-frete';
    resultado.className = 'mb-3';
    if (form) {
        form.parentNode.insertBefore(resultado, form);
    }
    function atualizarFrete() {
        // Tenta obter o CEP do HTML (exibido no endereço de entrega)
        let cep = '';
        const cepElement = document.querySelector('p.mb-0');
        if (cepElement && cepElement.textContent.includes('CEP:')) {
            cep = cepElement.textContent.replace(/[^0-9]/g, '');
        }
        if (!cep) {
            resultado.innerHTML = `<div class='alert alert-danger'>CEP de entrega não encontrado. Preencha o endereço.</div>`;
            sedexLabel.textContent = 'Indisponível';
            pacLabel.textContent = 'Indisponível';
            return;
        }
        fetch(`/checkout/calcula-frete.php?cep=${cep}`)
            .then(r => r.json())
            .then(data => {
                if (data.SEDEX && !data.SEDEX.error && !data.SEDEX.erro) {
                    if (sedexLabel) sedexLabel.textContent = 'R$ ' + data.SEDEX.valor;
                } else if (sedexLabel) {
                    sedexLabel.textContent = 'Indisponível';
                }
                if (data.PAC && !data.PAC.error && !data.PAC.erro) {
                    if (pacLabel) pacLabel.textContent = 'R$ ' + data.PAC.valor;
                } else if (pacLabel) {
                    pacLabel.textContent = 'Indisponível';
                }
                if ((data.SEDEx && data.SEDEx.error) || (data.PAC && data.PAC.error)) {
                    resultado.innerHTML = `<div class='alert alert-danger'>${data.SEDEX?.error || data.PAC?.error}</div>`;
                } else if ((data.SEDEX && data.SEDEX.erro) || (data.PAC && data.PAC.erro)) {
                    resultado.innerHTML = `<div class='alert alert-danger'>${data.SEDEX?.erro || data.PAC?.erro}</div>`;
                } else {
                    resultado.innerHTML = '';
                }
            })
            .catch((e) => {
                resultado.innerHTML = `<div class='alert alert-danger'>Erro ao consultar frete</div>`;
            });
    }
    atualizarFrete();
});
</script>
</body>
</html>

