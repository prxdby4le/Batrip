<?php

namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected string $table = 'orders';
    protected string $primaryKey = 'id';
    
    /**
     * Criar novo pedido
     */
    public function create($data)
    {
    $sql = "INSERT INTO {$this->table} 
        (user_id, customer_name, customer_email, customer_phone, 
         shipping_address, shipping_city, shipping_state, shipping_zipcode,
         shipping_method, shipping_cost, payment_method, items, subtotal, total, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->execute($sql, [
            $data['user_id'] ?? null,
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['shipping_address'],
            $data['shipping_city'],
            $data['shipping_state'],
            $data['shipping_zipcode'],
            $data['shipping_method'] ?? null,
            $data['shipping_cost'] ?? 0.0,
            $data['payment_method'],
            $data['items'], // JSON
            $data['subtotal'],
            $data['total'],
            'pending'
        ]);
        
        return $this->lastInsertId();
    }
    
    /**
     * Buscar pedidos de um usuário
     */
    public function findByUser($userId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY created_at DESC";
        
        return $this->query($sql, [$userId]);
    }
    
    /**
     * Buscar pedidos por status
     */
    public function findByStatus($status)
    {
        return $this->where(['status' => $status]);
    }
    
    /**
     * Atualizar status do pedido
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Buscar pedidos recentes
     */
    public function getRecent($limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }
    
    /**
     * Buscar pedidos pendentes
     */
    public function getPending()
    {
        return $this->where(['status' => 'pending']);
    }
    
    /**
     * Contar pedidos por status
     */
    public function countByStatus($status)
    {
        return $this->count(['status' => $status]);
    }
    
    /**
     * Buscar detalhes completos do pedido com itens
     */
    public function getFullDetails($id)
    {
        $order = $this->find($id);
        
        if ($order) {
            // Decodificar items JSON
            $order['items'] = json_decode($order['items'], true);
        }
        
        return $order;
    }
    
    /**
     * Obter último ID inserido
     */
    private function lastInsertId()
    {
        $pdo = self::getConnection();
        return $pdo->lastInsertId();
    }
    
    /**
     * Buscar pedidos de um usuário
     */
    public function getByUserId($userId)
    {
        return $this->findByUser($userId);
    }
}
