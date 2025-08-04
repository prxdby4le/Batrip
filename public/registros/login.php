<?php $pageTitle = 'Login | Batrip'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="/assets/materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Entrar</h2>
            <form>
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="loginEmail" placeholder="Digite seu e-mail">
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="loginPassword" placeholder="Digite sua senha">
                </div>
                <button type="submit" class="btn btn-custom w-100">Entrar</button>
            </form>
            <div class="text-center mt-3">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
