<?php
$pageTitle = 'Editar Perfil | Batrip';
include '../../includes/head.php';
// Página estática para entrega acadêmica, sem autenticação ou backend
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
      <div class="mb-3 text-end">
        <a href="registros/alterar_senha.php" class="btn btn-outline-warning">Alterar Senha</a>
      </div>
      <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-12 text-center mb-2">
            <div id="profile-img-preview-area">
              <div id="profile-img-preview" class="perfil-avatar d-inline-flex align-items-center justify-content-center" style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#222,var(--accent-red));color:#fff;font-size:2.2rem;font-weight:900;box-shadow:0 0 0 4px var(--accent-red);border:3px solid var(--accent-white);">
                U
              </div>
            </div>
            <div class="mt-2">
              <label class="form-label">Foto de perfil</label>
              <input type="file" name="profile_img" accept="image/*" class="form-control" id="profile-img-input">
              <div class="form-text text-muted">JPG, PNG ou WEBP. Máx: 2MB.</div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" value="Usuário Exemplo" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome de exibição <span class="text-muted">(@)</span></label>
            <input type="text" name="display_name" class="form-control" value="usuario_exemplo" required pattern="[a-zA-Z0-9_\.]{3,32}" title="Entre 3 e 32 caracteres. Letras, números, underline ou ponto.">
          </div>
          <div class="col-md-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="usuario@email.com" required>
          </div>
          <div class="col-12">
            <label class="form-label">Endereço</label>
            <input type="text" name="endereco" class="form-control" value="Rua Exemplo, 123">
          </div>
          <div class="col-md-6">
            <label class="form-label">Cidade</label>
            <input type="text" name="cidade" class="form-control" value="Cidade Exemplo">
          </div>
          <div class="col-md-2">
            <label class="form-label">Estado</label>
            <input type="text" name="estado" id="perfil-estado" class="form-control" maxlength="2" value="EX" pattern="[A-Za-z]{2}" title="UF com 2 letras">
          </div>
          <div class="col-md-4">
            <label class="form-label">CEP</label>
            <div class="input-group">
              <input type="text" name="cep" id="perfil-cep" class="form-control" value="00000-000" inputmode="numeric" maxlength="9" placeholder="00000-000">
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
