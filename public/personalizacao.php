<?php
$pageTitle = 'Solicitar Personalização | Batrip';
include '../includes/head.php';
// Página estática para solicitação de personalização
?>
<body>
<?php include '../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section">
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-8 col-lg-7 custom-form shadow">
      <h2 class="section-title mb-4">Solicitar Personalização</h2>
      <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Descrição do Pedido</label>
          <textarea name="descricao" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Imagem de Referência (opcional)</label>
          <input type="file" name="imagem" class="form-control">
        </div>
        <button class="btn btn-custom w-100">Enviar Solicitação</button>
      </form>
    </div>
  </div>
</section>
<?php include '../includes/footer.php'; ?>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
