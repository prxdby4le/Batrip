<?php
// Head global para todas as páginas
// Calcula automaticamente o caminho base
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '';

// Determina quantos níveis subir baseado na estrutura do projeto
if (strpos($requestUri, '/public/produtos/') !== false || strpos($scriptName, '/produtos/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/registros/') !== false || strpos($scriptName, '/registros/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/checkout/') !== false || strpos($scriptName, '/checkout/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/adm/') !== false || strpos($scriptName, '/adm/') !== false) {
    $basePath = '../../';
} elseif (strpos($requestUri, '/public/') !== false || strpos($scriptName, '/public/') !== false) {
    $basePath = '../';
} else {
    $basePath = '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Batrip'; ?></title>
    <link rel="icon" href="<?php echo $basePath; ?>assets/materials/materials/batrip%20symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $basePath; ?>assets/css/styles.css" rel="stylesheet">
</head>
