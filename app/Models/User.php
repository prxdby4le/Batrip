<?php
/**
 * User Model
 * 
 * Gerencia usuários no banco de dados
 * 
 * @category Models
 * @package  Batrip
 */

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    /**
     * Nome da tabela
     *
     * @var string
     */
    protected string $table = 'users';

    /**
     * Busca usuário por email
     *
     * @param  string $email
     * @return array|false
     */
    public function findByEmail(string $email)
    {
        return $this->findWhere(['email' => $email]);
    }

    /**
     * Busca usuário por username
     *
     * @param  string $username
     * @return array|false
     */
    public function findByUsername(string $username)
    {
        return $this->findWhere(['username' => $username]);
    }

    /**
     * Cria novo usuário com senha hasheada
     *
     * @param  array $data
     * @return int|false
     */
    public function register(array $data)
    {
        // Hash da senha
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Adiciona timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Verifica credenciais de login
     *
     * @param  string $email
     * @param  string $password
     * @return array|false
     */
    public function authenticate(string $email, string $password)
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Atualiza último login
     *
     * @param  int $id
     * @return bool
     */
    public function updateLastLogin(int $id): bool
    {
        return $this->update($id, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Verifica se usuário é admin
     *
     * @param  int $id
     * @return bool
     */
    public function isAdmin(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }

        if (isset($user['role'])) {
            return $user['role'] === 'admin';
        }

        return !empty($user['is_admin']) && (int)$user['is_admin'] === 1;
    }

    /**
     * Atualiza senha do usuário
     *
     * @param  int    $id
     * @param  string $newPassword
     * @return bool
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }

    /**
     * Busca todos usuários ativos
     *
     * @return array
     */
    public function getActive(): array
    {
        return $this->all(['active' => 1], 'created_at DESC');
    }
    
    /**
     * Busca usuário por ID
     *
     * @param  int $id
     * @return array|false
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }
}
