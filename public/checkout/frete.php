<?php
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
    $frete_opcao = $_POST['frete'] ?? '';
    $frete_preco = 0.0;
    $frete_prazo = '';
    // Busca valor e prazo do frete selecionado na resposta da API (armazenada em campo oculto)
    if (isset($_POST['frete_valor']) && is_numeric($_POST['frete_valor'])) {
        $frete_preco = (float)$_POST['frete_valor'];
    }
    if (isset($_POST['frete_prazo'])) {
        $frete_prazo = $_POST['frete_prazo'];
    }
    $_SESSION['checkout_frete'] = [
        'opcao' => $frete_opcao,
        'preco' => $frete_preco,
        'prazo' => $frete_prazo
    ];
    safe_redirect('pagamento.php');
}

$subtotal = get_cart_subtotal();
$frete_selecionado = $_SESSION['checkout_frete']['opcao'] ?? '';
$frete_valor = isset($_SESSION['checkout_frete']['preco']) ? (float)$_SESSION['checkout_frete']['preco'] : 0.0;
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
                    <li class="breadcrumb-item">Pagamento</li>
                    <li class="breadcrumb-item">Finalizar</li>
                    <li class="breadcrumb-item">Sucesso</li>
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
                        <input type="hidden" name="frete_valor" id="frete_valor_hidden" value="0">
                        <input type="hidden" name="frete_prazo" id="frete_prazo_hidden" value="">
                        <div class="col-12">
                            <label class="form-label fw-bold">Opções de Frete</label>
                            <div id="opcoes-frete-dinamicas"></div>
                        </div>
                        
                        <div class="col-12 d-flex gap-2">
                            <a href="endereco.php" class="btn btn-outline-secondary">
                                <?= icon('arrow-left', 'icon me-2') ?>Voltar
                            </a>
                            <button type="submit" class="btn btn-custom flex-fill" id="btn-continuar-frete" <?= ($missingAddress || $cartEmpty) ? 'disabled' : '' ?> >
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
                                <span>Frete<?= $frete_selecionado ? ' (' . htmlspecialchars($frete_selecionado) . ')' : '' ?>:</span>
                                <strong>R$ <?= number_format($frete_valor, 2, ',', '.') ?></strong>
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-success">R$ <?= number_format($subtotal + $frete_valor, 2, ',', '.') ?></strong>
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
        // Desabilita o botão até selecionar um frete
        const btnContinuar = document.getElementById('btn-continuar-frete');
        if (btnContinuar) btnContinuar.disabled = true;

    const form = document.getElementById('frete-form');
    const resultado = document.createElement('div');
    resultado.id = 'resultado-frete';
    resultado.className = 'mb-3';
    if (form) {
        form.parentNode.insertBefore(resultado, form);
    }
    function atualizarFrete() {
            // Sempre desabilita o botão ao atualizar opções
            if (btnContinuar) btnContinuar.disabled = true;
        // Tenta obter o CEP do HTML (exibido no endereço de entrega)
        let cep = '';
        const cepElement = document.querySelector('p.mb-0');
        if (cepElement && cepElement.textContent.includes('CEP:')) {
            cep = cepElement.textContent.replace(/[^0-9]/g, '');
        }
        if (!cep) {
            resultado.innerHTML = `<div class='alert alert-danger'>CEP de entrega não encontrado. Preencha o endereço.</div>`;
            return;
        }
        fetch(`/checkout/calcula-frete.php?cep=${cep}`)
            .then(r => r.json())
            .then(data => {
                const result = data.result || {};
                // Limpa opções antigas
                const opcoesDiv = document.getElementById('opcoes-frete-dinamicas');
                if (opcoesDiv) opcoesDiv.innerHTML = '';
                let algumDisponivel = false;
                Object.entries(result).forEach(([nome, servico], idx) => {
                    if (!servico || servico.error || servico.erro || !servico.valor) return;
                    algumDisponivel = true;
                    // Cria radio dinamicamente
                    const id = 'frete-din-' + idx;
                    const card = document.createElement('div');
                    card.className = 'card bg-secondary mb-2';
                    card.innerHTML = `
                        <div class=\"card-body\">
                            <div class=\"form-check\">
                                <input class=\"form-check-input\" type=\"radio\" name=\"frete\" id=\"${id}\" value=\"${nome}\" data-frete-valor=\"${servico.valor || servico.price}\" data-frete-prazo=\"${servico.prazo || servico.delivery_time || ''}\">
                                <label class=\"form-check-label w-100 d-flex justify-content-between\" for=\"${id}\">
                                    <div>
                                        <strong>${nome}</strong>
                                        <div class=\"small text-muted\">Entrega em até ${servico.prazo || servico.delivery_time || '?'} dias úteis</div>
                                    </div>
                                    <strong class=\"text-success\">R$ ${servico.valor || servico.price}</strong>
                                </label>
                            </div>
                        </div>
                    `;
                    if (opcoesDiv) opcoesDiv.appendChild(card);
                });
                // Habilita o botão só quando um frete for selecionado
                opcoesDiv.addEventListener('change', function(e) {
                    if (e.target && e.target.matches('input[name="frete"]')) {
                        document.getElementById('frete_valor_hidden').value = e.target.getAttribute('data-frete-valor') || '0';
                        document.getElementById('frete_prazo_hidden').value = e.target.getAttribute('data-frete-prazo') || '';
                        if (btnContinuar) btnContinuar.disabled = false;
                    }
                });
                // Atualiza o valor do frete oculto ao selecionar
                opcoesDiv.addEventListener('change', function(e) {
                    if (e.target && e.target.matches('input[name="frete"]')) {
                        document.getElementById('frete_valor_hidden').value = e.target.getAttribute('data-frete-valor') || '0';
                        document.getElementById('frete_prazo_hidden').value = e.target.getAttribute('data-frete-prazo') || '';
                    }
                });
                if (!algumDisponivel) {
                    let debugInfo = '';
                    if (data && data.debug) {
                        debugInfo = `<details class='mt-2'><summary>Debug SuperFrete</summary><pre style='font-size:12px;white-space:pre-wrap;'>${JSON.stringify(data, null, 2)}</pre></details>`;
                    }
                    resultado.innerHTML = `<div class='alert alert-danger'>Nenhum frete disponível para o CEP informado.</div>` + debugInfo;
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

