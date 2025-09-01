<?php
$pageTitle = 'Alterar Senha | Batrip';
include '../../includes/head.php';
// Página estática para entrega acadêmica, sem autenticação ou backend
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section">
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-6 col-lg-5 custom-form shadow">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Alterar Senha</h2>
        <a href="registros/perfil_editar.php" class="btn btn-sm btn-outline-light">Voltar</a>
      </div>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Senha atual</label>
          <input type="password" name="senha_atual" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nova senha</label>
          <input type="password" name="nova_senha" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirme a nova senha</label>
          <input type="password" name="nova_senha_confirm" class="form-control" required>
        </div>
        <button class="btn btn-custom w-100">Salvar Nova Senha</button>
      </form>
    </div>
  </div>
</section>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
