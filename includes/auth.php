<?php
// Inicia buffer de saída cedo para evitar "headers already sent" por saída acidental (BOM/echo)
if (function_exists('ob_get_level') && ob_get_level() === 0) {
    ob_start();
}

// Inicia sessão de forma segura, antes de qualquer saída que dependa de headers
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = false; // Docker local: sempre false
    $domain = '';
    $path = '/';
    $lifetime = 7200; // 2 horas
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

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login($redirect = 'registros/login.php') {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect);
        exit;
    }
}

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

function logout() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Regenera ID antes de encerrar sessão para mitigar reutilização
        session_regenerate_id(true);
    }
    session_destroy();
}

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

// Verifica se usuário atual é admin (coluna is_admin = 1). Retorna false se coluna não existir.
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

// CSRF helpers
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}
