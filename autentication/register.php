<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - BATRIP</title>
    <link rel="icon" href="materials/batrip symbol.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="materials/batrip png branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
            </a>
        </div>
    </nav>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Cadastro</h2>
            <form>
                <div class="mb-3">
                    <label for="registerName" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="registerName" placeholder="Digite seu nome">
                </div>
                <div class="mb-3">
                    <label for="registerEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="registerEmail" placeholder="Digite seu e-mail">
                </div>
                <div class="mb-3">
                    <label for="registerPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="registerPassword" placeholder="Crie uma senha">
                </div>
                <div class="mb-3">
                    <label for="registerConfirmPassword" class="form-label">Confirme sua senha</label>
                    <input type="password" class="form-control" id="registerConfirmPassword" placeholder="Confirme sua senha">
                </div>
                <div class="mb-3">
                    <label for="registerAdress" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="registerAdress" placeholder="Digite seu Endereço">
                </div>
                <div class="mb-3">
                    <label for="registerNumHouse" class="form-label">Número da casa</label>
                    <input type="text" class="form-control" id="registerNumHouse" placeholder="Número da sua casa">
                </div>
                <div class="mb-3">
                    <label for="registerComp" class="form-label">Complemento</label>
                    <input type="text" class="form-control" id="registerComp" placeholder="Complemento">
                </div>
                <div class="mb-3">
                    <label for="registerState" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="registerState" placeholder="Digite seu estado">
                </div>
                <div class="mb-3">
                    <label for="registerCEP" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="registerCEP" placeholder="Digite seu CEP">
                </div>
                <button type="submit" class="btn btn-custom w-100">Cadastrar</button>
            </form>
            <div class="text-center mt-3">
                <span>Já tem uma conta? <a href="login.php" class="footer-link">Entrar</a></span>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>