<?php $pageTitle = 'Redefinir Senha | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5 custom-form shadow">
            <h2 class="section-title mb-4">Redefinir Senha</h2>
            <form>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" placeholder="Digite seu e-mail">
                </div>
                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" id="novaSenha" placeholder="Digite a nova senha">
                </div>
                <div class="mb-3">
                    <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                    <input type="password" class="form-control" id="confirmarSenha" placeholder="Confirme a nova senha">
                </div>
                <button type="submit" class="btn btn-custom w-100">Redefinir</button>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="footer-link">Voltar ao login</a>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

