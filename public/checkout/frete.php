<?php require_once __DIR__ . '/../../includes/auth.php'; require_login(); ?>
<?php
// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        die('CSRF inválido');
    }
    $_SESSION['checkout_frete'] = $_POST['frete'] ?? 'SEDEX';
    header('Location: revisao.php');
    exit;
}
?>
<?php $pageTitle = 'Escolha o Frete | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-truck"></i> Escolha o Frete</h2>
            <form id="frete-form" class="row g-3" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="col-12">
                    <label class="form-label">Opções de Frete</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="frete" id="sedex" value="SEDEX" checked>
                        <label class="form-check-label" for="sedex">
                            SEDEX - R$ 29,90 - Entrega em até 3 dias úteis
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="frete" id="pac" value="PAC">
                        <label class="form-check-label" for="pac">
                            PAC - R$ 19,90 - Entrega em até 7 dias úteis
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-custom w-100">Continuar para Revisão</button>
                </div>
            </form>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

