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
        if (!$this->request->isPost()) {
            $this->redirect('/login');
            return;
        }
        
        $email = $this->request->post('email');
        $password = $this->request->post('password');
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Preencha todos os campos';
            $this->redirect('/login');
            return;
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Email ou senha inválidos';
            $this->redirect('/login');
            return;
        }
        
        // Define sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = (bool) $user['is_admin'];
        $_SESSION['last_activity'] = time();
        
        // Atualiza último login
        $this->userModel->updateLastLogin($user['id']);
        
        $_SESSION['success'] = 'Login realizado com sucesso!';
        
        // Redireciona para página anterior ou home
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
        unset($_SESSION['redirect_after_login']);
        
        $this->redirect($redirect);
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
        if (!$this->request->isPost()) {
            $this->redirect('/register');
            return;
        }
        
        $name = $this->request->post('name');
        $email = $this->request->post('email');
        $password = $this->request->post('password');
        $passwordConfirm = $this->request->post('password_confirm');
        
        // Validações
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Senhas não conferem';
        }
        
        // Verifica se email já existe
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email já cadastrado';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/register');
            return;
        }
        
        // Cria usuário
        $userId = $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        
        if ($userId) {
            $_SESSION['success'] = 'Conta criada com sucesso! Faça login.';
            $this->redirect('/login');
        } else {
            $_SESSION['error'] = 'Erro ao criar conta. Tente novamente.';
            $this->redirect('/register');
        }
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
