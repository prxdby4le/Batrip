<?php
// Página de registro unificada
$pageTitle = 'Registrar | Batrip';
require_once __DIR__ . '/../../includes/auth.php';

$error = '';
$msg = '';


// Preserva valores em caso de erro
$name = trim($_POST['name'] ?? '');
$display_name = trim($_POST['display_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$password2 = (string)($_POST['password2'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$estado = trim($_POST['estado'] ?? '');



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação mínima para funcionamento básico
    if ($name === '' || $display_name === '' || $email === '' || $password === '' || $password2 === '') {
        $error = 'Preencha todos os campos obrigatórios.';
    } elseif (!preg_match('/^[a-zA-Z0-9_\.]{3,32}$/', $display_name)) {
        $error = 'Nome de exibição deve ter entre 3 e 32 caracteres e usar apenas letras, números, _ ou ponto.';
    } elseif ($password !== $password2) {
        $error = 'As senhas não coincidem.';
    } else {
        try {
            // Verifica se display_name já existe
            require_once __DIR__ . '/../../includes/db.php';
            $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE display_name = ?');
            $stmtCheck->execute([$display_name]);
            if ($stmtCheck->fetch()) {
                $error = 'Nome de exibição já está em uso.';
            } else {
                if (register($name, strtolower($email), $password, '', '', '', '', $display_name)) {
                    header('Location: login.php');
                    exit;
                } else {
                    $error = 'Não foi possível registrar. E-mail já cadastrado?';
                }
            }
        } catch (Throwable $e) {
            error_log('Register error: ' . $e->getMessage());
            $error = 'Erro ao registrar. Tente novamente mais tarde.';
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
                <div class="mb-3">
                    <label for="registerName" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="registerName" name="name" placeholder="Digite seu nome" required value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerDisplayName" class="form-label">Nome de exibição <span class="text-muted">(@)</span></label>
                    <input type="text" class="form-control" id="registerDisplayName" name="display_name" placeholder="Escolha seu @" required pattern="[a-zA-Z0-9_\.]{3,32}" title="Entre 3 e 32 caracteres. Letras, números, underline ou ponto." value="<?php echo htmlspecialchars($display_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Digite seu e-mail" required value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Digite sua senha" required>
                </div>
                <div class="mb-3">
                    <label for="registerPassword2" class="form-label">Repita a Senha</label>
                    <input type="password" class="form-control" id="registerPassword2" name="password2" placeholder="Repita sua senha" required>
                </div>
                <div class="mb-3">
                    <label for="registerCep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="registerCep" name="cep" placeholder="Digite seu CEP" required value="<?php echo htmlspecialchars($cep, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerEndereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="registerEndereco" name="endereco" placeholder="Digite seu endereço" required value="<?php echo htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerCidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="registerCidade" name="cidade" placeholder="Digite sua cidade" required value="<?php echo htmlspecialchars($cidade, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="registerEstado" class="form-label">UF</label>
                    <input type="text" class="form-control" id="registerEstado" name="estado" placeholder="UF" maxlength="2" required value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <button type="submit" class="btn btn-primary w-100">Registrar</button>
            </form>
            <div class="text-center mt-3">
                <a href="registros/login.php" class="footer-link">Já tem uma conta? Entrar</a>
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


