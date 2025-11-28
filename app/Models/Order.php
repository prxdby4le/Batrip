<?php

namespace App\Models;

use App\Core\Model;

/**
 * Order Model
 * 
 * Gerencia operações relacionadas a pedidos
 */
class Order extends Model
{
    protected string $table = 'orders';
    protected string $primaryKey = 'id';
    
    /**
     * Criar novo pedido
     */
    public function create($data)
    {
        try {
            // Verificar se a conexão está disponível
            if (!$this->db) {
                error_log("Order::create - Erro: Conexão com banco de dados não disponível");
                return false;
            }
            
            // Limpar cache de colunas para garantir que temos as colunas mais recentes
            $this->clearColumnsCache();
            
            // Obter colunas existentes na tabela
            $columns = $this->getTableColumns();
            
            if (empty($columns)) {
                error_log("Order::create - Erro: Nenhuma coluna encontrada na tabela {$this->table}");
                return false;
            }
            
            // Preparar dados com valores padrão
            // Nota: Não incluímos 'created_at' pois a tabela tem DEFAULT CURRENT_TIMESTAMP
            $orderData = [
                'user_id' => $data['user_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? '',
                'customer_email' => $data['customer_email'] ?? '',
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? '',
                'shipping_city' => $data['shipping_city'] ?? '',
                'shipping_state' => $data['shipping_state'] ?? '',
                'shipping_zipcode' => $data['shipping_zipcode'] ?? '',
                'shipping_method' => $data['shipping_method'] ?? null,
                'shipping_cost' => $data['shipping_cost'] ?? ($data['shipping'] ?? 0.0),
                'payment_method' => $data['payment_method'] ?? 'pix',
                'endereco' => is_string($data['endereco'] ?? '') ? $data['endereco'] : json_encode($data['endereco'] ?? []),
                'frete' => is_string($data['frete'] ?? '') ? $data['frete'] : json_encode($data['frete'] ?? []),
                'items' => is_array($data['items'] ?? []) ? json_encode($data['items']) : ($data['items'] ?? '[]'),
                'subtotal' => $data['subtotal'] ?? 0.0,
                'shipping' => $data['shipping'] ?? ($data['shipping_cost'] ?? 0.0), // Coluna 'shipping' também existe
                'total' => $data['total'] ?? 0.0,
                'status' => $data['status'] ?? 'pending'
            ];
            
            // Garantir que colunas NOT NULL não sejam vazias
            if (empty($orderData['endereco'])) {
                $orderData['endereco'] = '{}';
            }
            if (empty($orderData['frete'])) {
                $orderData['frete'] = '{}';
            }
            if (empty($orderData['items'])) {
                $orderData['items'] = '[]';
            }
            
            // Filtrar apenas colunas que existem na tabela
            $filteredData = [];
            $params = [];
            $placeholders = [];
            
            foreach ($orderData as $key => $value) {
                if (in_array($key, $columns)) {
                    // Tratar valores NULL corretamente
                    if ($value === null && in_array($key, ['customer_phone', 'shipping_method'])) {
                        $filteredData[$key] = null;
                        $params[] = null;
                    } else {
                        $filteredData[$key] = $value;
                        $params[] = $value;
                    }
                    $placeholders[] = '?';
                } else {
                    error_log("Order::create - Coluna '{$key}' não existe na tabela, pulando...");
                }
            }
            
            // Se não temos user_id, não podemos criar o pedido
            if (!isset($filteredData['user_id']) || $filteredData['user_id'] === null) {
                error_log("Order::create - Erro: user_id é obrigatório");
                return false;
            }
            
            if (empty($filteredData)) {
                error_log("Order::create - Erro: Nenhum dado válido após filtrar colunas existentes");
                error_log("Order::create - Colunas disponíveis: " . json_encode($columns));
                return false;
            }
            
            // Construir SQL dinamicamente
            $columnsList = implode(', ', array_keys($filteredData));
            $valuesList = implode(', ', $placeholders);
            
            $sql = "INSERT INTO {$this->table} ({$columnsList}) VALUES ({$valuesList})";
            
            error_log("Order::create - SQL: " . $sql);
            error_log("Order::create - Colunas: " . json_encode(array_keys($filteredData)));
            error_log("Order::create - Parâmetros: " . json_encode($params));
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log("Order::create - Erro ao preparar statement: " . json_encode($errorInfo));
                return false;
            }
            
            $success = $stmt->execute($params);
            
            if ($success) {
                $orderId = (int)$this->db->lastInsertId();
                error_log("Order::create - Pedido criado com sucesso. ID: " . $orderId);
                return $orderId;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Order::create - Erro ao executar INSERT: " . json_encode($errorInfo));
                error_log("Order::create - SQL executado: " . $sql);
                error_log("Order::create - Número de parâmetros: " . count($params));
                error_log("Order::create - Número de placeholders: " . count($placeholders));
                return false;
            }
        } catch (\PDOException $e) {
            error_log("Order::create - Exceção ao criar pedido: " . $e->getMessage());
            error_log("Order::create - Trace: " . $e->getTraceAsString());
            return false;
        } catch (\Exception $e) {
            error_log("Order::create - Exceção genérica: " . $e->getMessage());
            error_log("Order::create - Trace: " . $e->getTraceAsString());
            return false;
        }
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
     * Inclui informações do cliente via JOIN com users
     */
    public function getFullDetails($id)
    {
        $pdo = $this->db;
        
        // Buscar pedido com informações do usuário (fallback para dados diretos do pedido)
        $sql = 'SELECT o.*, 
                       u.name as user_name, 
                       u.email as user_email, 
                       u.phone as user_phone
                FROM ' . $this->table . ' o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.' . $this->primaryKey . ' = ?';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($order) {
            // Garantir que campos de cliente existam (preferência: dados do pedido, fallback: dados do usuário)
            if (empty($order['customer_name']) && !empty($order['user_name'])) {
                $order['customer_name'] = $order['user_name'];
            }
            if (empty($order['customer_email']) && !empty($order['user_email'])) {
                $order['customer_email'] = $order['user_email'];
            }
            if (empty($order['customer_phone']) && !empty($order['user_phone'])) {
                $order['customer_phone'] = $order['user_phone'];
            }
            
            // Decodificar items JSON
            $order['items'] = json_decode($order['items'] ?? '[]', true);
            
            // Remover campos auxiliares do JOIN
            unset($order['user_name'], $order['user_email'], $order['user_phone']);
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
    
    /**
     * Busca todos os pedidos com info do usuário (para admin)
     */
    public static function withUserInfo()
    {
        $pdo = self::getConnection();
        // Busca apenas os campos realmente existentes no schema atual
        $sql = 'SELECT o.id, o.user_id, o.endereco, o.frete, o.subtotal, o.shipping, o.total, o.status, o.items, o.created_at, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC';
        $stmt = $pdo->query($sql);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // Decodifica endereço e frete para facilitar na view
        foreach ($orders as &$order) {
            $order['endereco'] = json_decode($order['endereco'] ?? '{}', true);
            $order['frete'] = json_decode($order['frete'] ?? '{}', true);
            $order['items'] = json_decode($order['items'] ?? '[]', true);
        }
        return $orders;
    }

    /**
     * Busca detalhes completos de um pedido (para admin)
     */
    public static function findWithDetails($id)
    {
        $pdo = self::getConnection();
        $sql = 'SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($order) {
            $order['items'] = json_decode($order['items'] ?? '[]', true);
        }
        return $order;
    }
}
