<?php
/**
 * View: Checkout/Address
 * Formulário de endereço de entrega
 */

require_once ROOT_PATH . '/includes/icon-helper.php';

$endereco = $endereco ?? [];
?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>checkout/carrinho">Carrinho</a></li>
                <li class="breadcrumb-item active" aria-current="page">Endereço</li>
                <li class="breadcrumb-item">Frete</li>
                <li class="breadcrumb-item">Pagamento</li>
                <li class="breadcrumb-item">Revisão</li>
            </ol>
        </nav>
        
        <h2 class="section-title mb-4"><?= icon('map-marker', 'icon') ?> Endereço de Entrega</h2>
        
        <form method="POST" action="<?= BASE_URL ?>checkout/endereco" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <div class="col-md-8">
                <label for="cep" class="form-label">CEP *</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="cep" name="cep" maxlength="9" required 
                           placeholder="00000-000" autocomplete="postal-code"
                           value="<?= htmlspecialchars($endereco['cep'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="button" id="buscar-cep">
                        <?= icon('search', 'icon') ?> Buscar
                    </button>
                </div>
                <small class="text-white">Digite o CEP e clique em Buscar</small>
            </div>
            
            <div class="col-md-8">
                <label for="endereco" class="form-label">Endereço *</label>
                <input type="text" class="form-control" id="endereco" name="endereco" required
                       autocomplete="street-address"
                       value="<?= htmlspecialchars($endereco['endereco'] ?? '') ?>">
            </div>
            
            <div class="col-md-4">
                <label for="numero" class="form-label">Número *</label>
                <input type="text" class="form-control" id="numero" name="numero" required
                       autocomplete="address-line2"
                       value="<?= htmlspecialchars($endereco['numero'] ?? '') ?>">
            </div>
            
            <div class="col-md-6">
                <label for="bairro" class="form-label">Bairro *</label>
                <input type="text" class="form-control" id="bairro" name="bairro" required
                       autocomplete="address-line3"
                       value="<?= htmlspecialchars($endereco['bairro'] ?? '') ?>">
            </div>
            
            <div class="col-md-6">
                <label for="cidade" class="form-label">Cidade *</label>
                <input type="text" class="form-control" id="cidade" name="cidade" required
                       autocomplete="address-level2"
                       value="<?= htmlspecialchars($endereco['cidade'] ?? '') ?>">
            </div>
            
            <div class="col-md-4">
                <label for="uf" class="form-label">UF *</label>
                <input type="text" class="form-control text-uppercase" id="uf" name="uf" maxlength="2" required
                       autocomplete="address-level1"
                       value="<?= htmlspecialchars($endereco['uf'] ?? '') ?>">
            </div>
            
            <div class="col-md-8">
                <label for="complemento" class="form-label">Complemento</label>
                <input type="text" class="form-control" id="complemento" name="complemento"
                       placeholder="Apto, bloco, etc."
                       autocomplete="address-line2"
                       value="<?= htmlspecialchars($endereco['complemento'] ?? '') ?>">
            </div>
            
            <div class="col-12">
                <label for="comentario" class="form-label">Comentário para entrega</label>
                <textarea class="form-control" id="comentario" name="comentario" rows="2" 
                          placeholder="Ex: Deixar na portaria, tocar campainha, etc."><?= htmlspecialchars($endereco['comentario'] ?? '') ?></textarea>
            </div>
            
            <div class="col-12 d-flex gap-2">
                <a href="<?= BASE_URL ?>checkout/carrinho" class="btn btn-outline-secondary">
                    <?= icon('arrow-left', 'icon me-2') ?>Voltar ao Carrinho
                </a>
                <button type="submit" class="btn btn-custom flex-fill">
                    Continuar para Frete<?= icon('arrow-right', 'icon ms-2') ?>
                </button>
            </div>
        </form>
    </div>
</section>

<script>
// Busca de CEP via ViaCEP
(function(){
    const btn = document.getElementById('buscar-cep');
    if (!btn) return;
    btn.addEventListener('click', function(){
        var cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('endereco').value = data.logradouro || '';
                        document.getElementById('bairro').value = data.bairro || '';
                        document.getElementById('cidade').value = data.localidade || '';
                        document.getElementById('uf').value = data.uf || '';
                    } else {
                        alert('CEP não encontrado.');
                    }
                })
                .catch(() => alert('Falha ao consultar CEP.'));
        } else {
            alert('Digite um CEP válido.');
        }
    });
})();
</script>

