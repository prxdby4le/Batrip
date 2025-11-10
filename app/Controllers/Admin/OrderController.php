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
        
        // Filtrar por status se fornecido
        $status = $this->request->get('status');
        
        if ($status && in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $orders = $orderModel->findByStatus($status);
        } else {
            $orders = $orderModel->all([], 'created_at DESC');
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
