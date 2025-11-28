<?php
/**
 * View: Profile/Edit
 * Formulário de edição de perfil - Layout estilo rede social
 */

// Verificar foto de perfil
$profileImg = null;
if (!empty($user['profile_img'])) {
    $profileImgPath = BASE_URL . 'assets/img/perfil/' . htmlspecialchars($user['profile_img']);
    $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__));
    if (file_exists($rootPath . '/assets/img/perfil/' . $user['profile_img'])) {
        $profileImg = $profileImgPath;
    }
}

$profileBg = !empty($user['profile_bg']) ? htmlspecialchars($user['profile_bg']) : null;
?>

<div class="navbar-space"></div>

<style>
    .profile-edit-layout {
        padding-top: 0;
        padding-bottom: 40px;
    }

    /* Cover Photo / Background */
    .profile-cover-edit {
        width: 100%;
        height: 350px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: opacity 0.3s;
    }

    .profile-cover-edit:hover {
        opacity: 0.9;
    }

    .profile-cover-edit img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-cover-edit-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .profile-cover-edit:hover .profile-cover-edit-overlay {
        opacity: 1;
    }

    .profile-cover-edit-overlay-content {
        text-align: center;
        color: white;
    }

    .profile-cover-edit-overlay-content i {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    /* Profile Picture */
    .profile-picture-edit-container {
        position: relative;
        margin-top: -80px;
        margin-bottom: 20px;
        z-index: 10;
        text-align: center;
    }

    .profile-picture-edit {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        border: 5px solid #1a1a1a;
        background: #2a2a2a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .profile-picture-edit:hover {
        transform: scale(1.05);
    }

    .profile-picture-edit img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-picture-edit .bi-person-circle {
        font-size: 140px;
        color: var(--accent-blue);
    }

    .profile-picture-edit-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        border-radius: 50%;
    }

    .profile-picture-edit:hover .profile-picture-edit-overlay {
        opacity: 1;
    }

    .profile-picture-edit-overlay-content {
        text-align: center;
        color: white;
        font-size: 0.9rem;
    }

    .profile-picture-edit-overlay-content i {
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    /* Form Card */
    .profile-edit-card {
        background: #1a1a1a;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    .profile-edit-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #333;
    }

    .profile-edit-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 10px;
    }

    .profile-edit-section {
        margin-bottom: 30px;
    }

    .profile-edit-section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #333;
    }

    .form-group-custom {
        margin-bottom: 20px;
    }

    .form-label-custom {
        color: #fff;
        font-weight: 500;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom {
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        border-radius: 8px;
        padding: 12px 15px;
        width: 100%;
        transition: border-color 0.3s, background 0.3s;
    }

    .form-control-custom:focus {
        background: #333;
        border-color: var(--accent-blue);
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-text-custom {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .file-upload-preview {
        margin-top: 15px;
        padding: 15px;
        background: #2a2a2a;
        border-radius: 8px;
        border: 1px solid #444;
    }

    .file-upload-preview img {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        display: block;
        margin: 0 auto;
    }

    .profile-edit-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding-top: 20px;
        border-top: 2px solid #333;
        margin-top: 30px;
    }

    .hidden-file-input {
        display: none;
    }

    @media (max-width: 768px) {
        .profile-cover-edit {
            height: 250px;
        }

        .profile-picture-edit {
            width: 120px;
            height: 120px;
        }

        .profile-picture-edit-container {
            margin-top: -60px;
        }

        .profile-edit-card {
            padding: 20px;
        }
    }
</style>

<section class="profile-edit-layout">
    <!-- Cover Photo -->
    <div class="profile-cover-edit" id="coverPhotoEdit">
        <?php if ($profileBg): ?>
            <?php 
            // Usa o script PHP para servir a imagem (garante acesso mesmo se .htaccess falhar)
            $bgUrl = BASE_URL . 'serve-upload.php?file=' . urlencode(ltrim($profileBg, '/'));
            ?>
            <img src="<?php echo $bgUrl; ?>" alt="Capa do perfil" id="coverPhotoPreview">
        <?php else: ?>
            <div id="coverPhotoPreview"></div>
        <?php endif; ?>
        <div class="profile-cover-edit-overlay">
            <div class="profile-cover-edit-overlay-content">
                <i class="bi bi-camera"></i>
                <div>Alterar Capa</div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Profile Picture -->
        <div class="profile-picture-edit-container">
            <div class="profile-picture-edit" id="profilePictureEdit">
                <?php if ($profileImg): ?>
                    <img src="<?php echo $profileImg; ?>" alt="Foto de perfil" id="profilePicturePreview">
                <?php else: ?>
                    <i class="bi bi-person-circle" id="profilePicturePreviewIcon"></i>
                <?php endif; ?>
                <div class="profile-picture-edit-overlay">
                    <div class="profile-picture-edit-overlay-content">
                        <i class="bi bi-camera"></i>
                        <div>Alterar Foto</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="profile-edit-card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php 
                    $error = $_SESSION['error'];
                    unset($_SESSION['error']);
                    echo is_array($error) ? implode('<br>', $error) : htmlspecialchars($error); 
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                    echo htmlspecialchars($success); 
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="profile-edit-header">
                <h1 class="profile-edit-title">
                    <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                </h1>
                <p style="color: #adb5bd; margin: 0;">Atualize suas informações pessoais</p>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>perfil/atualizar" enctype="multipart/form-data" id="profileEditForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                
                <!-- Hidden file inputs -->
                <input type="file" class="hidden-file-input" id="coverPhotoInput" name="profile_bg" accept="image/jpeg,image/jpg,image/png,image/webp">
                <input type="file" class="hidden-file-input" id="profilePictureInput" name="profile_img" accept="image/jpeg,image/jpg,image/png,image/webp">

                <!-- Informações Pessoais -->
                <div class="profile-edit-section">
                    <h3 class="profile-edit-section-title">
                        <i class="bi bi-person"></i>
                        Informações Pessoais
                    </h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="name" class="form-label-custom">Nome Completo *</label>
                                <input type="text" class="form-control-custom" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                                       autocomplete="name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="email" class="form-label-custom">E-mail *</label>
                                <input type="email" class="form-control-custom" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                       autocomplete="email" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="phone" class="form-label-custom">Telefone</label>
                                <input type="tel" class="form-control-custom" id="phone" name="phone" 
                                       placeholder="(00) 00000-0000"
                                       autocomplete="tel"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="profile-edit-section">
                    <h3 class="profile-edit-section-title">
                        <i class="bi bi-geo-alt"></i>
                        Endereço (Opcional)
                    </h3>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group-custom">
                                <label for="endereco" class="form-label-custom">Endereço</label>
                                <input type="text" class="form-control-custom" id="endereco" name="endereco" 
                                       placeholder="Rua, Avenida, etc."
                                       autocomplete="street-address"
                                       value="<?php echo htmlspecialchars($user['endereco'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label for="cep" class="form-label-custom">CEP</label>
                                <input type="text" class="form-control-custom" id="cep" name="cep" 
                                       placeholder="00000-000" maxlength="9"
                                       autocomplete="postal-code"
                                       value="<?php echo htmlspecialchars(!empty($user['cep']) ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $user['cep']) : ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="cidade" class="form-label-custom">Cidade</label>
                                <input type="text" class="form-control-custom" id="cidade" name="cidade" 
                                       placeholder="Nome da cidade"
                                       autocomplete="address-level2"
                                       value="<?php echo htmlspecialchars($user['cidade'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="estado" class="form-label-custom">Estado</label>
                                <input type="text" class="form-control-custom" id="estado" name="estado" 
                                       placeholder="UF" maxlength="2"
                                       autocomplete="address-level1"
                                       value="<?php echo htmlspecialchars($user['estado'] ?? ''); ?>">
                                <div class="form-text-custom">Digite apenas a sigla (ex: SP, RJ, MG)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="profile-edit-actions">
                    <button type="submit" class="btn btn-custom btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                    </button>
                    <a href="<?php echo BASE_URL; ?>perfil" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    // Cover photo upload
    document.getElementById('coverPhotoEdit').addEventListener('click', function() {
        document.getElementById('coverPhotoInput').click();
    });

    document.getElementById('coverPhotoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('coverPhotoPreview');
                if (!preview.tagName || preview.tagName.toLowerCase() !== 'img') {
                    const img = document.createElement('img');
                    img.id = 'coverPhotoPreview';
                    img.alt = 'Capa do perfil';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    preview.replaceWith(img);
                }
                document.getElementById('coverPhotoPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Profile picture upload
    document.getElementById('profilePictureEdit').addEventListener('click', function() {
        document.getElementById('profilePictureInput').click();
    });

    document.getElementById('profilePictureInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('profilePictureEdit');
                const previewIcon = document.getElementById('profilePicturePreviewIcon');
                let previewImg = document.getElementById('profilePicturePreview');
                
                if (previewIcon) {
                    previewIcon.remove();
                }
                
                if (!previewImg || previewImg.tagName.toLowerCase() !== 'img') {
                    previewImg = document.createElement('img');
                    previewImg.id = 'profilePicturePreview';
                    previewImg.alt = 'Foto de perfil';
                    previewImg.style.width = '100%';
                    previewImg.style.height = '100%';
                    previewImg.style.objectFit = 'cover';
                    container.insertBefore(previewImg, container.firstChild);
                }
                previewImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
