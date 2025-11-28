<?php
/**
 * View: Auth/Register
 * Página de registro
 */
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Criar Conta</h2>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>register">
                        <div class="mb-4">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Seu Nome" autocomplete="name" required autofocus
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="seu@email.com" autocomplete="email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
                            <div class="form-text" style="color: #6c757d; margin-top: 0.5rem;">A senha deve ter no mínimo 6 caracteres</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                   placeholder="Digite a senha novamente" autocomplete="new-password" required>
                        </div>
                        
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-custom btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Criar Conta
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0" style="color: #6c757d;">
                                Já tem uma conta? 
                                <a href="<?php echo BASE_URL; ?>login" class="text-decoration-none fw-semibold" style="color: #0d6efd;">
                                    Faça login aqui
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
