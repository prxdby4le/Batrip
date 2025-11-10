<?php
$pageTitle = 'Pedido Confirmado | Batrip';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/icon-helper.php';

$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$order = null;

// Buscar dados do pedido
if ($order_id > 0 && is_logged_in()) {
    try {
        $stmt = $pdo->prepare('
            SELECT o.*, u.name as user_name, u.email as user_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ');
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erro ao buscar pedido: " . $e->getMessage());
    }
}

include '../../includes/head.php';
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php if ($order): ?>
          <!-- Sucesso -->
          <div class="text-center mb-4">
            <div class="success-icon mb-3">
              <?= icon('check-circle', 'icon-5x text-success') ?>
            </div>
            <h2 class="mb-2">Pedido Confirmado!</h2>
            <p class="text-muted">Obrigado pela sua compra, <?= htmlspecialchars($order['user_name']) ?>!</p>
          </div>
          
          <div class="card bg-dark text-light mb-4">
            <div class="card-header bg-secondary">
              <h5 class="mb-0"><?= icon('file-invoice', 'icon me-2') ?>Detalhes do Pedido</h5>
            </div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-6">
                  <strong>Número do Pedido:</strong><br>
                  <span class="text-success fs-5">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="col-6 text-end">
                  <strong>Data:</strong><br>
                  <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                </div>
              </div>
              
              <hr class="border-secondary">
              
              <div class="row">
                <div class="col-6">
                  <strong>Status:</strong><br>
                  <span class="badge bg-warning">Pendente</span>
                </div>
                <div class="col-6 text-end">
                  <strong>Total:</strong><br>
                  <span class="text-success fs-5">R$ <?= number_format($order['total'], 2, ',', '.') ?></span>
                </div>
              </div>
            </div>
          </div>
          
          <?php 
          // Decodificar endereço de entrega
          $endereco = json_decode($order['shipping_address'], true);
          if ($endereco): ?>
          <div class="card bg-dark text-light mb-4">
            <div class="card-header bg-secondary">
              <h5 class="mb-0"><?= icon('map-marker', 'icon me-2') ?>Endereço de Entrega</h5>
            </div>
            <div class="card-body">
              <p class="mb-1">
                <strong><?= htmlspecialchars($endereco['endereco'] ?? '') ?>, <?= htmlspecialchars($endereco['numero'] ?? '') ?></strong>
                <?php if (!empty($endereco['complemento'])): ?>
                  <br><span class="text-muted">Complemento: <?= htmlspecialchars($endereco['complemento']) ?></span>
                <?php endif; ?>
              </p>
              <p class="mb-1">
                <?= htmlspecialchars($endereco['bairro'] ?? '') ?> - 
                <?= htmlspecialchars($endereco['cidade'] ?? '') ?>/<?= htmlspecialchars($endereco['uf'] ?? '') ?>
              </p>
              <p class="mb-0">CEP: <?= htmlspecialchars($endereco['cep'] ?? '') ?></p>
              <?php if (!empty($endereco['comentario'])): ?>
                <p class="mb-0 mt-2 text-muted">
                  <small><?= icon('comment', 'icon me-1') ?><?= htmlspecialchars($endereco['comentario']) ?></small>
                </p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
          
          <div class="alert alert-info">
            <h5 class="alert-heading"><?= icon('info-circle', 'icon me-2') ?>Próximos Passos</h5>
            <ul class="mb-0">
              <li>Você receberá um email de confirmação em breve no endereço: <strong><?= htmlspecialchars($order['user_email']) ?></strong></li>
              <li>Acompanhe o status do seu pedido na seção <a href="<?= $base ?>registros/pedidos.php" class="alert-link">Meus Pedidos</a></li>
              <li>O prazo de entrega começa a contar após a confirmação do pagamento</li>
            </ul>
          </div>
          
          <div class="d-grid gap-2 mt-4">
            <a href="<?= $base ?>registros/pedidos.php" class="btn btn-custom">
              <?= icon('list', 'icon me-2') ?>Ver Meus Pedidos
            </a>
            <a href="<?= $base ?>index.php" class="btn btn-outline-secondary">
              <?= icon('shopping-bag', 'icon me-2') ?>Continuar Comprando
            </a>
          </div>
          
        <?php else: ?>
          <!-- Pedido não encontrado -->
          <div class="alert alert-warning text-center">
            <?= icon('exclamation-triangle', 'icon-3x mb-3') ?>
            <h4 class="alert-heading">Pedido não encontrado</h4>
            <p>Não foi possível localizar os detalhes do seu pedido.</p>
            <hr>
            <a href="<?= $base ?>registros/pedidos.php" class="btn btn-warning">Ver Meus Pedidos</a>
            <a href="<?= $base ?>index.php" class="btn btn-outline-secondary">Voltar à Loja</a>
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
