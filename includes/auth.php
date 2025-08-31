<?php
// Inicia sessão de forma segura, antes de qualquer saída
if (session_status() === PHP_SESSION_NONE) {
    // só inicia sessão se headers ainda não foram enviados
    if (!headers_sent()) {
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

function register($name, $email, $password, $endereco = '', $cidade = '', $estado = '', $cep = '') {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, endereco, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?)');
    return $stmt->execute([$name, $email, $hash, $endereco, $cidade, $estado, $cep]);
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
