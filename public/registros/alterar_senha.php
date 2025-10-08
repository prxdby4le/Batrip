<?php
$pageTitle = 'Alterar Senha | Batrip';
include '../../includes/head.php';
include '../../includes/auth.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

// Requer login para alterar senha
require_login();

$success = '';
$error = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Token de segurança inválido. Tente novamente.";
    } else {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $nova_senha_confirm = $_POST['nova_senha_confirm'] ?? '';
        
        // Validações
        if (empty($senha_atual) || empty($nova_senha) || empty($nova_senha_confirm)) {
            $error = "Todos os campos são obrigatórios.";
        } elseif (strlen($nova_senha) < 6) {
            $error = "A nova senha deve ter pelo menos 6 caracteres.";
        } elseif ($nova_senha !== $nova_senha_confirm) {
            $error = "A confirmação da nova senha não confere.";
        } else {
            try {
                // Buscar senha atual do usuário
                $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if (!$user || !password_verify($senha_atual, $user['password'])) {
                    $error = "Senha atual incorreta.";
                } else {
                    // Atualizar senha
                    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    
                    if ($stmt->execute([$nova_senha_hash, $_SESSION['user_id']])) {
                        $success = "Senha alterada com sucesso!";
                        // Limpar campos
                        $_POST = [];
                    } else {
                        $error = "Erro ao alterar senha. Tente novamente.";
                    }
                }
            } catch (PDOException $e) {
                error_log("Erro ao alterar senha: " . $e->getMessage());
                $error = "Erro interno. Tente novamente.";
            }
        }
    }
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section">
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-6 col-lg-5 custom-form shadow">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Alterar Senha</h2>
        <a href="perfil_editar.php" class="btn btn-sm btn-outline-light">Voltar</a>
      </div>
      
      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible">
          <?= icon('check-circle', 'icon me-2') ?><?= htmlspecialchars($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible">
          <?= icon('exclamation-circle', 'icon me-2') ?><?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
        
        <div class="mb-3">
          <label class="form-label"><?= icon('lock', 'icon me-2') ?>Senha atual</label>
          <input type="password" name="senha_atual" class="form-control" required autocomplete="current-password">
        </div>
        <div class="mb-3">
          <label class="form-label"><?= icon('key', 'icon me-2') ?>Nova senha</label>
          <input type="password" name="nova_senha" class="form-control" required minlength="6" autocomplete="new-password">
          <div class="form-text">Mínimo de 6 caracteres</div>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= icon('key', 'icon me-2') ?>Confirme a nova senha</label>
          <input type="password" name="nova_senha_confirm" class="form-control" required minlength="6" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-custom w-100">
          <?= icon('save', 'icon me-2') ?>Salvar Nova Senha
        </button>
      </form>
    </div>
  </div>
</section>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
