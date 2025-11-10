<?php
/**
 * Logger Helper
 * 
 * Sistema simples de logging para o projeto
 * 
 * @category Helpers
 * @package  Batrip
 */

namespace App\Helpers;

class Logger
{
    /**
     * Diretório de logs
     *
     * @var string
     */
    private static string $logDir = __DIR__ . '/../../logs/';

    /**
     * Níveis de log
     */
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';

    /**
     * Escreve log
     *
     * @param  string $level Nível do log
     * @param  string $message Mensagem
     * @param  array  $context Dados adicionais
     * @return bool
     */
    private static function write(string $level, string $message, array $context = []): bool
    {
        // Cria diretório se não existir
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        // Nome do arquivo com data
        $filename = self::$logDir . date('Y-m-d') . '.log';

        // Formata mensagem
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Escreve no arquivo
        return file_put_contents($filename, $logMessage, FILE_APPEND) !== false;
    }

    /**
     * Log de erro
     *
     * @param  string $message
     * @param  array  $context
     * @return bool
     */
    public static function error(string $message, array $context = []): bool
    {
        return self::write(self::ERROR, $message, $context);
    }

    /**
     * Log de aviso
     *
     * @param  string $message
     * @param  array  $context
     * @return bool
     */
    public static function warning(string $message, array $context = []): bool
    {
        return self::write(self::WARNING, $message, $context);
    }

    /**
     * Log de informação
     *
     * @param  string $message
     * @param  array  $context
     * @return bool
     */
    public static function info(string $message, array $context = []): bool
    {
        return self::write(self::INFO, $message, $context);
    }

    /**
     * Log de debug
     *
     * @param  string $message
     * @param  array  $context
     * @return bool
     */
    public static function debug(string $message, array $context = []): bool
    {
        return self::write(self::DEBUG, $message, $context);
    }

    /**
     * Log de ação do usuário
     *
     * @param  string $action Ação realizada
     * @param  int    $userId ID do usuário
     * @param  array  $details Detalhes
     * @return bool
     */
    public static function userAction(string $action, int $userId, array $details = []): bool
    {
        $context = array_merge(['user_id' => $userId], $details);
        return self::info("User Action: {$action}", $context);
    }

    /**
     * Log de acesso ao admin
     *
     * @param  int    $userId
     * @param  string $page
     * @return bool
     */
    public static function adminAccess(int $userId, string $page): bool
    {
        return self::info("Admin Access: {$page}", ['user_id' => $userId]);
    }

    /**
     * Limpa logs antigos (mantém últimos N dias)
     *
     * @param  int $days Dias para manter
     * @return int Arquivos removidos
     */
    public static function cleanup(int $days = 30): int
    {
        if (!is_dir(self::$logDir)) {
            return 0;
        }

        $files = glob(self::$logDir . '*.log');
        $removed = 0;
        $cutoffTime = time() - ($days * 86400);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $removed++;
                }
            }
        }

        return $removed;
    }
}
