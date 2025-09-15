<?php
require_once '../../includes/auth.php';

// Fazer logout
logout();

// Redirecionar para página inicial com mensagem
$_SESSION['logout_success'] = 'Você saiu com sucesso. Volte sempre!';
header('Location: ../index.php');
exit;
?>