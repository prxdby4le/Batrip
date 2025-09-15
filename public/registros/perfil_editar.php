<?php
$pageTitle = 'Editar Perfil | Batrip';
include '../../includes/head.php';
include '../../includes/auth.php';

// Requer login para editar perfil
require_login();

$success = '';
$error = '';

// Buscar dados atuais do usuário
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        logout();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar usuário: " . $e->getMessage());
    $error = "Erro ao carregar dados do perfil.";
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Token de segurança inválido. Tente novamente.";
    } else {
        // Coletar e sanitizar dados
        $name = trim($_POST['name'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = strtoupper(trim($_POST['estado'] ?? ''));
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $senha_confirm = $_POST['senha_confirm'] ?? '';
        
        // Validações
        if (empty($name) || strlen($name) < 2) {
            $error = "Nome deve ter pelo menos 2 caracteres.";
        } elseif (empty($display_name) || !preg_match('/^[a-zA-Z0-9_\.]{3,32}$/', $display_name)) {
            $error = "Nome de usuário deve ter entre 3 e 32 caracteres (apenas letras, números, _ ou .).";
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email inválido.";
        } elseif (!empty($estado) && strlen($estado) !== 2) {
            $error = "Estado deve ter 2 caracteres.";
        } elseif (!empty($cep) && strlen($cep) !== 8) {
            $error = "CEP deve ter 8 dígitos.";
        } elseif (empty($senha_confirm)) {
            $error = "Confirme sua senha atual para salvar as alterações.";
        } elseif (!password_verify($senha_confirm, $user['password'])) {
            $error = "Senha atual incorreta.";
        } else {
            try {
                // Verificar se email ou display_name já existem (exceto o usuário atual)
                $stmt = $pdo->prepare('SELECT id FROM users WHERE (email = ? OR display_name = ?) AND id != ?');
                $stmt->execute([$email, $display_name, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $error = "Email ou nome de usuário já estão em uso.";
                } else {
                    // Processar upload de imagem se houver
                    $profile_img_updated = false;
                    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = handleProfileImageUpload($_FILES['profile_img'], $_SESSION['user_id']);
                        if ($uploadResult['success']) {
                            $profile_img_updated = true;
                        } else {
                            $error = $uploadResult['error'];
                        }
                    }
                    
                    if (empty($error)) {
                        // Atualizar dados no banco
                        $sql = 'UPDATE users SET name = ?, display_name = ?, email = ?, endereco = ?, cidade = ?, estado = ?, cep = ?';
                        $params = [$name, $display_name, $email, $endereco, $cidade, $estado, $cep];
                        
                        if ($profile_img_updated) {
                            $sql .= ', profile_img = ?';
                            $params[] = 'usuario_' . $_SESSION['user_id'] . '.jpg';
                        }
                        
                        $sql .= ' WHERE id = ?';
                        $params[] = $_SESSION['user_id'];
                        
                        $stmt = $pdo->prepare($sql);
                        if ($stmt->execute($params)) {
                            // Atualizar dados da sessão
                            $_SESSION['user_name'] = $name;
                            $_SESSION['user_email'] = $email;
                            
                            // Recarregar dados do usuário
                            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                            $stmt->execute([$_SESSION['user_id']]);
                            $user = $stmt->fetch();
                            
                            $success = "Perfil atualizado com sucesso!";
                        } else {
                            $error = "Erro ao atualizar perfil. Tente novamente.";
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Erro ao atualizar perfil: " . $e->getMessage());
                $error = "Erro ao atualizar perfil. Tente novamente.";
            }
        }
    }
}

// Função para upload de imagem
function handleProfileImageUpload($file, $userId) {
    $uploadDir = __DIR__ . '/../../assets/img/perfil/';
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    // Validar tipo de arquivo
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Formato não suportado. Use JPG, PNG ou WEBP.'];
    }
    
    // Validar tamanho
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Arquivo muito grande. Máximo 2MB.'];
    }
    
    // Gerar nome do arquivo
    $fileName = 'usuario_' . $userId . '.jpg';
    $filePath = $uploadDir . $fileName;
    
    // Criar diretório se não existir
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Remover arquivo anterior se existir
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Mover arquivo
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Redimensionar e otimizar imagem se necessário
        optimizeProfileImage($filePath);
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Erro ao fazer upload da imagem.'];
}

// Função para otimizar imagem de perfil
function optimizeProfileImage($filePath) {
    if (!extension_loaded('gd')) return;
    
    $maxSize = 300; // pixels
    $quality = 85;
    
    // Obter informações da imagem
    $imageInfo = getimagesize($filePath);
    if (!$imageInfo) return;
    
    list($width, $height, $type) = $imageInfo;
    
    // Não redimensionar se já é pequena
    if ($width <= $maxSize && $height <= $maxSize) return;
    
    // Calcular novas dimensões mantendo proporção
    $newWidth = $newHeight = $maxSize;
    if ($width > $height) {
        $newHeight = round(($height * $maxSize) / $width);
    } else {
        $newWidth = round(($width * $maxSize) / $height);
    }
    
    // Criar imagem original
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            return;
    }
    
    if (!$source) return;
    
    // Criar nova imagem
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Salvar como JPEG
    imagejpeg($destination, $filePath, $quality);
    
    // Limpar memória
    imagedestroy($source);
    imagedestroy($destination);
}
?>
<body>
<?php include '../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<section class="section profile-edit-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-8">
        <!-- Header -->
        <div class="edit-profile-header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h1 class="section-title mb-2">
                <i class="fas fa-user-edit me-2"></i>Editar Perfil
              </h1>
              <p class="text-muted">Atualize suas informações pessoais</p>
            </div>
            <div class="header-actions">
              <a href="perfil.php" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i>Voltar
              </a>
              <a href="alterar_senha.php" class="btn btn-outline-warning ms-2">
                <i class="fas fa-key me-1"></i>Alterar Senha
              </a>
            </div>
          </div>
        </div>

        <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
          
          <div class="row g-4">
            <!-- Profile Picture Card -->
            <div class="col-12">
              <div class="profile-card">
                <div class="profile-card-header">
                  <h3><i class="fas fa-camera me-2"></i>Foto de Perfil</h3>
                </div>
                <div class="profile-card-body text-center">
                  <div class="profile-photo-section">
                    <div id="profile-img-preview-area" class="mb-3">
                      <div id="profile-img-preview" class="profile-photo-preview">
                        <?php 
                        $hasProfileImg = !empty($user['profile_img']) && file_exists(__DIR__ . '/../../assets/img/perfil/' . $user['profile_img']);
                        if ($hasProfileImg): 
                        ?>
                          <img src="../../assets/img/perfil/<?= htmlspecialchars($user['profile_img']) ?>?v=<?= time() ?>" alt="Foto atual" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; border-radius: 50%;">
                        <?php else: ?>
                          <i class="fas fa-user"></i>
                        <?php endif; ?>
                      </div>
                      <div class="photo-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Alterar Foto</span>
                      </div>
                    </div>
                    <div class="photo-upload-section">
                      <input type="file" name="profile_img" accept="image/*" class="form-control d-none" id="profile-img-input">
                      <label for="profile-img-input" class="btn btn-custom">
                        <i class="fas fa-upload me-2"></i>Escolher Foto
                      </label>
                      <div class="form-text mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Formatos aceitos: JPG, PNG ou WEBP. Tamanho máximo: 2MB
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Personal Information Card -->
            <div class="col-12">
              <div class="profile-card">
                <div class="profile-card-header">
                  <h3><i class="fas fa-user me-2"></i>Informações Pessoais</h3>
                </div>
                <div class="profile-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="name" class="form-control" id="name-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        <label for="name-input"><i class="fas fa-user me-2"></i>Nome Completo</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="display_name" class="form-control" id="display-name-input" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>" required pattern="[a-zA-Z0-9_\.]{3,32}" title="Entre 3 e 32 caracteres. Letras, números, underline ou ponto.">
                        <label for="display-name-input"><i class="fas fa-at me-2"></i>Nome de Usuário</label>
                      </div>
                      <div class="form-text">Entre 3 e 32 caracteres. Use apenas letras, números, _ ou .</div>
                    </div>
                    <div class="col-12">
                      <div class="form-floating">
                        <input type="email" name="email" class="form-control" id="email-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        <label for="email-input"><i class="fas fa-envelope me-2"></i>Email</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Address Information Card -->
            <div class="col-12">
              <div class="profile-card">
                <div class="profile-card-header">
                  <h3><i class="fas fa-map-marker-alt me-2"></i>Informações de Endereço</h3>
                </div>
                <div class="profile-card-body">
                  <div class="row g-3">
                    <div class="col-12">
                      <div class="form-floating">
                        <input type="text" name="endereco" class="form-control" id="endereco-input" value="<?= htmlspecialchars($user['endereco'] ?? '') ?>">
                        <label for="endereco-input"><i class="fas fa-home me-2"></i>Endereço</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-floating">
                        <input type="text" name="cep" id="perfil-cep" class="form-control" value="<?= htmlspecialchars($user['cep'] ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $user['cep']) : '') ?>" inputmode="numeric" maxlength="9" placeholder="00000-000">
                        <label for="perfil-cep"><i class="fas fa-mail-bulk me-2"></i>CEP</label>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <button class="btn btn-outline-light w-100 h-100" type="button" id="btn-buscar-cep">
                        <i class="fas fa-search me-1"></i>Buscar CEP
                      </button>
                    </div>
                    <div class="col-md-4">
                      <div class="form-floating">
                        <input type="text" name="cidade" class="form-control" id="cidade-input" value="<?= htmlspecialchars($user['cidade'] ?? '') ?>">
                        <label for="cidade-input"><i class="fas fa-city me-2"></i>Cidade</label>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-floating">
                        <input type="text" name="estado" id="perfil-estado" class="form-control" maxlength="2" value="<?= htmlspecialchars($user['estado'] ?? '') ?>" pattern="[A-Za-z]{2}" title="UF com 2 letras">
                        <label for="perfil-estado"><i class="fas fa-flag me-2"></i>UF</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Dica:</strong> Preencha o CEP e clique em "Buscar CEP" para preencher automaticamente o endereço, cidade e estado.
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Security Card -->
            <div class="col-12">
              <div class="profile-card">
                <div class="profile-card-header">
                  <h3><i class="fas fa-shield-alt me-2"></i>Confirmação de Segurança</h3>
                </div>
                <div class="profile-card-body">
                  <div class="row g-3">
                    <div class="col-12">
                      <div class="form-floating">
                        <input type="password" name="senha_confirm" class="form-control" id="senha-confirm-input" required>
                        <label for="senha-confirm-input"><i class="fas fa-lock me-2"></i>Confirme sua senha atual</label>
                      </div>
                      <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Por segurança, confirme sua senha atual para salvar as alterações.
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Save Button -->
            <div class="col-12">
              <div class="text-center">
                <button type="submit" class="btn btn-custom btn-lg px-5">
                  <i class="fas fa-save me-2"></i>Salvar Todas as Alterações
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
    (function(){
      // CEP Mask and Auto-fill functionality
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
        ufInput.addEventListener('input', function(){ 
          this.value = this.value.toUpperCase().slice(0,2); 
        });
      }
      
      async function buscarCEP(){
        if (!cepInput) return;
        const cepNum = (cepInput.value||'').replace(/\D/g,'');
        if (cepNum.length !== 8) { 
          showAlert('CEP deve ter 8 dígitos.', 'warning'); 
          return; 
        }
        
        // Loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Buscando...';
        btn.disabled = true;
        
        try{
          const resp = await fetch('https://viacep.com.br/ws/' + cepNum + '/json/');
          if (!resp.ok) throw new Error('Falha ao consultar CEP');
          const data = await resp.json();
          
          if (data.erro) { 
            showAlert('CEP não encontrado.', 'warning'); 
            return; 
          }
          
          // Fill address fields
          if (enderecoInput) enderecoInput.value = (data.logradouro||'') + (data.bairro? (' - ' + data.bairro) : '');
          if (cidadeInput) cidadeInput.value = data.localidade||'';
          if (ufInput) ufInput.value = (data.uf||'');
          
          showAlert('Endereço preenchido automaticamente!', 'success');
          
        }catch(e){
          showAlert('Erro ao buscar CEP. Tente novamente.', 'danger');
        } finally {
          // Reset button
          btn.innerHTML = '<i class="fas fa-search me-1"></i>Buscar CEP';
          btn.disabled = false;
        }
      }
      
      if (btn) btn.addEventListener('click', buscarCEP);

      // Enhanced Profile Picture Preview
      const imgInput = document.getElementById('profile-img-input');
      const previewContainer = document.getElementById('profile-img-preview');
      const previewArea = document.getElementById('profile-img-preview-area');
      
      if (imgInput) {
        imgInput.addEventListener('change', function(e){
          const file = this.files && this.files[0];
          if (!file) return;
          
          // Validate file type
          if (!file.type.match(/^image\/(jpeg|jpg|png|webp)$/)) {
            showAlert('Formato não suportado. Use JPG, PNG ou WEBP.', 'warning');
            this.value = '';
            return;
          }
          
          // Validate file size (2MB max)
          if (file.size > 2 * 1024 * 1024) {
            showAlert('Arquivo muito grande. Máximo 2MB.', 'warning');
            this.value = '';
            return;
          }
          
          const reader = new FileReader();
          reader.onload = function(ev){
            // Create or update image element
            let img = previewContainer.querySelector('img');
            if (!img) {
              img = document.createElement('img');
              previewContainer.innerHTML = '';
              previewContainer.appendChild(img);
            }
            img.src = ev.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.position = 'absolute';
            img.style.top = '0';
            img.style.left = '0';
            img.style.borderRadius = '50%';
            
            showAlert('Foto selecionada com sucesso!', 'success');
          };
          reader.readAsDataURL(file);
        });
      }
      
      // Form validation enhancements
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          const displayName = document.querySelector('input[name="display_name"]');
          const email = document.querySelector('input[name="email"]');
          const password = document.querySelector('input[name="senha_confirm"]');
          
          let isValid = true;
          
          // Validate display name
          if (displayName && displayName.value.length < 3) {
            showAlert('Nome de usuário deve ter pelo menos 3 caracteres.', 'warning');
            displayName.focus();
            isValid = false;
          }
          
          // Validate email
          if (email && !email.value.includes('@')) {
            showAlert('Digite um email válido.', 'warning');
            email.focus();
            isValid = false;
          }
          
          // Validate password confirmation
          if (password && password.value.length < 1) {
            showAlert('Confirme sua senha para salvar as alterações.', 'warning');
            password.focus();
            isValid = false;
          }
          
          if (!isValid) {
            e.preventDefault();
          }
        });
      }
      
      // Utility function for alerts
      function showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlert = document.querySelector('.temp-alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} temp-alert position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
        
        const icons = {
          success: 'fas fa-check-circle',
          warning: 'fas fa-exclamation-triangle',
          danger: 'fas fa-times-circle',
          info: 'fas fa-info-circle'
        };
        
        alertDiv.innerHTML = `
          <i class="${icons[type] || icons.info} me-2"></i>
          ${message}
          <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
          if (alertDiv.parentElement) {
            alertDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alertDiv.remove(), 300);
          }
        }, 5000);
      }
      
      // Add CSS animations for alerts
      const style = document.createElement('style');
      style.textContent = `
        @keyframes slideIn {
          from { transform: translateX(100%); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
          from { transform: translateX(0); opacity: 1; }
          to { transform: translateX(100%); opacity: 0; }
        }
        .temp-alert {
          box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
      `;
      document.head.appendChild(style);
      
    })();
</script>
<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/scripts.php'; ?>
</body>

</html>
