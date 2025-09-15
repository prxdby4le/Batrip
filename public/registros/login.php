<?php
$pageTitle = 'Login | Batrip';
include '../../includes/head.php';
include '../../includes/auth.php';

$error = '';
$success = '';

// Redirecionar se já estiver logado
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token de segurança inválido. Tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        
        if (empty($email) || empty($password)) {
            $error = 'Por favor, preencha email e senha.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido.';
        } else {
            if (login($email, $password)) {
                // Login bem-sucedido
                $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Email ou senha incorretos.';
            }
        }
    }
}
?>

<body>
    <?php include '../../includes/nav.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 col-lg-5 custom-form shadow">
            <h2 class="section-title mb-4"><i class="fas fa-sign-in-alt me-2"></i>Entrar</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           autocomplete="email">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock me-2"></i>Senha</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                    <div class="text-end mt-1">
                        <a href="redefinir-senha.php" class="footer-link">
                            <i class="fas fa-question-circle me-1"></i>Esqueceu a senha?
                        </a>
                    </div>
                </div>
                <button type="submit" class="btn btn-custom w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                </button>
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