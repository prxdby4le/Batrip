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
                    
                    <div id="login-alert" class="alert d-none mb-4" role="alert"></div>
                    
                    <form id="login-form" method="POST">
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php 
                            require_once ROOT_PATH . '/includes/auth.php';
                            echo htmlspecialchars(get_csrf_token()); 
                        ?>">
                        
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
                            <button type="submit" class="btn btn-custom btn-lg" id="login-submit">
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
                    
                    <script>
                    document.getElementById('login-form').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        const form = e.target;
                        const submitBtn = document.getElementById('login-submit');
                        const alertDiv = document.getElementById('login-alert');
                        const originalText = submitBtn.innerHTML;
                        
                        // Desabilita botão
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Entrando...';
                        alertDiv.classList.add('d-none');
                        
                        try {
                            const response = await fetch('<?php echo BASE_URL; ?>login', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.getElementById('csrf_token').value,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    email: document.getElementById('email').value,
                                    password: document.getElementById('password').value,
                                    csrf_token: document.getElementById('csrf_token').value
                                })
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                alertDiv.className = 'alert alert-success mb-4';
                                alertDiv.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                                alertDiv.classList.remove('d-none');
                                
                                // Redireciona após 1 segundo
                                setTimeout(() => {
                                    window.location.href = data.redirect || '<?php echo BASE_URL; ?>';
                                }, 1000);
                            } else {
                                alertDiv.className = 'alert alert-danger mb-4';
                                alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + (data.message || 'Erro ao fazer login');
                                alertDiv.classList.remove('d-none');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            }
                        } catch (error) {
                            alertDiv.className = 'alert alert-danger mb-4';
                            alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Erro ao conectar com o servidor. Tente novamente.';
                            alertDiv.classList.remove('d-none');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
