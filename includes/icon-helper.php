<?php
/**
 * Helper para renderizar ícones SVG
 * 
 * Uso: icon('shopping-cart', 'icon-lg me-2')
 */
function icon($name, $class = 'icon') {
    // Determinar o caminho base correto
    global $basePath;
    $base = $basePath ?? '../';
    
    // Ajustar caminho baseado na estrutura
    $iconPath = $base . 'assets/img/icons/' . $name . '.svg';
    
    // Ler o arquivo SVG
    $svgFile = __DIR__ . '/../assets/img/icons/' . $name . '.svg';
    
    if (file_exists($svgFile)) {
        $svg = file_get_contents($svgFile);
        // Adicionar classes ao SVG
        $svg = str_replace('<svg', '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '"', $svg);
        return $svg;
    }
    
    // Fallback: retorna um span vazio se o ícone não existir
    return '<span class="' . htmlspecialchars($class, ENT_QUOTES) . '"></span>';
}

/**
 * Renderiza um ícone SVG diretamente
 */
function render_icon($name, $class = 'icon') {
    echo icon($name, $class);
}
