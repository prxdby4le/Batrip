<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * AuthController - Autenticação e registro
 */
class AuthController extends Controller
{
    private User $userModel;
    
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
        $this->userModel = new User();
    }
    
    /**
     * Formulário de login
     */
    public function loginForm(): void
    {
        // Se já está logado, redireciona
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
        $data = [
            'pageTitle' => 'Login - Batrip',
            'layout' => 'auth'
        ];
        
        $this->view('auth.login', $data);
    }
    
    /**
     * Processa login
     */
    public function login(): void
    {
        // Limpa output buffer para JSON
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        if (!$this->request->isPost()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }
        
        // Tenta obter dados do JSON primeiro, depois do POST tradicional
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $email = $jsonData['email'] ?? $this->request->post('email') ?? '';
        $password = $jsonData['password'] ?? $this->request->post('password') ?? '';
        $csrfToken = $jsonData['csrf_token'] ?? $this->request->post('csrf_token') ?? $this->request->header('X-CSRF-Token') ?? '';
        
        // Valida CSRF token
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(dirname(__DIR__)));
        }
        require_once ROOT_PATH . '/includes/auth.php';
        if (!verify_csrf_token($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
            exit;
        }
        
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
            exit;
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Email ou senha inválidos']);
            exit;
        }
        
        // Define sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = (bool) $user['is_admin'];
        $_SESSION['last_activity'] = time();
        
        // Atualiza último login
        $this->userModel->updateLastLogin($user['id']);
        
        // Redireciona para página anterior ou home
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
        unset($_SESSION['redirect_after_login']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'redirect' => $redirect
        ]);
        exit;
    }
    
    /**
     * Formulário de registro
     */
    public function registerForm(): void
    {
        // Se já está logado, redireciona
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
        $data = [
            'pageTitle' => 'Criar Conta - Batrip',
            'layout' => 'auth'
        ];
        
        $this->view('auth.register', $data);
    }
    
    /**
     * Processa registro
     */
    public function register(): void
    {
        // Limpa output buffer para JSON
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        if (!$this->request->isPost()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }
        
        // Tenta obter dados do JSON primeiro, depois do POST tradicional
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $name = $jsonData['name'] ?? $this->request->post('name') ?? '';
        $display_name = $jsonData['display_name'] ?? $this->request->post('display_name') ?? '';
        $email = $jsonData['email'] ?? $this->request->post('email') ?? '';
        $password = $jsonData['password'] ?? $this->request->post('password') ?? '';
        $password2 = $jsonData['password2'] ?? $this->request->post('password2') ?? '';
        $cep = $jsonData['cep'] ?? $this->request->post('cep') ?? '';
        $endereco = $jsonData['endereco'] ?? $this->request->post('endereco') ?? '';
        $cidade = $jsonData['cidade'] ?? $this->request->post('cidade') ?? '';
        $estado = $jsonData['estado'] ?? $this->request->post('estado') ?? '';
        $csrfToken = $jsonData['csrf_token'] ?? $this->request->post('csrf_token') ?? $this->request->header('X-CSRF-Token') ?? '';
        
        // Valida CSRF token
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(dirname(__DIR__)));
        }
        require_once ROOT_PATH . '/includes/auth.php';
        if (!verify_csrf_token($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
            exit;
        }
        
        // Validações
        $errors = [];
        
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Nome deve ter pelo menos 2 caracteres';
        }
        
        if (empty($display_name) || !preg_match('/^[a-zA-Z0-9_\.]{3,32}$/', $display_name)) {
            $errors[] = 'Nome de usuário deve ter entre 3 e 32 caracteres e usar apenas letras, números, _ ou ponto';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if ($password !== $password2) {
            $errors[] = 'As senhas não coincidem';
        }
        
        if (!empty($estado) && strlen($estado) !== 2) {
            $errors[] = 'Estado deve ter 2 caracteres';
        }
        
        if (!empty($cep) && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
            $errors[] = 'CEP inválido';
        }
        
        // Verifica se email já existe
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email já cadastrado';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(' | ', $errors), 'errors' => $errors]);
            exit;
        }
        
        // Limpa CEP
        $cepClean = preg_replace('/\D/', '', $cep);
        
        // Cria usuário usando função register do includes/auth.php
        // ROOT_PATH já foi definido acima na validação CSRF
        $registered = register($name, $email, $password, $endereco, $cidade, $estado, $cepClean, $display_name);
        
        if ($registered) {
            echo json_encode([
                'success' => true,
                'message' => 'Conta criada com sucesso! Você já pode fazer login.',
                'redirect' => '/login'
            ]);
        } else {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email ou nome de usuário já estão em uso']);
        }
        exit;
    }
    
    /**
     * Logout
     */
    public function logout(): void
    {
        // Destroi sessão
        session_destroy();
        session_start();
        
        $_SESSION['success'] = 'Logout realizado com sucesso!';
        
        $this->redirect('/');
    }
}
