<?php
/**
 * Autoloader PSR-4
 * 
 * Carrega automaticamente classes do namespace App\
 */

spl_autoload_register(function ($class) {
    // Namespace base do projeto
    $prefix = 'App\\';
    
    // Diretório base das classes
    $base_dir = __DIR__ . '/app/';
    
    // Verifica se a classe usa o namespace base
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Não é do nosso namespace, próximo autoloader
        return;
    }
    
    // Pega o nome relativo da classe
    $relative_class = substr($class, $len);
    
    // Converte namespace para caminho de arquivo
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Se o arquivo existe, carrega
    if (file_exists($file)) {
        require $file;
    }
});
