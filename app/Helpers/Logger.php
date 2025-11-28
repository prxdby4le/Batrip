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
    private static string $logDir = '';
    
    /**
     * Inicializa o diretório de logs
     *
     * @return string
     */
    private static function getLogDir(): string
    {
        if (empty(self::$logDir)) {
            // Detecta ROOT_PATH se não estiver definido
            $rootPath = null;
            
            if (defined('ROOT_PATH')) {
                $rootPath = ROOT_PATH;
            } else {
                // Tenta detectar o ROOT_PATH
                $possibleRoots = [
                    dirname(dirname(__DIR__)),  // app/Helpers/../../
                    dirname(__DIR__, 3),        // Alternativa
                    '/var/www/html',            // Docker
                ];
                
                foreach ($possibleRoots as $root) {
                    if (is_dir($root) && (file_exists($root . '/public/index-mvc.php') || file_exists($root . '/config/config.php'))) {
                        $rootPath = $root;
                        break;
                    }
                }
                
                // Se não encontrou, usa o padrão
                if (!$rootPath) {
                    $rootPath = dirname(dirname(__DIR__));
                }
            }
            
            // Usa ROOT_PATH para definir o diretório de logs
            self::$logDir = rtrim($rootPath, '/') . '/logs/';
        }
        
        return self::$logDir;
    }

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
        $logDir = self::getLogDir();
        
        // Garante que o diretório existe e tem permissões corretas
        if (!is_dir($logDir)) {
            // Tenta criar o diretório com permissões adequadas
            if (!@mkdir($logDir, 0775, true)) {
                // Se falhar, tenta usar error_log do PHP como fallback
                $timestamp = date('Y-m-d H:i:s');
                $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
                error_log("[{$timestamp}] [{$level}] {$message}{$contextStr}");
                return false;
            }
        }

        // Verifica se o diretório é gravável
        if (!is_writable($logDir)) {
            // Tenta ajustar permissões
            @chmod($logDir, 0775);
            
            // Se ainda não for gravável, usa error_log como fallback
            if (!is_writable($logDir)) {
                $timestamp = date('Y-m-d H:i:s');
                $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
                error_log("[{$timestamp}] [{$level}] {$message}{$contextStr}");
                return false;
            }
        }

        // Nome do arquivo com data
        $filename = $logDir . date('Y-m-d') . '.log';

        // Formata mensagem
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Escreve no arquivo (suprime warnings se falhar)
        $result = @file_put_contents($filename, $logMessage, FILE_APPEND);
        
        // Se falhar, usa error_log como fallback
        if ($result === false) {
            error_log("[{$timestamp}] [{$level}] {$message}{$contextStr}");
            return false;
        }
        
        return true;
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
        $logDir = self::getLogDir();
        
        if (!is_dir($logDir)) {
            return 0;
        }

        $files = glob($logDir . '*.log');
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
