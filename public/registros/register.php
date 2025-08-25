<?php
require_once __DIR__ . '/../../includes/auth.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    if ($name && $email && $password && $password === $password2 && $endereco && $cidade && $estado && $cep) {
        // Salvar dados extras no banco (ajustar função register e tabela users)
        if (register($name, $email, $password, $endereco, $cidade, $estado, $cep)) {
            header('Location: login.php');
            exit;
        } else {
            $msg = 'Erro ao registrar. E-mail já cadastrado?';
        }
    } else {
        $msg = 'Preencha todos os campos corretamente.';
    }
}
?>
<?php $pageTitle = 'Registrar | Batrip'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="/Batrip/materials/batrip symbol.png" type="image/x-icon">
    <link href="../assets/css/bootstrap-css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/Batrip/assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Registrar</h2>
            <form method="post" autocomplete="off">
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
    <script src="../assets/js/bootstrap-js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
    <script>
    // Auto-complete de endereço via ViaCEP
    document.getElementById('registerCep').addEventListener('blur', function() {
        var cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('registerEndereco').value = data.logradouro || '';
                        document.getElementById('registerCidade').value = data.localidade || '';
                        document.getElementById('registerEstado').value = data.uf || '';
                    }
                });
        }
    });
    </script>
</body>
</html>


