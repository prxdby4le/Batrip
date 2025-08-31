<?php
$pageTitle = 'Login | Batrip';
include '../../includes/head.php';
require_once __DIR__ . '/../../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Sessão expirada. Tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            $error = 'Preencha email e senha.';
        } else {
            if (login($email, $password)) {
                $dest = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $dest);
                exit;
            } else {
                $error = 'Credenciais inválidas.';
            }
        }
    }
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-5" style="max-width: 520px;">

    <div class="hero-content">
        <h1 class="hero-title">Entrar</h1>
    </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" class="card card-body bg-dark text-light">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-custom w-100">Entrar</button>
    <p class="mt-3 mb-0">Não tem conta? <a href="register.php">Registrar</a></p>
  </form>
</main>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
<?php
require_once __DIR__ . '/../../includes/auth.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
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
}