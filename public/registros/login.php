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
    
    <?php
    $formTitle = 'Entrar';
    $submitText = 'Entrar';
    $showRegisterLink = true;
    $showForgotPassword = true;
    
    $formContent = '
        <div class="mb-3">
            <label for="loginEmail" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Digite seu e-mail" required>
        </div>
        <div class="mb-3">
            <label for="loginPassword" class="form-label">Senha</label>
            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Digite sua senha" required>
        </div>
    ';
    
    include '../../includes/auth-form.php';
    ?>
    
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
</html>
