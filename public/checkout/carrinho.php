<?php 
require_once __DIR__ . '/../../includes/auth.php'; 
require_login(); 
$pageTitle = 'Carrinho | Batrip';
require_once __DIR__ . '/../../includes/cart-functions.php';
include '../../includes/head.php';
$cart = get_cart();
$cep = get_user_cep();
$frete = calcular_frete($cep);
$subtotal = get_cart_subtotal();
$total = $subtotal + $frete;
?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h2>
            <div class="row">
                <div class="col-lg-8 order-lg-1 order-2">
                    <div class="card mb-3">
                        <div class="card-body p-0">
                            <h5 class="fw-bold mb-3 px-3 pt-3">Itens do Pedido</h5>
                            <?php if (empty($cart)): ?>
                                <div class="alert alert-info text-center m-3">Seu carrinho está vazio.</div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="cart-items-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th></th>
                                            <th>Produto</th>
                                            <th>Tamanho</th>
                                            <th>Qtd</th>
                                            <th class="text-end">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart as $item): ?>
                                            <tr>
                                                <td style="width:64px;">
                                                    <img src="<?php echo htmlspecialchars((strpos($item['img'], 'assets/img/') === 0 ? $item['img'] : 'assets/img/' . ltrim($item['img'], '/')), ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                                                </td>
                                                <td>
                                                    <div class="fw-semibold small"><?php echo htmlspecialchars($item['title']); ?></div>
                                                </td>
                                                <td style="width:140px;">
                                                    <form method="post" action="cart.php" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="action" value="updateSize">
                                                        <input type="hidden" name="title" value="<?php echo htmlspecialchars($item['title']); ?>">
                                                        <input type="hidden" name="old_size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                                        <select name="new_size" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <?php foreach (['P','M','G','GG'] as $sz): ?>
                                                                <option value="<?php echo $sz; ?>" <?php echo ($item['size'] === $sz ? 'selected' : ''); ?>><?php echo $sz; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td style="width:150px;">
                                                    <form method="post" action="cart.php" class="d-flex align-items-center gap-1">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="action" value="updateQty">
                                                        <input type="hidden" name="title" value="<?php echo htmlspecialchars($item['title']); ?>">
                                                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                                        <div class="input-group input-group-sm">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="this.nextElementSibling.stepDown(); this.form.submit();">-</button>
                                                            <input type="number" name="qty" class="form-control text-center" value="<?php echo (int)$item['qty']; ?>" min="1">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td class="text-end">R$ <?php echo number_format($item['qty'] * $item['price'], 2, ',', '.'); ?></td>
                                                <td class="text-end" style="width:48px;">
                                                    <form method="post" action="cart.php">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="action" value="remove">
                                                        <input type="hidden" name="remove_title" value="<?php echo htmlspecialchars($item['title']); ?>">
                                                        <input type="hidden" name="remove_size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                                        <button class="btn btn-sm btn-link text-danger" title="Remover"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body d-flex justify-content-end">
                            <div class="text-end">
                                <div>Subtotal: <strong>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong></div>
                                <div>Frete (estimado): <strong>R$ <?php echo number_format($frete, 2, ',', '.'); ?></strong></div>
                                <div class="fs-5 mt-2">Total: <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="endereco.php" class="btn btn-custom">Continuar</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
