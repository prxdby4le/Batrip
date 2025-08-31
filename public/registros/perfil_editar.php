<?php
$pageTitle = 'Editar Perfil | Batrip';
include '../../includes/head.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();

$msg = '';
$error = '';


// Carrega usuário atual
$stmt = $pdo->prepare('SELECT name, display_name, email, endereco, cidade, estado, cep, password, profile_img FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$userId = (int)$_SESSION['user_id'];
$profileImgPath = '../../assets/img/perfil/usuario_' . $userId . '.jpg';
$hasProfileImg = file_exists(__DIR__ . '/../../assets/img/perfil/usuario_' . $userId . '.jpg');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($token)) {
    $error = 'Sessão expirada. Tente novamente.';
  } else {
    $name = trim($_POST['name'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $senhaConfirm = (string)($_POST['senha_confirm'] ?? '');

    // Upload da foto de perfil
    $profileImgDb = $user['profile_img'] ?? null;
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['profile_img']['tmp_name'];
      $ext = strtolower(pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','webp'];
      if (in_array($ext, $allowed)) {
        $dest = __DIR__ . '/../../assets/img/perfil/usuario_' . $userId . '.jpg';
        $img = @imagecreatefromstring(file_get_contents($tmp));
        if ($img) {
          imagejpeg($img, $dest, 90);
          imagedestroy($img);
          $profileImgDb = 'assets/img/perfil/usuario_' . $userId . '.jpg';
        } else {
          $error = 'Arquivo de imagem inválido.';
        }
      } else {
        $error = 'Formato de imagem não suportado. Use JPG, PNG ou WEBP.';
      }
    }

    if ($name === '' || $display_name === '' || $email === '') {
      $error = 'Nome, nome de exibição e email são obrigatórios.';
    } elseif (!preg_match('/^[a-zA-Z0-9_\.]{3,32}$/', $display_name)) {
      $error = 'Nome de exibição deve ter entre 3 e 32 caracteres e usar apenas letras, números, _ ou ponto.';
    } elseif (!$user || !password_verify($senhaConfirm, $user['password'])) {
      $error = 'Senha atual incorreta.';
    } elseif (!$error) {
      // Verifica se display_name já existe para outro usuário
      $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE display_name = ? AND id != ?');
      $stmtCheck->execute([$display_name, $_SESSION['user_id']]);
      if ($stmtCheck->fetch()) {
        $error = 'Nome de exibição já está em uso.';
      } else {
        // Atualiza
        $stmtU = $pdo->prepare('UPDATE users SET name=?, display_name=?, email=?, endereco=?, cidade=?, estado=?, cep=?, profile_img=? WHERE id = ?');
        $stmtU->execute([$name, $display_name, $email, $endereco, $cidade, $estado, $cep, $profileImgDb, $_SESSION['user_id']]);
        $_SESSION['user_name'] = $name; // reflete na navbar
        $_SESSION['user_email'] = $email;
        $msg = 'Perfil atualizado com sucesso.';
        // recarrega
        $stmt = $pdo->prepare('SELECT name, display_name, email, endereco, cidade, estado, cep, password, profile_img FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
      }
    }
  }
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section">
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-8 col-lg-7 custom-form shadow">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Editar Perfil</h2>
        <a href="registros/perfil.php" class="btn btn-sm btn-outline-light">Voltar</a>
      </div>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="row g-3">
          <div class="col-12 text-center mb-2">
            <div id="profile-img-preview-area">
            <?php if ($hasProfileImg): ?>
              <img id="profile-img-preview" src="<?= $profileImgPath ?>?v=<?= filemtime(__DIR__ . '/../../assets/img/perfil/usuario_' . $userId . '.jpg') ?>" alt="Foto de perfil" class="rounded-circle shadow" style="width:90px;height:90px;object-fit:cover;border:3px solid var(--accent-red);background:#222;">
            <?php else: ?>
              <div id="profile-img-preview" class="perfil-avatar d-inline-flex align-items-center justify-content-center" style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#222,var(--accent-red));color:#fff;font-size:2.2rem;font-weight:900;box-shadow:0 0 0 4px var(--accent-red);border:3px solid var(--accent-white);">
                <?= strtoupper(mb_substr($user['name'] ?? '?', 0, 1, 'UTF-8')) ?>
              </div>
            <?php endif; ?>
            </div>
            <div class="mt-2">
              <label class="form-label">Foto de perfil</label>
              <input type="file" name="profile_img" accept="image/*" class="form-control" id="profile-img-input">
              <div class="form-text text-muted">JPG, PNG ou WEBP. Máx: 2MB.</div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome de exibição <span class="text-muted">(@)</span></label>
            <input type="text" name="display_name" class="form-control" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>" required pattern="[a-zA-Z0-9_\.]{3,32}" title="Entre 3 e 32 caracteres. Letras, números, underline ou ponto.">
          </div>
          <div class="col-md-12">
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
    </div>
  </div>
</section>
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

      // Preview da foto de perfil
      const imgInput = document.getElementById('profile-img-input');
      const previewArea = document.getElementById('profile-img-preview-area');
      imgInput && imgInput.addEventListener('change', function(e){
        const file = this.files && this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev){
          let img = previewArea.querySelector('img#profile-img-preview');
          if (!img) {
            img = document.createElement('img');
            img.id = 'profile-img-preview';
            img.className = 'rounded-circle shadow';
            img.style.width = '90px';
            img.style.height = '90px';
            img.style.objectFit = 'cover';
            img.style.border = '3px solid var(--accent-red)';
            img.style.background = '#222';
            previewArea.innerHTML = '';
            previewArea.appendChild(img);
          }
          img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
      });
    })();
</script>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>

</html>
