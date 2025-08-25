<?php
// Include para formulários de autenticação
// Recebe variáveis: $formTitle, $formAction, $submitText, $showRegisterLink, $showLoginLink
?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5 custom-form shadow">
        <h2 class="section-title mb-4"><?php echo $formTitle ?? 'Formulário'; ?></h2>
        
        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo strpos($msg, 'sucesso') !== false ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" <?php echo $formAction ? 'action="' . $formAction . '"' : ''; ?> autocomplete="off">
            <?php echo $formContent ?? ''; ?>
            <button type="submit" class="btn btn-custom w-100"><?php echo $submitText ?? 'Enviar'; ?></button>
        </form>
        
        <div class="text-center mt-3">
            <?php if ($showRegisterLink ?? false): ?>
                <p><a href="register.php" class="text-decoration-none" style="color: var(--accent-red);">Não tem conta? Cadastre-se aqui</a></p>
            <?php endif; ?>
            
            <?php if ($showLoginLink ?? false): ?>
                <p><a href="login.php" class="text-decoration-none" style="color: var(--accent-red);">Já tem conta? Faça login aqui</a></p>
            <?php endif; ?>
            
            <?php if ($showForgotPassword ?? false): ?>
                <p><a href="redefinir-senha.php" class="text-decoration-none" style="color: var(--text-gray);">Esqueceu sua senha?</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>
