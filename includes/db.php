<?php
// Configuração automática para Docker ou ambiente local
$isDocker = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'docker' || 
            getenv('APP_ENV') === 'docker' || 
            file_exists('/.dockerenv');

if ($isDocker) {
    // Configurações para Docker
    $host = 'db';
    $db   = 'batrip';
    $user = 'batrip_user';
    $pass = 'batrip_pass_2024';
} else {
    // Configurações para ambiente local
    $host = 'localhost';
    $db   = 'batrip';
    $user = 'root';
    $pass = '';
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Batrip DB Error: " . $e->getMessage());
    die('Erro ao conectar ao banco de dados. Verifique as configurações.');
}
