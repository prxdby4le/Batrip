<?php
require_once __DIR__ . '/../../includes/auth.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        if (login($email, $password)) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/Batrip/public/index.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $msg = 'E-mail ou senha inválidos.';
        }
    } else {
        $msg = 'Preencha todos os campos.';
    }
}

$pageTitle = 'Login | Batrip';
include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Entrar</h2>
            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn btn-custom w-100">Entrar</button>
            </form>
            <div class="text-center mt-3">
                <?php if ($msg): ?><div class="alert alert-danger mt-2"><?php echo $msg; ?></div><?php endif; ?>
                <span>Não tem uma conta? <a href="register.php" class="footer-link">Cadastre-se</a></span>
                <span>Esqueceu a senha? <a href="redefinir-senha.php" class="footer-link">Clique aqui</a></span>
            </div>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-danger w-100" onclick="window.location.href='/adm/login-adm.php'">
                    <i class="fas fa-user-shield"></i> Área Administrativa
                </button>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

