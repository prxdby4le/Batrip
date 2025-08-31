<?php
// Página de registro unificada
$pageTitle = 'Registrar | Batrip';
require_once __DIR__ . '/../../includes/auth.php';

$error = '';
$msg = '';

// Preserva valores em caso de erro
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$password2 = (string)($_POST['password2'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$estado = trim($_POST['estado'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        if ($name === '' || $email === '' || $password === '' || $password2 === '' || $cep === '' || $endereco === '' || $cidade === '' || $estado === '') {
            $error = 'Preencha todos os campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'E-mail inválido.';
        } elseif ($password !== $password2) {
            $error = 'As senhas não coincidem.';
        } elseif (strlen($password) < 6) {
            $error = 'A senha deve ter pelo menos 6 caracteres.';
        } else {
            $estado = strtoupper(substr($estado, 0, 2));
            $cepDigits = preg_replace('/\D/', '', $cep);
            if (strlen($estado) !== 2) {
                $error = 'Informe a UF com 2 letras (ex.: SP).';
            } elseif (strlen($cepDigits) !== 8) {
                $error = 'CEP deve ter 8 dígitos.';
            } else {
                try {
                    if (register($name, strtolower($email), $password, $endereco, $cidade, $estado, $cepDigits)) {
                        header('Location: login.php?registered=1');
                        exit;
                    } else {
                        $error = 'Não foi possível registrar. E-mail já cadastrado?';
                    }
                } catch (Throwable $e) {
                    error_log('Register error: ' . $e->getMessage());
                    $error = 'Erro ao registrar. Tente novamente mais tarde.';
                }
            }
        }
    }
}
?>
<?php include '../../includes/head.php'; ?>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 col-lg-5 custom-form shadow">
            <h2 class="section-title mb-4">Registrar</h2>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label for="registerName" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="registerName" name="name" placeholder="Digite seu nome" required value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Digite seu e-mail" required value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="registerPasswordConfirm" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="registerPasswordConfirm" name="password2" placeholder="Repita a senha" required>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="registerCep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="registerCep" name="cep" placeholder="00000-000" required maxlength="9" value="<?php echo htmlspecialchars($cep, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="registerEndereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="registerEndereco" name="endereco" placeholder="Rua, número, complemento" required value="<?php echo htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="registerCidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="registerCidade" name="cidade" placeholder="Cidade" required value="<?php echo htmlspecialchars($cidade, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="registerEstado" class="form-label">UF</label>
                            <input type="text" class="form-control" id="registerEstado" name="estado" placeholder="SP" required maxlength="2" value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-custom w-100">Registrar</button>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="footer-link">Já tem uma conta? Entrar</a>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
    <script>
    // Auto-complete de endereço via ViaCEP
    (function(){
        const cepInput = document.getElementById('registerCep');
        const enderecoInput = document.getElementById('registerEndereco');
        const cidadeInput = document.getElementById('registerCidade');
        const estadoInput = document.getElementById('registerEstado');
        if (!cepInput) return;
        function fillFromCep(cep){
            const only = (cep || '').replace(/\D/g, '');
            if (only.length !== 8) return;
            fetch('https://viacep.com.br/ws/' + only + '/json/')
                .then(r => r.json())
                .then(data => {
                    if (data && !data.erro) {
                        if (enderecoInput && !enderecoInput.value) enderecoInput.value = (data.logradouro || '');
                        if (cidadeInput && !cidadeInput.value) cidadeInput.value = (data.localidade || '');
                        if (estadoInput && !estadoInput.value) estadoInput.value = (data.uf || '');
                    }
                })
                .catch(()=>{});
        }
        cepInput.addEventListener('blur', function(){ fillFromCep(this.value); });
    })();
    </script>
</body>
</html>


