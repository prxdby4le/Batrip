<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Listar todos pedidos
     */
    public function index()
    {
        $this->requireAdmin();
        
        $orderModel = new Order();
        $pdo = \App\Core\Database::getInstance()->getConnection();
        
        // Filtrar por status se fornecido
        $status = $this->request->get('status');
        
        // Buscar pedidos com informações do usuário
        try {
            $sql = '
                SELECT o.id, o.user_id, 
                       COALESCE(o.customer_name, u.name) as customer_name, 
                       COALESCE(o.customer_email, u.email) as customer_email, 
                       o.status, o.total, o.created_at, o.items,
                       o.shipping_address, o.shipping_city, o.shipping_state,
                       o.payment_method
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
            ';
            
            if ($status && in_array($status, ['pending', 'processing', 'production_complete', 'shipped', 'delivered', 'cancelled'])) {
                $sql .= ' WHERE o.status = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$status]);
            } else {
                $sql .= ' ORDER BY o.created_at DESC';
                $stmt = $pdo->query($sql);
            }
            
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Processa itens JSON
            foreach ($orders as &$order) {
                $items = json_decode($order['items'] ?? '[]', true);
                $order['items_count'] = count($items);
                $order['items_summary'] = '';
                if (!empty($items)) {
                    $summaries = [];
                    foreach ($items as $item) {
                        $qty = $item['qty'] ?? $item['quantity'] ?? 1;
                        $title = $item['title'] ?? 'Produto';
                        $size = $item['size'] ?? '';
                        $summaries[] = "{$qty}x {$title}" . ($size ? " ({$size})" : '');
                    }
                    $order['items_summary'] = implode(', ', $summaries);
                }
            }
        } catch (\PDOException $e) {
            // Erro silencioso - apenas retorna array vazio
            $orders = [];
        }
        
        $this->view('admin/orders/index', [
            'pageTitle' => 'Gerenciar Pedidos - Admin',
            'orders' => $orders,
            'currentStatus' => $status
        ], 'admin');
    }
    
    /**
     * Ver detalhes do pedido
     */
    public function show()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $orderModel = new Order();
        $order = $orderModel->getFullDetails($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Pedido não encontrado';
            return $this->redirect('adm/pedidos');
        }
        
        $this->view('admin/orders/show', [
            'pageTitle' => "Pedido #{$id} - Admin",
            'order' => $order
        ], 'admin');
    }
    
    /**
     * Atualizar status do pedido
     */
    public function updateStatus()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $newStatus = $this->request->post('status');
        
        $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        
        if (!in_array($newStatus, $allowedStatuses)) {
            return $this->jsonError('Status inválido');
        }
        
        $orderModel = new Order();
        $updated = $orderModel->updateStatus($id, $newStatus);
        
        if ($updated) {
            $_SESSION['success'] = 'Status do pedido atualizado!';
            return $this->jsonSuccess(['message' => 'Status atualizado']);
        }
        
        return $this->jsonError('Erro ao atualizar status');
    }
    
    /**
     * Deletar pedido (cancelar)
     */
    public function destroy()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
        $orderModel = new Order();
        
        // Ao invés de deletar, marcar como cancelado
        $updated = $orderModel->updateStatus($id, 'cancelled');
        
        if ($updated) {
            $_SESSION['success'] = 'Pedido cancelado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao cancelar pedido';
        }
        
        return $this->redirect('adm/pedidos');
    }
}
