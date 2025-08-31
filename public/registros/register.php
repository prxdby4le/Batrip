<?php
$pageTitle = 'Registrar | Batrip';
include '../../includes/head.php';
require_once __DIR__ . '/../../includes/auth.php';

$msg = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Sessão expirada. Tente novamente.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        if ($name === '' || $email === '' || $password === '') {
            $error = 'Preencha todos os campos.';
        } else {
            if (register($name, $email, $password)) {
                $msg = 'Cadastro realizado. Faça login.';
            } else {
                $error = 'Não foi possível registrar. Email pode já estar em uso.';
            }
        }
    }
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-5" style="max-width: 520px;">
  <h1 class="h4 mb-4">Registrar</h1>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post" class="card card-body bg-dark text-light">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-custom w-100">Cadastrar</button>
    <p class="mt-3 mb-0">Já tem conta? <a href="login.php">Entrar</a></p>
  </form>
</main>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
<?php
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../includes/auth.php';
    $csrf_error = '';
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $csrf_error = 'Sessão expirada. Atualize a página e tente novamente.';
    }
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    if (!$csrf_error && $name && $email && $password && $password === $password2 && $endereco && $cidade && $estado && $cep) {
        // Salvar dados extras no banco (ajustar função register e tabela users)
        if (register($name, $email, $password, $endereco, $cidade, $estado, $cep)) {
            header('Location: login.php');
            exit;
        } else {
            $msg = 'Erro ao registrar. E-mail já cadastrado?';
        }
    } else {
        $msg = $csrf_error ?: 'Preencha todos os campos corretamente.';
    }
}
?>
<?php $pageTitle = 'Registrar | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Registrar</h2>
            <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label for="registerName" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="registerName" name="name" placeholder="Digite seu nome" required>
                </div>
                <div class="mb-3">
                    <label for="registerEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="mb-3">
                    <label for="registerPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Digite sua senha" required>
                </div>
                <div class="mb-3">
                    <label for="registerPasswordConfirm" class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" id="registerPasswordConfirm" name="password2" placeholder="Confirme sua senha" required>
                </div>
                <div class="mb-3">
                    <label for="registerCep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="registerCep" name="cep" placeholder="Digite seu CEP" required maxlength="9">
                </div>
                <div class="mb-3">
                    <label for="registerEndereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="registerEndereco" name="endereco" placeholder="Rua, número, complemento" required>
                </div>
                <div class="mb-3">
                    <label for="registerCidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="registerCidade" name="cidade" placeholder="Cidade" required>
                </div>
                <div class="mb-3">
                    <label for="registerEstado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="registerEstado" name="estado" placeholder="Estado" required maxlength="2">
                </div>
                <button type="submit" class="btn btn-custom w-100">Registrar</button>
            </form>
            <div class="text-center mt-3">
                <?php if ($msg): ?><div class="alert alert-danger mt-2"><?php echo $msg; ?></div><?php endif; ?>
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
            if (!cepInput) return;
            cepInput.addEventListener('blur', function() {
                var cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch('https://viacep.com.br/ws/' + cep + '/json/')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                const e = document.getElementById('registerEndereco');
                                const c = document.getElementById('registerCidade');
                                const u = document.getElementById('registerEstado');
                                if (e) e.value = data.logradouro || '';
                                if (c) c.value = data.localidade || '';
                                if (u) u.value = data.uf || '';
                            }
                        });
                }
            });
        })();
        </script>
</body>
</html>


