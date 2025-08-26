<?php
// Função para iniciar sessão de forma segura
function safe_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Iniciar sessão automaticamente
safe_session_start();

require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login($redirect = '/Batrip/login.php') {
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
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
}

function register($name, $email, $password, $endereco = '', $cidade = '', $estado = '', $cep = '') {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, endereco, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?)');
    return $stmt->execute([$name, $email, $hash, $endereco, $cidade, $estado, $cep]);
}
