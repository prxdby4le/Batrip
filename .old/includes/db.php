<?php
// Configuração automática para Docker ou ambiente local/WSL, com override por variáveis de ambiente
$isDocker = (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'docker')
    || (getenv('APP_ENV') === 'docker')
    || file_exists('/.dockerenv');

// Helper para pegar primeira variável de ambiente disponível
$env = function ($keys, $default = null) {
    foreach ((array)$keys as $k) {
        $v = getenv($k);
        if ($v !== false && $v !== '') {
            return $v;
        }
        if (isset($_ENV[$k]) && $_ENV[$k] !== '') {
            return $_ENV[$k];
        }
    }
    return $default;
};

// Defaults sensatos e overrides por ENV
$host = $env(['DB_HOST', 'MYSQL_HOST'], $isDocker ? 'db' : 'localhost');
$port = (int)$env(['DB_PORT', 'MYSQL_PORT'], 3306);
$db   = $env(['DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE'], 'batrip');
$user = $env(['DB_USER', 'DB_USERNAME', 'MYSQL_USER'], $isDocker ? 'batrip_user' : 'root');
$pass = $env(['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASSWORD'], $isDocker ? 'batrip_pass_2024' : '');

$charset = 'utf8mb4';
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

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
