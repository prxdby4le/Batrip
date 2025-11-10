<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Order;

/**
 * ProfileController - Gerenciamento de perfil do usuário
 */
class ProfileController extends Controller
{
    private User $userModel;
    private Order $orderModel;
    
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
        $this->userModel = new User();
        $this->orderModel = new Order();
    }
    
    /**
     * Exibe perfil do usuário
     */
    public function index(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/perfil';
            $_SESSION['error'] = 'Você precisa estar logado para acessar seu perfil';
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/');
            return;
        }
        
        // Buscar pedidos do usuário
        $orders = $this->orderModel->getByUserId($userId);
        
        $data = [
            'pageTitle' => 'Meu Perfil - Batrip',
            'user' => $user,
            'orders' => $orders,
            'layout' => 'main'
        ];
        
        $this->view('profile.index', $data);
    }
    
    /**
     * Exibe formulário de edição de perfil
     */
    public function edit(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/perfil/editar';
            $_SESSION['error'] = 'Você precisa estar logado para editar seu perfil';
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/');
            return;
        }
        
        $data = [
            'pageTitle' => 'Editar Perfil - Batrip',
            'user' => $user,
            'layout' => 'main'
        ];
        
        $this->view('profile.edit', $data);
    }
    
    /**
     * Atualiza perfil do usuário
     */
    public function update(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Você precisa estar logado para editar seu perfil';
            $this->redirect('/login');
            return;
        }
        
        if (!$this->request->isPost()) {
            $this->redirect('/perfil/editar');
            return;
        }
        
        // CSRF
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        if (!$this->validateCsrf($token)) {
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect('/perfil/editar');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        $name = $this->request->post('name');
        $email = $this->request->post('email');
        $phone = $this->request->post('phone');
        $address = $this->request->post('address');
        $city = $this->request->post('city');
        $state = $this->request->post('state');
        $zipcode = $this->request->post('zipcode');
        
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        // Verifica se email já existe em outro usuário
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $errors[] = 'Este email já está em uso';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/perfil/editar');
            return;
        }
        
        // Atualiza usuário
        $updateData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone ?? null,
            'address' => $address ?? null,
            'city' => $city ?? null,
            'state' => $state ?? null,
            'zipcode' => $zipcode ?? null
        ];
        
        $success = $this->userModel->update($userId, $updateData);
        
        if ($success) {
            // Atualiza sessão
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            $this->redirect('/perfil');
        } else {
            $_SESSION['error'] = 'Erro ao atualizar perfil. Tente novamente.';
            $this->redirect('/perfil/editar');
        }
    }
    
    /**
     * Exibe pedidos do usuário
     */
    public function orders(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/pedidos';
            $_SESSION['error'] = 'Você precisa estar logado para ver seus pedidos';
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $orders = $this->orderModel->getByUserId($userId);
        
        $data = [
            'pageTitle' => 'Meus Pedidos - Batrip',
            'orders' => $orders,
            'layout' => 'main'
        ];
        
        $this->view('profile.orders', $data);
    }
}

