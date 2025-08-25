<?php
$pageTitle = 'Conjunto Fragmentado | Batrip';
require_once '../../includes/cart-functions.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tamanho_oversized = $_POST['tamanho_oversized'] ?? '';
    $tamanho_boxy = $_POST['tamanho_boxy'] ?? '';
    $quantidade = max(1, intval($_POST['quantidade'] ?? 1));
    if ($tamanho_oversized && $tamanho_boxy) {
        // Adiciona as duas camisetas individualmente ao carrinho
        $produto_oversized = [
            'title' => 'Conjunto Fragmentado - Oversized',
            'size' => $tamanho_oversized,
            'qty' => $quantidade,
            'price' => 135.00,
            'img' => '/Batrip/assets/img/fragmentado-frente.jpeg'
        ];
        $produto_boxy = [
            'title' => 'Conjunto Fragmentado - Boxy',
            'size' => $tamanho_boxy,
            'qty' => $quantidade,
            'price' => 135.00,
            'img' => '/Batrip/assets/img/fragmentado-costa.jpeg'
        ];
        add_to_cart($produto_oversized);
        add_to_cart($produto_boxy);
        $msg = 'Conjunto adicionado ao carrinho!';
    } else {
        $msg = 'Selecione os tamanhos de ambas as peças.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="/Batrip/materials/batrip symbol.png" type="image/x-icon">
    <link href="../assets/css/bootstrap-css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/Batrip/assets/css/styles.css" rel="stylesheet">
</head>
<?php include '../../includes/nav.php'; ?>
<body>
    <div class="navbar-space"></div>
    <section class="section produto-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="row">
                        <div class="col-6">
                            <div class="product-image-store mb-2">
                                <img src="/Batrip/assets/img/fragmentado-frente.jpeg" alt="Camiseta Oversized" class="img-fluid rounded product-img-store">
                            </div>
                            <div class="text-center small">Oversized</div>
                        </div>
                        <div class="col-6">
                            <div class="product-image-store mb-2">
                                <img src="/Batrip/assets/img/fragmentado-costa.jpeg" alt="Camiseta Boxy" class="img-fluid rounded product-img-store">
                            </div>
                            <div class="text-center small">Boxy</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="product-title mb-2">Conjunto Fragmentado</h2>
                    <p class="product-price mb-2">R$ 270,00</p>
                    <p class="product-desc">
                        Oversized + Boxy. Exclusividade, sonoridade e autenticidade em um só conjunto.
                    </p>
                    <form method="post" autocomplete="off">
                        <div class="mb-3">
                            <label for="tamanho_oversized" class="form-label">Tamanho Oversized</label>
                            <select class="form-select" id="tamanho_oversized" name="tamanho_oversized">
                                <option>P</option>
                                <option>M</option>
                                <option>G</option>
                                <option>GG</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tamanho_boxy" class="form-label">Tamanho Boxy</label>
                            <select class="form-select" id="tamanho_boxy" name="tamanho_boxy">
                                <option>P</option>
                                <option>M</option>
                                <option>G</option>
                                <option>GG</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade" value="1" min="1">
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Adicionar ao Carrinho</button>
                        <?php if ($msg): ?>
                            <div class="alert alert-success mt-2"><?php echo $msg; ?></div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php if (!@include '../../includes/footer.php') { echo '<div style="color:red;text-align:center">Erro: Não foi possível carregar o rodapé.</div>'; } ?>
    <script src="../assets/js/bootstrap-js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>

