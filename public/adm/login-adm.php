<?php
// Inicia buffer cedo para evitar avisos de headers no redirecionamento de login
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }

$pageTitle = 'Login Admin | Batrip';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// Se já está logado e é admin, redireciona para o painel
if (is_logged_in() && is_admin()) {
    header('Location: index-adm.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, name, email, password, is_admin FROM users WHERE email = ? AND is_admin = 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];
                
                header('Location: index-adm.php');
                exit;
            } else {
                $error = 'Credenciais inválidas ou usuário não é administrador.';
            }
        } catch (PDOException $e) {
            $error = 'Erro interno. Tente novamente.';
            error_log("Erro de login admin: " . $e->getMessage());
        }
    }
}

include '../../includes/head.php';
?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Área Administrativa</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" id="email" 
                           placeholder="Digite seu e-mail de administrador" 
                           value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" name="password" id="password" 
                           placeholder="Digite sua senha" required>
                </div>
                
                <button type="submit" class="btn btn-custom w-100">Entrar</button>
            </form>
            
            <div class="mt-3 text-center">
                <small class="text-muted">
                    Apenas administradores podem acessar esta área.<br>
                    <strong>Admin padrão:</strong> admin@batrip.com / admin123
                </small>
            </div>
        </div>
    </div>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>

