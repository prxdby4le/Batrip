<?php
// Head global para todas as páginas
// Determina o caminho base baseado na localização do arquivo
$currentPath = $_SERVER['PHP_SELF'];
$levels = substr_count(str_replace('\\', '/', $currentPath), '/') - 2; // -2 para contar a partir de /public/
$basePath = str_repeat('../', $levels);

// Se não há níveis, estamos em /public/ então usamos ../ para sair da pasta public
if ($levels <= 0) {
    $basePath = '../';
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
