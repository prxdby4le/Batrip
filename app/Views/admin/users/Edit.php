<?php
/**
 * View: Admin Users Edit
 */

$user = $user ?? [];
?>

<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>adm/usuarios" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="bi bi-pencil me-2"></i>Editar Usuário
                </h4>
                
                <form method="POST" action="<?php echo BASE_URL; ?>adm/usuarios/<?php echo $user['id']; ?>/atualizar">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user" <?php echo ($user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>
                                User (Cliente)
                            </option>
                            <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                Admin (Administrador)
                            </option>
                        </select>
                        <div class="form-text">
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="text-warning">⚠️ Este é seu usuário. Tenha cuidado ao alterar o role.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Alterar Senha (Opcional)</h6>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Deixe em branco para não alterar">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-custom btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                        <a href="<?php echo BASE_URL; ?>adm/usuarios" class="btn btn-outline-light">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
