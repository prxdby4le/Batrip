<?php
// Include para scripts JavaScript comuns
// Determina o caminho base baseado na localização do arquivo
$currentPath = $_SERVER['PHP_SELF'];
$levels = substr_count(str_replace('\\', '/', $currentPath), '/') - 2; // -2 para contar a partir de /public/
$basePath = str_repeat('../', $levels);

// Se não há níveis, estamos em /public/ então usamos ../ para sair da pasta public
if ($levels <= 0) {
    $basePath = '../';
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/script.js"></script>
