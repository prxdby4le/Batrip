<?php
/**
 * Sistema de Logging Estruturado - Batrip E-commerce
 * 
 * Fornece funções para log com níveis, contexto e formatação JSON.
 */

require_once __DIR__ . '/config.php';

/**
 * Níveis de log disponíveis
 */
class LogLevel {
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';
}

/**
 * Registra um evento no log com estrutura JSON
 * 
 * @param string $level Nível do log (use constantes LogLevel::*)
 * @param string $message Mensagem descritiva
 * @param array $context Dados adicionais de contexto
 * @param string $category Categoria do log (cart, auth, product, etc)
 */
function log_event($level, $message, $context = [], $category = 'general') {
    // Verificar se deve logar baseado no nível configurado
    if (!should_log($level)) {
        return;
    }
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'datetime_iso' => date('c'),
        'level' => $level,
        'category' => $category,
        'message' => $message,
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_email' => $_SESSION['user_email'] ?? null,
        'session_id' => session_id(),
        'ip' => get_client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'context' => $context,
        'memory_usage' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true),
    ];
    
    // Adicionar stack trace apenas para erros críticos
    if ($level === LogLevel::ERROR || $level === LogLevel::CRITICAL) {
        $logData['stack_trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    }
    
    $logJson = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Log para arquivo PHP error_log
    error_log("[BATRIP] " . $logJson);
    
    // Opcionalmente, salvar em arquivo dedicado
    if (defined('LOG_DIR') && is_dir(LOG_DIR) && is_writable(LOG_DIR)) {
        $logFile = LOG_DIR . 'batrip_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logJson . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Verifica se deve logar baseado no nível configurado
 */
function should_log($level) {
    $levels = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::WARNING => 2,
        LogLevel::ERROR => 3,
        LogLevel::CRITICAL => 4,
    ];
    
    $currentLevel = defined('LOG_LEVEL') ? LOG_LEVEL : 'INFO';
    $configuredPriority = $levels[$currentLevel] ?? 1;
    $requestedPriority = $levels[$level] ?? 0;
    
    return $requestedPriority >= $configuredPriority;
}

/**
 * Obtém o IP real do cliente (considera proxies)
 */
function get_client_ip() {
    $ip = 'Unknown';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

/**
 * Atalhos para níveis de log específicos
 */

function log_debug($message, $context = [], $category = 'general') {
    log_event(LogLevel::DEBUG, $message, $context, $category);
}

function log_info($message, $context = [], $category = 'general') {
    log_event(LogLevel::INFO, $message, $context, $category);
}

function log_warning($message, $context = [], $category = 'general') {
    log_event(LogLevel::WARNING, $message, $context, $category);
}

function log_error($message, $context = [], $category = 'general') {
    log_event(LogLevel::ERROR, $message, $context, $category);
}

function log_critical($message, $context = [], $category = 'general') {
    log_event(LogLevel::CRITICAL, $message, $context, $category);
}

/**
 * Logs específicos por categoria
 */

function log_cart_action($action, $productId, $details = []) {
    log_info("Cart action: $action", array_merge([
        'action' => $action,
        'product_id' => $productId,
    ], $details), 'cart');
}

function log_auth_action($action, $userId = null, $details = []) {
    log_info("Auth action: $action", array_merge([
        'action' => $action,
        'user_id' => $userId,
    ], $details), 'auth');
}

function log_product_action($action, $productId, $details = []) {
    log_info("Product action: $action", array_merge([
        'action' => $action,
        'product_id' => $productId,
    ], $details), 'product');
}

function log_order_action($action, $orderId, $details = []) {
    log_info("Order action: $action", array_merge([
        'action' => $action,
        'order_id' => $orderId,
    ], $details), 'order');
}

/**
 * Log de exceções
 */
function log_exception(Exception $e, $context = []) {
    log_error($e->getMessage(), array_merge([
        'exception_class' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
    ], $context), 'exception');
}

/**
 * Log de performance (tempo de execução)
 */
function log_performance($operation, $startTime, $details = []) {
    $duration = microtime(true) - $startTime;
    log_info("Performance: $operation", array_merge([
        'operation' => $operation,
        'duration_ms' => round($duration * 1000, 2),
        'duration_s' => round($duration, 4),
    ], $details), 'performance');
}

