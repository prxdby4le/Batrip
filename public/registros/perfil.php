<?php 
$pageTitle = 'Perfil | Batrip'; 
include '../../includes/head.php';
// Integração inicial: dados mockados
$user = [
    'name' => 'Usuário Exemplo',
    'display_name' => 'usuarioexemplo',
    'email' => 'exemplo@teste.com',
    'endereco' => 'Rua Exemplo, 123',
    'cidade' => 'Cidade',
    'estado' => 'UF',
    'cep' => '00000-000',
    'created_at' => '2025-08-31'
];
$orders = [];
$userId = 1;
$profileImgPath = "../../assets/img/perfil/usuario_1.jpg";
$hasProfileImg = false;
$avatar = 'U';
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section">
        <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="col-md-8 col-lg-7 custom-form shadow">
                <h2 class="section-title mb-4 text-center"><i class="fas fa-user"></i> Bem-vindo, <span style="color:var(--accent-red)">@<?= htmlspecialchars($user['display_name'] ?? '') ?></span>!</h2>
                <div class="mb-4">
                    <div class="d-flex justify-content-center mb-4">
                        <?php if ($hasProfileImg): ?>
                            <img src="<?= $profileImgPath ?>?v=<?= filemtime(__DIR__ . '/../../assets/img/perfil/usuario_' . $userId . '.jpg') ?>" alt="Foto de perfil" class="rounded-circle shadow" style="width:100px;height:100px;object-fit:cover;border:3px solid var(--accent-red);background:#222;">
                        <?php else: ?>
                            <div class="perfil-avatar d-flex align-items-center justify-content-center" style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#222,var(--accent-red));color:#fff;font-size:2.7rem;font-weight:900;box-shadow:0 0 0 4px var(--accent-red);border:3px solid var(--accent-white);">
                                <?= $avatar ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 m-0">Dados do Perfil</h3>
                        <div class="d-flex gap-2">
                            <a href="registros/perfil_editar.php" class="btn btn-sm btn-outline-light">Editar Perfil</a>
                            <a href="registros/alterar_senha.php" class="btn btn-sm btn-outline-warning">Alterar Senha</a>
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Nome</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($user['name'] ?? '') ?></dd>
                        <dt class="col-sm-3">Nome de exibição</dt>
                        <dd class="col-sm-9">@<?= htmlspecialchars($user['display_name'] ?? '') ?></dd>
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
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 m-0">Meus Pedidos</h3>
                    </div>
                    <?php if (!$orders): ?>
                        <div class="alert alert-info mb-0">Você ainda não realizou nenhum pedido.</div>
                        <a class="btn btn-custom mt-3" href="../index.php">Ir às compras</a>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-end">Frete</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><?= (int)$o['id'] ?></td>
                                        <td><?= htmlspecialchars($o['created_at'] ?? '-') ?></td>
                                        <td class="text-end">R$ <?= number_format((float)$o['subtotal'],2,',','.') ?></td>
                                        <td class="text-end">R$ <?= number_format((float)$o['shipping'],2,',','.') ?></td>
                                        <td class="text-end">R$ <?= number_format((float)$o['total'],2,',','.') ?></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-light" href="pedido.php?id=<?= (int)$o['id'] ?>">Ver</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

