<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - BATRIP</title>
    <link rel="icon" href="materials/batrip symbol.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <!-- Header padrão -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="materials/batrip png branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">

                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="perfil-adm.php" class="login-btn"><i class="fas fa-user-shield"></i> Admin</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="navbar-space"></div>
    <script>
        if(localStorage.getItem('batrip_admin') !== 'logado') {
            window.location.href = 'login-adm.php';
        }
    </script>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4 text-center">Login Administrativo</h2>
            <form id="adminLoginForm">
                <div class="mb-3">
                    <label for="adminUser" class="form-label">Usuário</label>
                    <input type="text" class="form-control" id="adminUser" placeholder="Usuário" required>
                </div>
                <div class="mb-3">
                    <label for="adminPass" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="adminPass" placeholder="Senha" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">Entrar</button>
                <div id="adminLoginError" class="text-danger mt-3" style="display:none;">Usuário ou senha inválidos.</div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const user = document.getElementById('adminUser').value;
            const pass = document.getElementById('adminPass').value;
            // Credenciais fixas
            if(user === 'pradasoueu' && pass === 'fragmentos') {
                localStorage.setItem('batrip_admin', 'logado');
                window.location.href = 'index-adm.php';
            } else {
                document.getElementById('adminLoginError').style.display = 'block';
            }
        });
    </script>
</body>
</html>