<?php $pageTitle = 'Perfil | Batrip'; ?>
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
    <section class="section">
        <div class="container">
            <h2 class="section-title mb-4 text-center"><i class="fas fa-user"></i> Meu Perfil</h2>
            <div class="row justify-content-center">
                <div class="col-md-3 mb-4">
                    <div class="nav flex-column nav-pills perfil-tabs" id="perfilTabs" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="principal-tab" data-bs-toggle="pill" data-bs-target="#principal" type="button" role="tab" aria-controls="principal" aria-selected="true">
                            <i class="fas fa-user"></i> Principal
                        </button>
                        <button class="nav-link" id="seguranca-tab" data-bs-toggle="pill" data-bs-target="#seguranca" type="button" role="tab" aria-controls="seguranca" aria-selected="false">
                            <i class="fas fa-lock"></i> Segurança
                        </button>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="tab-content" id="perfilTabsContent">
                        <div class="tab-pane fade show active" id="principal" role="tabpanel">
                            <form>
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" value="Usuário Exemplo">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" value="usuario@exemplo.com">
                                </div>
                                <div class="mb-3">
                                    <label for="endereco" class="form-label">Endereço</label>
                                    <input type="text" class="form-control" id="endereco" value="Rua Exemplo, 123">
                                </div>
                                <div class="mb-3">
                                    <label for="complemento" class="form-label">Complemento</label>
                                    <input type="text" class="form-control" id="complemento" value="Apto 1">
                                </div>
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <input type="text" class="form-control" id="estado" value="SP">
                                </div>
                                <div class="mb-3">
                                    <label for="cep" class="form-label">CEP</label>
                                    <input type="text" class="form-control" id="cep" value="00000-000">
                                </div>
                                <button type="submit" class="btn btn-custom w-100 mb-2">Salvar Alterações</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="seguranca" role="tabpanel">
                            <form>
                                <div class="mb-3">
                                    <label for="senhaAtual" class="form-label">Senha Atual</label>
                                    <input type="password" class="form-control" id="senhaAtual">
                                </div>
                                <div class="mb-3">
                                    <label for="novaSenha" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" id="novaSenha">
                                </div>
                                <div class="mb-3">
                                    <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" id="confirmarSenha">
                                </div>
                                <button type="submit" class="btn btn-custom w-100 mb-2">Alterar Senha</button>
                                <a href="redefinir-senha.php" class="btn btn-outline-danger w-100"><i class="fas fa-key"></i> Redefinir Senha</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
<?php $pageTitle = 'Perfil | Batrip'; ?>
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
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
// ...navbar agora é incluída via nav.php
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- ...conteúdo da página de perfil... -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
