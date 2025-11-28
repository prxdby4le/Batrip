<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\User;
use App\Helpers\Logger;

class OrdersController extends Controller
{
    // Lista todos os pedidos
    public function index()
    {
        $this->requireAdmin();
        
        $orderModel = new Order();
        $orders = $orderModel->all([], 'created_at DESC');
        
        // Processa itens JSON para cada pedido
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
            
            // Busca dados do usuário se necessário
            if (!isset($order['customer_name']) && isset($order['user_id'])) {
                $userModel = new User();
                $user = $userModel->find($order['user_id']);
                if ($user) {
                    $order['customer_name'] = $user['name'] ?? '';
                    $order['customer_email'] = $user['email'] ?? '';
                }
            }
        }
        
        $this->view('admin/orders/index', [
            'pageTitle' => 'Gerenciar Pedidos - Admin',
            'orders' => $orders
        ], 'admin');
    }

    // Mostra detalhes de um pedido
    public function show()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $orderModel = new Order();
        $order = $orderModel->getFullDetails($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Pedido não encontrado';
            return $this->redirect('/adm/index-adm.php');
        }
        
        // Busca dados do usuário se necessário
        if (!isset($order['customer_name']) && isset($order['user_id'])) {
            $userModel = new User();
            $user = $userModel->find($order['user_id']);
            if ($user) {
                $order['customer_name'] = $user['name'] ?? '';
                $order['customer_email'] = $user['email'] ?? '';
                $order['customer_phone'] = $user['phone'] ?? '';
            }
        }
        
        $this->view('admin/orders/show', [
            'pageTitle' => "Pedido #{$id} - Admin",
            'order' => $order
        ], 'admin');
    }

    // Atualiza status do pedido
    public function updateStatus()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $status = $this->request->post('status') ?? '';
        
        $allowedStatuses = ['pending', 'processing', 'production_complete', 'shipped', 'delivered', 'cancelled'];
        
        if (empty($status) || !in_array($status, $allowedStatuses)) {
            $_SESSION['error'] = 'Status inválido';
            return $this->redirect('/adm/index-adm.php');
        }
        
        $orderModel = new Order();
        $updated = $orderModel->updateStatus($id, $status);
        
        if ($updated) {
            Logger::info('Status do pedido atualizado', [
                'order_id' => $id,
                'new_status' => $status,
                'admin_id' => $_SESSION['user_id'] ?? null
            ]);
            $_SESSION['success'] = 'Status do pedido atualizado com sucesso!';
        } else {
            Logger::error('Erro ao atualizar status do pedido', [
                'order_id' => $id,
                'status' => $status
            ]);
            $_SESSION['error'] = 'Erro ao atualizar status do pedido';
        }
        
        return $this->redirect('/adm/index-adm.php');
    }
}
