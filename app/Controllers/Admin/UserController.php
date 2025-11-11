<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Listar todos usuários
     */
    public function index()
    {
        $this->requireAdmin();
        
        $userModel = new User();
        $users = $userModel->all([], 'created_at DESC');
        $users = array_map(function ($user) {
            if (!isset($user['role'])) {
                $user['role'] = (!empty($user['is_admin']) && (int)$user['is_admin'] === 1) ? 'admin' : 'user';
            }
            return $user;
        }, $users);
        
        $this->view('admin/users/index', [
            'pageTitle' => 'Gerenciar Usuários - Admin',
            'users' => $users
        ], 'admin');
    }
    
    /**
     * Ver detalhes do usuário
     */
    public function show()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $userModel = new User();
        $user = $userModel->find($id);
        if ($user && !isset($user['role'])) {
            $user['role'] = (!empty($user['is_admin']) && (int)$user['is_admin'] === 1) ? 'admin' : 'user';
        }
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            return $this->redirect('adm/usuarios');
        }
        
        $this->view('admin/users/show', [
            'pageTitle' => "Usuário: {$user['name']} - Admin",
            'user' => $user
        ], 'admin');
    }
    
    /**
     * Formulário de editar usuário
     */
    public function edit()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $userModel = new User();
        $user = $userModel->find($id);
        if ($user && !isset($user['role'])) {
            $user['role'] = (!empty($user['is_admin']) && (int)$user['is_admin'] === 1) ? 'admin' : 'user';
        }
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            return $this->redirect('adm/usuarios');
        }
        
        $this->view('admin/users/edit', [
            'pageTitle' => "Editar Usuário - Admin",
            'user' => $user
        ], 'admin');
    }
    
    /**
     * Atualizar usuário
     */
    public function update()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
        // Validação
        $errors = [];
        
        if (empty($this->request->post('name'))) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($this->request->post('email'))) {
            $errors[] = 'Email é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $this->redirect("adm/usuarios/{$id}/editar");
        }
        
        // Preparar dados
        $role = $this->request->post('role') ?? 'user';

        $data = [
            'name' => $this->request->post('name'),
            'email' => $this->request->post('email'),
            'role' => $role,
            'is_admin' => $role === 'admin' ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Atualizar senha se fornecida
        $newPassword = $this->request->post('password');
        if (!empty($newPassword) && strlen($newPassword) >= 6) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        // Atualizar
        $userModel = new User();
        $updated = $userModel->update($id, $data);
        
        if ($updated) {
            $_SESSION['success'] = 'Usuário atualizado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar usuário';
        }
        
        return $this->redirect('adm/usuarios');
    }
    
    /**
     * Deletar usuário
     */
    public function destroy()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
        // Não permitir deletar o próprio usuário
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Você não pode deletar seu próprio usuário';
            return $this->redirect('adm/usuarios');
        }
        
        $userModel = new User();
        $deleted = $userModel->delete($id);
        
        if ($deleted) {
            $_SESSION['success'] = 'Usuário deletado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao deletar usuário';
        }
        
        return $this->redirect('adm/usuarios');
    }
    
    /**
     * Alternar role admin/user
     */
    public function toggleAdmin()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
        // Não permitir alterar o próprio role
        if ($id == $_SESSION['user_id']) {
            return $this->jsonError('Você não pode alterar seu próprio role');
        }
        
        $userModel = new User();
        $user = $userModel->find($id);
        
        if ($user) {
            $currentRole = $user['role'] ?? ((!empty($user['is_admin']) && (int)$user['is_admin'] === 1) ? 'admin' : 'user');
            $newRole = $currentRole === 'admin' ? 'user' : 'admin';
            $userModel->update($id, [
                'role' => $newRole,
                'is_admin' => $newRole === 'admin' ? 1 : 0
            ]);
            
            return $this->jsonSuccess([
                'message' => 'Role atualizado',
                'role' => $newRole
            ]);
        }
        
        return $this->jsonError('Usuário não encontrado');
    }
}
