<?php
/**
 * View: Auth/Login
 * Página de login
 */
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Login</h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>login">
                        <div class="mb-4">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="seu@email.com" autocomplete="email" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="********" autocomplete="current-password" required>
                        </div>
                        
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-custom btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0" style="color: #6c757d;">
                                Não tem uma conta? 
                                <a href="<?php echo BASE_URL; ?>register" class="text-decoration-none fw-semibold" style="color: #0d6efd;">
                                    Registre-se aqui
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
