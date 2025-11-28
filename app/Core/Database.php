<?php
/**
 * Database Singleton Class
 * 
 * Gerencia a conexão com o banco de dados usando padrão Singleton
 * 
 * @category Core
 * @package  Batrip
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    /**
     * Instância única da classe
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Conexão PDO
     *
     * @var PDO
     */
    private PDO $connection;

    /**
     * Construtor privado (Singleton)
     */
    private function __construct()
    {
        // Tenta usar conexão global se existir
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $this->connection = $GLOBALS['pdo'];
            return;
        }

        // Configuração automática para Docker ou ambiente local/WSL
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

        // Retry automático para ambientes Docker
        $maxTries = 10;
        $tries = 0;
        while (true) {
            try {
                $this->connection = new PDO($dsn, $user, $pass, $options);
                // Garantir que $pdo esteja disponível globalmente
                $GLOBALS['pdo'] = $this->connection;
                break;
            } catch (PDOException $e) {
                $tries++;
                if ($tries >= $maxTries) {
                    error_log("Batrip DB Error: " . $e->getMessage());
                    throw new \Exception('Erro ao conectar ao banco de dados. Verifique as configurações.');
                }
                sleep(2);
            }
        }
    }

    /**
     * Obtém a instância única
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtém a conexão PDO
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Previne clonagem
     */
    private function __clone() {}

    /**
     * Previne unserialize
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

