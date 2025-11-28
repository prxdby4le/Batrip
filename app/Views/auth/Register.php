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
                    
                    <div id="register-alert" class="alert d-none mb-4" role="alert"></div>
                    
                    <form id="register-form" method="POST">
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php 
                            require_once ROOT_PATH . '/includes/auth.php';
                            echo htmlspecialchars(get_csrf_token()); 
                        ?>">
                        
                        <div class="mb-4">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Seu Nome" autocomplete="name" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label for="display_name" class="form-label">Nome de Usuário</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" 
                                   placeholder="usuario123" pattern="[a-zA-Z0-9_\.]{3,32}" required>
                            <div class="form-text" style="color: #6c757d; margin-top: 0.5rem;">Entre 3 e 32 caracteres. Use apenas letras, números, _ ou .</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="seu@email.com" autocomplete="email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Mínimo 6 caracteres" autocomplete="new-password" required minlength="6">
                            <div class="form-text" style="color: #6c757d; margin-top: 0.5rem;">A senha deve ter no mínimo 6 caracteres</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password2" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="password2" name="password2" 
                                   placeholder="Digite a senha novamente" autocomplete="new-password" required minlength="6">
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="mb-3">Endereço (opcional)</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep" 
                                       placeholder="00000-000" maxlength="9">
                            </div>
                            <div class="col-md-8">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" 
                                       placeholder="Rua, número">
                            </div>
                            <div class="col-md-8">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" 
                                       placeholder="Digite sua cidade">
                            </div>
                            <div class="col-md-4">
                                <label for="estado" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="estado" name="estado" 
                                       placeholder="UF" maxlength="2">
                            </div>
                        </div>
                        
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-custom btn-lg" id="register-submit">
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
                    
                    <script>
                    document.getElementById('register-form').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        const form = e.target;
                        const submitBtn = document.getElementById('register-submit');
                        const alertDiv = document.getElementById('register-alert');
                        const originalText = submitBtn.innerHTML;
                        
                        // Desabilita botão
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Criando conta...';
                        alertDiv.classList.add('d-none');
                        
                        try {
                            const response = await fetch('<?php echo BASE_URL; ?>register', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.getElementById('csrf_token').value,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    name: document.getElementById('name').value,
                                    display_name: document.getElementById('display_name').value,
                                    email: document.getElementById('email').value,
                                    password: document.getElementById('password').value,
                                    password2: document.getElementById('password2').value,
                                    cep: document.getElementById('cep').value,
                                    endereco: document.getElementById('endereco').value,
                                    cidade: document.getElementById('cidade').value,
                                    estado: document.getElementById('estado').value,
                                    csrf_token: document.getElementById('csrf_token').value
                                })
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                alertDiv.className = 'alert alert-success mb-4';
                                alertDiv.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                                alertDiv.classList.remove('d-none');
                                
                                // Limpa formulário
                                form.reset();
                                
                                // Redireciona após 2 segundos
                                setTimeout(() => {
                                    window.location.href = data.redirect || '<?php echo BASE_URL; ?>login';
                                }, 2000);
                            } else {
                                alertDiv.className = 'alert alert-danger mb-4';
                                const errorMsg = data.errors ? data.errors.join('<br>') : (data.message || 'Erro ao criar conta');
                                alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + errorMsg;
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
