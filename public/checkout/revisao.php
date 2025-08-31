<?php require_once __DIR__ . '/../../includes/auth.php'; require_login(); ?>
<?php $pageTitle = 'Revisão do Pedido | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-clipboard-check"></i> Revisão do Pedido</h2>
            <?php
                // Se não houver endereço salvo, redireciona para preenchimento
                $addr = $_SESSION['checkout_address'] ?? null;
                if (!$addr) {
                    header('Location: endereco.php');
                    exit;
                }
            ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Endereço de Entrega</h5>
                    <div id="resumo-endereco">
                        <?php echo htmlspecialchars($addr['endereco'] ?? ''); ?>, <?php echo htmlspecialchars($addr['numero'] ?? ''); ?><?php echo !empty($addr['complemento']) ? ' - ' . htmlspecialchars($addr['complemento']) : ''; ?><br>
                        <?php echo htmlspecialchars($addr['bairro'] ?? ''); ?> - <?php echo htmlspecialchars($addr['cidade'] ?? ''); ?>/<?php echo htmlspecialchars($addr['uf'] ?? ''); ?><br>
                        CEP: <?php echo htmlspecialchars($addr['cep'] ?? ''); ?>
                    </div>
                    <a href="endereco.php" class="btn btn-link p-0 mt-2">Editar endereço</a>
                </div>
            </div>
            <?php require_once __DIR__ . '/../../includes/cart-functions.php'; $cart = get_cart(); ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Itens do Pedido</h5>
                    <?php if (empty($cart)): ?>
                        <div class="text-muted">Seu carrinho está vazio.</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cart as $item): ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars((strpos($item['img'], 'assets/img/') === 0 ? $item['img'] : 'assets/img/' . ltrim($item['img'], '/')), ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="text-muted small">Tamanho: <?php echo htmlspecialchars($item['size']); ?> • Qtd: <?php echo (int)$item['qty']; ?></div>
                                    </div>
                                    <div class="ms-3">R$ <?php echo number_format($item['qty'] * $item['price'], 2, ',', '.'); ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex justify-content-end mt-3">
                            <div>
                                <div class="d-flex justify-content-between"><span class="me-3">Subtotal:</span><strong>R$ <?php echo number_format(get_cart_subtotal(), 2, ',', '.'); ?></strong></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Frete</h5>
                    <?php 
                        $cep = get_user_cep();
                        $freteValor = calcular_frete($cep);
                        $opcao = $_SESSION['checkout_frete'] ?? 'SEDEX';
                    ?>
                    <div id="resumo-frete">Opção: <strong><?php echo htmlspecialchars($opcao); ?></strong> • Valor: <strong>R$ <?php echo number_format($freteValor, 2, ',', '.'); ?></strong></div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body d-flex justify-content-end">
                    <?php $total = get_cart_subtotal() + $freteValor; ?>
                    <div class="text-end">
                        <div>Subtotal: <strong>R$ <?php echo number_format(get_cart_subtotal(), 2, ',', '.'); ?></strong></div>
                        <div>Frete: <strong>R$ <?php echo number_format($freteValor, 2, ',', '.'); ?></strong></div>
                        <div class="fs-5 mt-2">Total: <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></div>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <a href="pagamento.php" class="btn btn-custom">Ir para Pagamento</a>
            </div>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>

