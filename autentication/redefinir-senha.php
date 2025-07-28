<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha | Batrip</title>
    <link rel="icon" href="materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arvo:wght@400;700&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4 text-center"><i class="fas fa-key"></i> Redefinir Senha</h2>
            <form id="redefinirSenhaForm">
                <div class="mb-3">
                    <label for="emailRedefinir" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="emailRedefinir" placeholder="Digite seu e-mail" required>
                </div>
                <div class="mb-3">
                    <label for="codigoVerificacao" class="form-label">Código de Verificação</label>
                    <input type="text" class="form-control" id="codigoVerificacao" placeholder="Digite o código recebido" required>
                </div>
                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" id="novaSenha" placeholder="Digite a nova senha" required>
                </div>
                <div class="mb-3">
                    <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                    <input type="password" class="form-control" id="confirmarSenha" placeholder="Confirme a nova senha" required>
                </div>
                <button type="submit" class="btn btn-custom w-100">Redefinir Senha</button>
            </form>
            <div id="redefinirSenhaMsg" class="alert alert-success mt-3 d-none">Senha redefinida com sucesso!</div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
