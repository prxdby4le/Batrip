<?php
$pageTitle = 'Login | Batrip';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $error = 'Preencha email e senha.';
    } else {
        $error = 'Login simulado: navegação liberada.';
    }
}
include '../../includes/head.php';
?>

<body>
    <?php include '../../includes/nav.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 col-lg-5 custom-form shadow">
            <h2 class="section-title mb-4">Entrar</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="text-end mt-1">
                        <a href="/registros/redefinir-senha.php" class="footer-link">Esqueceu a senha?</a>
                    </div>
                </div>
                <button type="submit" class="btn btn-custom w-100">Entrar</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="footer-link">Não tem conta? Registrar</a>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>

</html>