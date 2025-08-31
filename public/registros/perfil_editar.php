<?php
$pageTitle = 'Editar Perfil | Batrip';
include '../../includes/head.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();

$msg = '';
$error = '';

// Carrega usuário atual
$stmt = $pdo->prepare('SELECT name, email, endereco, cidade, estado, cep, password FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Sessão expirada. Tente novamente.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $senhaConfirm = (string)($_POST['senha_confirm'] ?? '');

        if ($name === '' || $email === '') {
            $error = 'Nome e email são obrigatórios.';
        } elseif (!$user || !password_verify($senhaConfirm, $user['password'])) {
            $error = 'Senha atual incorreta.';
        } else {
            // Atualiza
            $stmtU = $pdo->prepare('UPDATE users SET name=?, email=?, endereco=?, cidade=?, estado=?, cep=? WHERE id = ?');
            $stmtU->execute([$name, $email, $endereco, $cidade, $estado, $cep, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $name; // reflete na navbar
            $_SESSION['user_email'] = $email;
            $msg = 'Perfil atualizado com sucesso.';
            // recarrega
            $stmt = $pdo->prepare('SELECT name, email, endereco, cidade, estado, cep, password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
    }
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-5" style="max-width: 720px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Editar Perfil</h1>
    <a href="perfil.php" class="btn btn-sm btn-outline-light">Voltar</a>
  </div>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post" class="card card-body bg-dark text-light">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label">Endereço</label>
        <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($user['endereco'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Cidade</label>
        <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($user['cidade'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <input type="text" name="estado" id="perfil-estado" class="form-control" maxlength="2" value="<?= htmlspecialchars($user['estado'] ?? '') ?>" pattern="[A-Za-z]{2}" title="UF com 2 letras">
      </div>
      <div class="col-md-4">
        <label class="form-label">CEP</label>
        <div class="input-group">
          <input type="text" name="cep" id="perfil-cep" class="form-control" value="<?= htmlspecialchars($user['cep'] ?? '') ?>" inputmode="numeric" maxlength="9" placeholder="00000-000">
          <button class="btn btn-outline-light" type="button" id="btn-buscar-cep">Buscar</button>
        </div>
        <div class="form-text text-muted">Preencha o CEP e clique em Buscar para auto-preencher endereço/cidade/UF.</div>
      </div>
      <div class="col-12">
        <label class="form-label">Confirme sua senha para salvar</label>
        <input type="password" name="senha_confirm" class="form-control" required>
      </div>
      <div class="col-12">
        <button class="btn btn-custom w-100">Salvar Alterações</button>
      </div>
    </div>
  </form>
  <script>
    (function(){
      const cepInput = document.getElementById('perfil-cep');
      const ufInput = document.getElementById('perfil-estado');
      const btn = document.getElementById('btn-buscar-cep');
      const cidadeInput = document.querySelector('input[name="cidade"]');
      const enderecoInput = document.querySelector('input[name="endereco"]');
      function maskCEP(v){
        const only = (v||'').replace(/\D/g,'').slice(0,8);
        if (only.length <= 5) return only;
        return only.slice(0,5) + '-' + only.slice(5);
      }
      if (cepInput){
        cepInput.addEventListener('input', function(){
          this.value = maskCEP(this.value);
        });
      }
      if (ufInput){
        ufInput.addEventListener('input', function(){ this.value = this.value.toUpperCase().slice(0,2); });
      }
      async function buscarCEP(){
        if (!cepInput) return;
        const cepNum = (cepInput.value||'').replace(/\D/g,'');
        if (cepNum.length !== 8) { alert('CEP inválido.'); return; }
        try{
          const resp = await fetch('https://viacep.com.br/ws/' + cepNum + '/json/');
          if (!resp.ok) throw new Error('Falha ao consultar CEP');
          const data = await resp.json();
          if (data.erro) { alert('CEP não encontrado.'); return; }
          if (enderecoInput) enderecoInput.value = (data.logradouro||'') + (data.bairro? (' - ' + data.bairro) : '');
          if (cidadeInput) cidadeInput.value = data.localidade||'';
          if (ufInput) ufInput.value = (data.uf||'');
        }catch(e){
          alert('Erro ao buscar CEP.');
        }
      }
      if (btn) btn.addEventListener('click', buscarCEP);
    })();
  </script>
</main>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>
</html>
