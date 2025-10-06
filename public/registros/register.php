<?php
$pageTitle = 'Registrar | Batrip';
include '../../includes/head.php';
include '../../includes/auth.php';

$error = '';
$success = '';

// Redirecionar se já estiver logado
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

// Valores dos campos para manter após erro
$name = trim($_POST['name'] ?? '');
$display_name = trim($_POST['display_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$estado = trim($_POST['estado'] ?? '');

// Processar formulário de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token de segurança inválido. Tente novamente.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password2'] ?? '');
        
        // Validações
        if (empty($name) || empty($display_name) || empty($email) || empty($password) || empty($password2)) {
            $error = 'Por favor, preencha todos os campos obrigatórios.';
        } elseif (strlen($name) < 2) {
            $error = 'Nome deve ter pelo menos 2 caracteres.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\.]{3,32}$/', $display_name)) {
            $error = 'Nome de usuário deve ter entre 3 e 32 caracteres e usar apenas letras, números, _ ou ponto.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido.';
        } elseif (strlen($password) < 6) {
            $error = 'Senha deve ter pelo menos 6 caracteres.';
        } elseif ($password !== $password2) {
            $error = 'As senhas não coincidem.';
        } elseif (!empty($estado) && strlen($estado) !== 2) {
            $error = 'Estado deve ter 2 caracteres.';
        } elseif (!empty($cep) && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
            $error = 'CEP inválido.';
        } else {
            // Tentar registrar
            $cepClean = preg_replace('/\D/', '', $cep);
            if (register($name, $email, $password, $endereco, $cidade, $estado, $cepClean, $display_name)) {
                $success = 'Cadastro realizado com sucesso! Você já pode fazer login.';
                // Limpar campos
                $name = $display_name = $email = $cep = $endereco = $cidade = $estado = '';
            } else {
                $error = 'Email ou nome de usuário já estão em uso. Tente outros valores.';
            }
        }
    }
}
?>
<?php include '../../includes/head.php'; ?>
<?php include '../../includes/nav.php'; ?>
<body>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 col-lg-5 custom-form shadow">
            <h2 class="section-title mb-4"><i class="fas fa-user-plus me-2"></i>Criar Conta</h2>
            
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
            
            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="registerName" class="form-label"><i class="fas fa-user me-2"></i>Nome Completo</label>
                        <input type="text" class="form-control" id="registerName" name="name" placeholder="Digite seu nome completo" required value="<?= htmlspecialchars($name) ?>">
                    </div>
                    <div class="col-12">
                        <label for="registerDisplayName" class="form-label"><i class="fas fa-at me-2"></i>Nome de Usuário</label>
                        <input type="text" class="form-control" id="registerDisplayName" name="display_name" placeholder="Escolha seu nome de usuário" required pattern="[a-zA-Z0-9_\.]{3,32}" title="Entre 3 e 32 caracteres. Letras, números, underline ou ponto." value="<?= htmlspecialchars($display_name) ?>">
                        <div class="form-text">Entre 3 e 32 caracteres. Use apenas letras, números, _ ou .</div>
                    </div>
                    <div class="col-12">
                        <label for="registerEmail" class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Digite seu email" required value="<?= htmlspecialchars($email) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="registerPassword" class="form-label"><i class="fas fa-lock me-2"></i>Senha</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Digite sua senha" required minlength="6">
                        <div class="form-text">Mínimo de 6 caracteres</div>
                    </div>
                    <div class="col-md-6">
                        <label for="registerPassword2" class="form-label"><i class="fas fa-lock me-2"></i>Confirmar Senha</label>
                        <input type="password" class="form-control" id="registerPassword2" name="password2" placeholder="Confirme sua senha" required minlength="6">
                    </div>
                </div>
                
                <hr class="my-4">
                <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Endereço (opcional)</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="registerCep" class="form-label"><i class="fas fa-mail-bulk me-2"></i>CEP</label>
                        <input type="text" class="form-control" id="registerCep" name="cep" placeholder="00000-000" maxlength="9" value="<?= htmlspecialchars($cep) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-outline-light w-100" type="button" id="btn-buscar-cep">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <label for="registerEndereco" class="form-label"><i class="fas fa-home me-2"></i>Endereço</label>
                        <input type="text" class="form-control" id="registerEndereco" name="endereco" placeholder="Rua, número" value="<?= htmlspecialchars($endereco) ?>">
                    </div>
                    <div class="col-md-8">
                        <label for="registerCidade" class="form-label"><i class="fas fa-city me-2"></i>Cidade</label>
                        <input type="text" class="form-control" id="registerCidade" name="cidade" placeholder="Digite sua cidade" value="<?= htmlspecialchars($cidade) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="registerEstado" class="form-label"><i class="fas fa-flag me-2"></i>Estado</label>
                        <input type="text" class="form-control" id="registerEstado" name="estado" placeholder="UF" maxlength="2" value="<?= htmlspecialchars($estado) ?>">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-custom w-100">
                        <i class="fas fa-user-plus me-2"></i>Criar Conta
                    </button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="registros/login.php" class="footer-link">
                    <i class="fas fa-sign-in-alt me-1"></i>Já tem uma conta? Fazer login
                </a>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
    <script>
    (function(){
        // Máscara e busca automática de CEP
        const cepInput = document.getElementById('registerCep');
        const enderecoInput = document.getElementById('registerEndereco');
        const cidadeInput = document.getElementById('registerCidade');
        const estadoInput = document.getElementById('registerEstado');
        const btnBuscar = document.getElementById('btn-buscar-cep');
        
        // Máscara de CEP
        if (cepInput) {
            cepInput.addEventListener('input', function(){
                let value = this.value.replace(/\D/g, '');
                if (value.length <= 5) {
                    this.value = value;
                } else {
                    this.value = value.substring(0, 5) + '-' + value.substring(5, 8);
                }
            });
        }
        
        // Função para buscar CEP
        async function buscarCEP() {
            if (!cepInput) return;
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                alert('CEP deve ter 8 dígitos');
                return;
            }
            
            btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    alert('CEP não encontrado');
                    return;
                }
                
                if (enderecoInput) enderecoInput.value = data.logradouro || '';
                if (cidadeInput) cidadeInput.value = data.localidade || '';
                if (estadoInput) estadoInput.value = data.uf || '';
                
            } catch (error) {
                alert('Erro ao buscar CEP');
            } finally {
                btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
            }
        }
        
        if (btnBuscar) {
            btnBuscar.addEventListener('click', buscarCEP);
        }
        
        // Auto buscar quando CEP estiver completo
        if (cepInput) {
            cepInput.addEventListener('blur', function(){
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    buscarCEP();
                }
            });
        }
        
        // Validação de confirmação de senha
        const password = document.getElementById('registerPassword');
        const password2 = document.getElementById('registerPassword2');
        
        if (password && password2) {
            function validatePasswords() {
                if (password.value && password2.value && password.value !== password2.value) {
                    password2.setCustomValidity('As senhas não coincidem');
                } else {
                    password2.setCustomValidity('');
                }
            }
            
            password.addEventListener('change', validatePasswords);
            password2.addEventListener('change', validatePasswords);
        }
        
        // Formatação do estado para maiúsculo
        if (estadoInput) {
            estadoInput.addEventListener('input', function(){
                this.value = this.value.toUpperCase().substring(0, 2);
            });
        }
    })();
    </script>
</body>
</html>


