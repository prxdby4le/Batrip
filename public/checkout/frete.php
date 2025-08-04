<?php $pageTitle = 'Frete | Checkout | Batrip'; ?>
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
            <h2 class="section-title mb-4">Escolha o Frete</h2>
            <form>
                <div class="mb-3">
                    <label class="form-label">Opções de Frete</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="frete" id="frete1" checked>
                        <label class="form-check-label" for="frete1">PAC - R$ 20,00 (5-10 dias úteis)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="frete" id="frete2">
                        <label class="form-check-label" for="frete2">SEDEX - R$ 35,00 (2-5 dias úteis)</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-custom w-100">Continuar</button>
            </form>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
<?php
$pageTitle = 'Escolha o Frete | Batrip';
?>
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
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div style="height: 70px;"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-truck"></i> Escolha o Frete</h2>
            <form id="frete-form" class="row g-3">
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
</body>
</html>
