<?php
/**
 * View: Checkout/Shipping
 * Escolha do frete
 */

require_once ROOT_PATH . '/includes/icon-helper.php';

$endereco = $endereco ?? [];
$subtotal = $subtotal ?? 0;
$frete_selecionado = $frete_selecionado ?? '';
$frete_valor = $frete_valor ?? 0.0;
?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/carrinho">Carrinho</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/endereco">Endereço</a></li>
                <li class="breadcrumb-item active" aria-current="page">Frete</li>
                <li class="breadcrumb-item">Pagamento</li>
                <li class="breadcrumb-item">Revisão</li>
            </ol>
        </nav>
        
        <h2 class="section-title mb-4"><?= icon('truck', 'icon') ?> Escolha o Frete</h2>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card bg-dark text-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Endereço de Entrega</h5>
                        <?php if ($endereco): ?>
                            <p class="mb-1">
                                <?= htmlspecialchars($endereco['endereco'] ?? '') ?>, 
                                <?= htmlspecialchars($endereco['numero'] ?? '') ?>
                                <?php if (!empty($endereco['complemento'] ?? '')): ?>
                                    - <?= htmlspecialchars($endereco['complemento']) ?>
                                <?php endif; ?>
                            </p>
                            <p class="mb-1">
                                <?= htmlspecialchars($endereco['bairro'] ?? '') ?> - 
                                <?= htmlspecialchars($endereco['cidade'] ?? '') ?>/<?= htmlspecialchars($endereco['uf'] ?? '') ?>
                            </p>
                            <p class="mb-0">CEP: <?= htmlspecialchars($endereco['cep'] ?? '') ?></p>
                        <?php else: ?>
                            <p class="mb-0 text-muted">Endereço não definido.</p>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>checkout/endereco" class="btn btn-sm btn-outline-light mt-2">
                            <?= icon('edit', 'icon me-1') ?>Alterar endereço
                        </a>
                    </div>
                </div>
                
                <form method="POST" action="<?= BASE_URL ?>checkout/frete" id="frete-form" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="frete_valor" id="frete_valor_hidden" value="0">
                    <input type="hidden" name="frete_prazo" id="frete_prazo_hidden" value="">
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">Opções de Frete</label>
                        <div id="opcoes-frete-dinamicas"></div>
                    </div>
                    
                    <div class="col-12 d-flex gap-2">
                        <a href="<?= BASE_URL ?>checkout/endereco" class="btn btn-outline-secondary">
                            <?= icon('arrow-left', 'icon me-2') ?>Voltar
                        </a>
                        <button type="submit" class="btn btn-custom flex-fill" id="btn-continuar-frete" disabled>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
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
        if (btnContinuar) btnContinuar.disabled = true;
        
        let cep = '';
        const cepElement = document.querySelector('p.mb-0');
        if (cepElement && cepElement.textContent.includes('CEP:')) {
            cep = cepElement.textContent.replace(/[^0-9]/g, '');
        }
        
        if (!cep) {
            resultado.innerHTML = `<div class='alert alert-danger'>CEP de entrega não encontrado. Preencha o endereço.</div>`;
            return;
        }
        
        fetch('<?= BASE_URL ?>checkout/calcula-frete?cep=' + cep)
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                }
                return r.json();
            })
            .then(data => {
                console.log('Resposta do frete:', data); // Debug
                
                // Verifica se há erro na resposta
                if (data.error) {
                    const errorMsg = data.error || 'Nenhum frete disponível para o CEP informado.';
                    let debugInfo = '';
                    
                    // Adicionar informações de debug se disponíveis
                    if (data.http_code) {
                        debugInfo += `<br><small class='text-muted'>Código HTTP: ${data.http_code}</small>`;
                    }
                    
                    if (data.debug) {
                        if (data.debug.cep_destino !== undefined) {
                            debugInfo += `<br><small class='text-muted'>CEP destino: ${data.debug.cep_destino} (${data.debug.cep_destino_length || 'N/A'} dígitos)</small>`;
                        }
                        if (data.debug.cep_origem !== undefined) {
                            debugInfo += `<br><small class='text-muted'>CEP origem: ${data.debug.cep_origem} (${data.debug.cep_origem_length || 'N/A'} dígitos)</small>`;
                        }
                        if (data.debug.cep_destino_is_numeric !== undefined) {
                            debugInfo += `<br><small class='text-muted'>CEP destino é numérico: ${data.debug.cep_destino_is_numeric ? 'Sim' : 'Não'}</small>`;
                        }
                        if (data.debug.cep_origem_valid !== undefined) {
                            debugInfo += `<br><small class='text-muted'>CEP origem válido: ${data.debug.cep_origem_valid ? 'Sim' : 'Não'}</small>`;
                        }
                    }
                    
                    // Se for erro HTTP 400, mostrar mais detalhes
                    if (data.http_code === 400 && data.response) {
                        let responseInfo = '';
                        if (data.response.message) {
                            responseInfo += `<br><small class='text-danger'>Detalhes: ${data.response.message}</small>`;
                        }
                        if (data.response.errors && Array.isArray(data.response.errors)) {
                            responseInfo += `<br><small class='text-danger'>Erros: ${data.response.errors.join(', ')}</small>`;
                        }
                        debugInfo += responseInfo;
                    }
                    
                    const alertType = data.http_code === 400 ? 'alert-danger' : 'alert-warning';
                    resultado.innerHTML = `<div class='alert ${alertType}'>
                        <strong>Atenção:</strong> ${errorMsg}
                        ${debugInfo}
                        ${data.simulado ? `<br><small class='text-info'>Frete simulado para desenvolvimento</small>` : ''}
                    </div>`;
                    return;
                }
                
                const result = data.result || {};
                const opcoesDiv = document.getElementById('opcoes-frete-dinamicas');
                if (opcoesDiv) opcoesDiv.innerHTML = '';
                
                let algumDisponivel = false;
                Object.entries(result).forEach(([nome, servico], idx) => {
                    if (!servico || servico.error || servico.erro || (!servico.valor && !servico.price)) {
                        console.log('Serviço ignorado:', nome, servico);
                        return;
                    }
                    algumDisponivel = true;
                    
                    const valor = servico.valor || servico.price || '0.00';
                    const prazo = servico.prazo || servico.delivery_time || '?';
                    
                    const id = 'frete-din-' + idx;
                    const card = document.createElement('div');
                    card.className = 'card bg-secondary mb-2';
                    card.innerHTML = `
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="frete" id="${id}" value="${nome}" data-frete-valor="${valor}" data-frete-prazo="${prazo}">
                                <label class="form-check-label w-100 d-flex justify-content-between" for="${id}">
                                    <div>
                                        <strong>${nome}</strong>
                                        <div class="small text-muted">Entrega em até ${prazo} dias úteis</div>
                                    </div>
                                    <strong class="text-success">R$ ${parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                </label>
                            </div>
                        </div>
                    `;
                    if (opcoesDiv) opcoesDiv.appendChild(card);
                });
                
                // Rebind eventos de mudança (pode ter elementos novos)
                opcoesDiv.addEventListener('change', function(e) {
                    if (e.target && e.target.matches('input[name="frete"]')) {
                        document.getElementById('frete_valor_hidden').value = e.target.getAttribute('data-frete-valor') || '0';
                        document.getElementById('frete_prazo_hidden').value = e.target.getAttribute('data-frete-prazo') || '';
                        if (btnContinuar) btnContinuar.disabled = false;
                    }
                });
                
                if (!algumDisponivel) {
                    resultado.innerHTML = `<div class='alert alert-warning'>
                        <strong>Atenção:</strong> Nenhuma opção de frete disponível para o CEP informado.
                        <br><small class='text-muted'>Por favor, verifique o CEP ou entre em contato com o suporte.</small>
                    </div>`;
                } else {
                    resultado.innerHTML = '';
                }
            })
            .catch((e) => {
                console.error('Erro ao consultar frete:', e);
                resultado.innerHTML = `<div class='alert alert-danger'>
                    <strong>Erro:</strong> Não foi possível calcular o frete. Por favor, tente novamente.
                    <br><small class='text-muted'>${e.message || 'Erro desconhecido'}</small>
                </div>`;
            });
    }
    
    atualizarFrete();
});
</script>
