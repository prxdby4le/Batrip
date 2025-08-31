<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Pedido realizado | Batrip';
include '../../includes/head.php';
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section" style="min-height:60vh;">
  <div class="container">
    <div class="alert alert-success">
      <h4 class="alert-heading">Pedido confirmado!</h4>
      <p>Obrigado pela compra. Seu pedido #<?= (int)($_GET['id'] ?? 0) ?> foi registrado com sucesso.</p>
      <hr>
      <p class="mb-0">Em breve você receberá atualizações por email.</p>
    </div>
    <a class="btn btn-custom" href="../index.php">Voltar à loja</a>
  </div>
</section>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
