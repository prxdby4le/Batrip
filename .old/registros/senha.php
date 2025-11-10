<?php
$pageTitle = 'Alterar Senha | Batrip';
include '../../includes/head.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Sessão expirada. Tente novamente.';
    } else {
        $senhaAtual = (string)($_POST['senha_atual'] ?? '');
        $nova = (string)($_POST['nova_senha'] ?? '');
        $conf = (string)($_POST['confirmar_senha'] ?? '');
        if ($nova === '' || strlen($nova) < 6) {
            $error = 'A nova senha deve ter ao menos 6 caracteres.';
        } elseif ($nova !== $conf) {
            $error = 'A confirmação não confere.';
        } else {
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($senhaAtual, $row['password'])) {
                $error = 'Senha atual incorreta.';
            } else {
                $hash = password_hash($nova, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $_SESSION['user_id']]);
                $msg = 'Senha alterada com sucesso.';
            }
        }
    }
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-5" style="max-width: 520px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Alterar Senha</h1>
    <a href="perfil.php" class="btn btn-sm btn-outline-light">Voltar</a>
  </div>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post" class="card card-body bg-dark text-light">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="mb-3">
      <label class="form-label">Senha Atual</label>
      <input type="password" name="senha_atual" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Nova Senha</label>
      <input type="password" name="nova_senha" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirmar Nova Senha</label>
      <input type="password" name="confirmar_senha" class="form-control" required>
    </div>
    <button class="btn btn-custom w-100">Salvar</button>
  </form>
</main>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
