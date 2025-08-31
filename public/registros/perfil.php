<?php 
$pageTitle = 'Perfil | Batrip'; 
include '../../includes/head.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();

// Carrega dados do usuário logado
$stmt = $pdo->prepare('SELECT name, email, endereco, cidade, estado, cep, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section">
        <div class="container">
            <h2 class="section-title mb-4 text-center"><i class="fas fa-user"></i> Meu Perfil</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card bg-dark text-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 m-0">Dados do Perfil</h3>
                                <div class="d-flex gap-2">
                                    <a href="perfil_editar.php" class="btn btn-sm btn-outline-light">Editar Perfil</a>
                                    <a href="senha.php" class="btn btn-sm btn-outline-warning">Alterar Senha</a>
                                </div>
                            </div>
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Nome</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($user['name'] ?? '') ?></dd>
                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($user['email'] ?? '') ?></dd>
                                <dt class="col-sm-3">Endereço</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($user['endereco'] ?? '-') ?></dd>
                                <dt class="col-sm-3">Cidade/UF</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars(($user['cidade'] ?? '-') . ' / ' . ($user['estado'] ?? '-')) ?></dd>
                                <dt class="col-sm-3">CEP</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($user['cep'] ?? '-') ?></dd>
                                <dt class="col-sm-3">Desde</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($user['created_at'] ?? '-') ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

