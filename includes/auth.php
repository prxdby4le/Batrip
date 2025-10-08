<?php
// Inicia buffer de saída cedo para evitar "headers already sent" por saída acidental (BOM/echo)
if (function_exists('ob_get_level') && ob_get_level() === 0) {
    ob_start();
}

// Inicia sessão de forma segura, antes de qualquer saída que dependa de headers
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Carregar configurações
    require_once __DIR__ . '/config.php';
    
    $secure = IS_PRODUCTION; // Secure cookie apenas em produção
    $domain = '';
    $path = '/';
    $lifetime = SESSION_LIFETIME;
    
    if (!headers_sent()) {
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            session_set_cookie_params($lifetime, $path . '; samesite=Lax', $domain, $secure, true);
        }
        @ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

require_once __DIR__ . '/db.php';

// Verificar timeout de sessão por inatividade
check_session_timeout();

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se o usuário está logado, false caso contrário
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Requer que o usuário esteja logado para acessar a página
 * Redireciona para login se não estiver autenticado
 * 
 * @param string $redirect Caminho para a página de login
 * @return void
 */
function require_login($redirect = 'registros/login.php') {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Autentica um usuário com email e senha
 * Regenera o ID de sessão para prevenir fixação
 * 
 * @param string $email Email do usuário
 * @param string $password Senha em texto plano (será verificada com hash)
 * @return bool True se autenticado com sucesso, false caso contrário
 */
function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        // Mitiga fixação de sessão
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

/**
 * Faz logout do usuário destruindo a sessão
 * Regenera o ID de sessão antes de destruir
 * 
 * @return void
 */
function logout() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Regenera ID antes de encerrar sessão para mitigar reutilização
        session_regenerate_id(true);
    }
    session_destroy();
}

/**
 * Registra um novo usuário no sistema
 * 
 * @param string $name Nome completo do usuário
 * @param string $email Email (será convertido para lowercase)
 * @param string $password Senha em texto plano (será hasheada)
 * @param string $endereco Endereço completo (opcional)
 * @param string $cidade Cidade (opcional)
 * @param string $estado Estado - 2 letras (opcional, será convertido para uppercase)
 * @param string $cep CEP - apenas números (opcional, formatação automática)
 * @param string $display_name Nome de exibição/apelido (opcional)
 * @return bool True se registrado com sucesso, false se email/display_name já existe
 * @throws PDOException Se ocorrer erro não relacionado a unicidade
 */
function register($name, $email, $password, $endereco = '', $cidade = '', $estado = '', $cep = '', $display_name = '') {
    global $pdo;
    $name = trim((string)$name);
    $display_name = trim((string)$display_name);
    $email = strtolower(trim((string)$email));
    $endereco = trim((string)$endereco);
    $cidade = trim((string)$cidade);
    $estado = strtoupper(substr(trim((string)$estado), 0, 2));
    $cep = preg_replace('/\D/', '', (string)$cep);

    $hash = password_hash((string)$password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, display_name, email, password, endereco, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$name, $display_name, $email, $hash, $endereco, $cidade, $estado, $cep]);
    } catch (PDOException $e) {
        // Violação de integridade (e-mail ou display_name único)
        if ($e->getCode() === '23000') {
            return false;
        }
        throw $e;
    }
}

/**
 * Verifica se o usuário atual é administrador
 * 
 * @return bool True se for admin, false caso contrário ou se coluna não existir
 */
function is_admin() {
    if (!is_logged_in()) return false;
    try {
        global $pdo;
        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        if (!$row) return false;
        // Alguns bancos podem retornar null se coluna não existir
        return !empty($row['is_admin']);
    } catch (Throwable $e) {
        // Coluna pode não existir ainda; por segurança, não tratar como admin
        return false;
    }
}

/**
 * Requer que o usuário seja administrador
 * Redireciona para login se não estiver autenticado
 * Exibe erro 403 se autenticado mas não for admin
 * 
 * @param string $redirect Caminho para a página de login
 * @return void
 */
function require_admin($redirect = 'registros/login.php') {
    require_login($redirect);
    if (!is_admin()) {
        // 403 simples
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Acesso negado</title></head><body style="font-family:sans-serif;background:#111;color:#eee;padding:2rem;">';
        echo '<h1>403 • Acesso negado</h1><p>Você não tem permissão para acessar esta área.</p>';
        echo '<p><a href="../index.php" style="color:#6cf;">Voltar</a></p>';
        echo '</body></html>';
        exit;
    }
}

/**
 * Gera ou retorna o token CSRF da sessão atual
 * Cria um novo token de 64 caracteres se não existir
 * 
 * @return string Token CSRF hexadecimal (64 caracteres)
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica se o token CSRF fornecido é válido
 * Usa hash_equals para prevenir timing attacks
 * 
 * @param string $token Token a ser verificado
 * @return bool True se válido, false caso contrário
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Verifica e aplica timeout de sessão por inatividade
 * Se usuário estiver inativo por mais de SESSION_TIMEOUT segundos, destrói a sessão
 */
function check_session_timeout() {
    if (!defined('SESSION_TIMEOUT')) {
        return; // Configuração não carregada
    }
    
    // Verificar se há usuário logado
    if (!isset($_SESSION['user_id'])) {
        return; // Não há usuário logado, não aplicar timeout
    }
    
    $currentTime = time();
    
    // Primeira visita - inicializar timestamp
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = $currentTime;
        $_SESSION['session_created'] = $currentTime;
        return;
    }
    
    // Calcular tempo de inatividade
    $inactiveTime = $currentTime - $_SESSION['last_activity'];
    
    // Se excedeu timeout, destruir sessão
    if ($inactiveTime > SESSION_TIMEOUT) {
        require_once __DIR__ . '/logger.php';
        log_info("Sessão expirada por inatividade", [
            'user_id' => $_SESSION['user_id'],
            'inactive_seconds' => $inactiveTime,
            'last_activity' => date('Y-m-d H:i:s', $_SESSION['last_activity'])
        ], 'auth');
        
        // Salvar mensagem antes de destruir sessão
        $message = MSG_ERROR_SESSION_EXPIRED ?? 'Sua sessão expirou. Faça login novamente.';
        
        session_unset();
        session_destroy();
        session_start();
        
        $_SESSION['session_expired'] = true;
        $_SESSION['error_message'] = $message;
        
        // Redirecionar para login se não estiver em página pública
        $publicPages = ['login.php', 'register.php', 'index.php'];
        $currentPage = basename($_SERVER['PHP_SELF']);
        
        if (!in_array($currentPage, $publicPages)) {
            header('Location: /registros/login.php');
            exit;
        }
        
        return;
    }
    
    // Atualizar timestamp de última atividade
    $_SESSION['last_activity'] = $currentTime;
    
    // Verificar tempo total da sessão (SESSION_LIFETIME)
    if (isset($_SESSION['session_created'])) {
        $sessionAge = $currentTime - $_SESSION['session_created'];
        
        // Se sessão está muito antiga, regenerar ID
        if ($sessionAge > (SESSION_LIFETIME / 2)) {
            session_regenerate_id(true);
            $_SESSION['session_created'] = $currentTime;
        }
    }
}

/**
 * Obtém tempo restante de sessão em segundos
 * @return int Segundos até expiração
 */
function get_session_time_remaining() {
    if (!isset($_SESSION['last_activity']) || !defined('SESSION_TIMEOUT')) {
        return SESSION_TIMEOUT ?? 1800;
    }
    
    $elapsed = time() - $_SESSION['last_activity'];
    $remaining = SESSION_TIMEOUT - $elapsed;
    
    return max(0, $remaining);
}
