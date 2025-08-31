<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
require_once __DIR__ . '/../../includes/cart-functions.php';

// Redireciona se carrinho vazio
if (empty(get_cart())) {
    header('Location: carrinho.php');
    exit;
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        die('CSRF inválido');
    }
    $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
    $_SESSION['checkout_address'] = [
        'cep' => $cep,
        'endereco' => trim($_POST['endereco'] ?? ''),
        'numero' => trim($_POST['numero'] ?? ''),
        'bairro' => trim($_POST['bairro'] ?? ''),
        'cidade' => trim($_POST['cidade'] ?? ''),
        'uf' => strtoupper(trim($_POST['uf'] ?? '')),
        'complemento' => trim($_POST['complemento'] ?? ''),
        'comentario' => trim($_POST['comentario'] ?? ''),
    ];
    if ($cep) {
        set_user_cep($cep);
    }
    header('Location: frete.php');
    exit;
}

$pageTitle = 'Endereço de Entrega | Batrip';
$addr = $_SESSION['checkout_address'] ?? [];
include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h2>
            <form method="post" class="row g-3" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="col-md-8">
                    <label for="cep" class="form-label">CEP</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="cep" name="cep" maxlength="9" required placeholder="00000-000" value="<?php echo isset($addr['cep']) ? htmlspecialchars($addr['cep']) : ''; ?>">
                        <button class="btn btn-outline-secondary" type="button" id="buscar-cep">Buscar</button>
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" required value="<?php echo htmlspecialchars($addr['endereco'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" required value="<?php echo htmlspecialchars($addr['numero'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" required value="<?php echo htmlspecialchars($addr['bairro'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" required value="<?php echo htmlspecialchars($addr['cidade'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="uf" class="form-label">UF</label>
                    <input type="text" class="form-control" id="uf" name="uf" maxlength="2" required value="<?php echo htmlspecialchars($addr['uf'] ?? ''); ?>">
                </div>
                <div class="col-md-8">
                    <label for="complemento" class="form-label">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento" value="<?php echo htmlspecialchars($addr['complemento'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label for="comentario" class="form-label">Comentário para entrega</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="2" placeholder="Ex: Deixar na portaria, tocar campainha, etc."><?php echo htmlspecialchars($addr['comentario'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-custom w-100">Continuar para Frete</button>
                </div>
            </form>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
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
</body>
</html>

