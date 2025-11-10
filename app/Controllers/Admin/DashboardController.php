<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Dashboard principal do admin
     */
    public function index()
    {
        // Verificar se é admin
        $this->requireAdmin();
        
        // Buscar estatísticas
        $productModel = new Product();
        $orderModel = new Order();
        $userModel = new User();
        
        // Contar totais
        $totalProducts = $productModel->count();
        $totalOrders = $orderModel->count();
        $totalUsers = $userModel->count();
        $pendingOrders = $orderModel->countByStatus('pending');
        
        // Buscar pedidos recentes
        $recentOrders = $orderModel->getRecent(5);
        
        // Buscar produtos recentes
        $recentProducts = $productModel->all([], 'created_at DESC', 5);
        
        // Calcular receita total (simplificado)
        $totalRevenue = 0;
        $allOrders = $orderModel->all();
        foreach ($allOrders as $order) {
            $totalRevenue += $order['total'] ?? 0;
        }
        
        // Renderizar view
        $this->view('admin/dashboard/index', [
            'pageTitle' => 'Dashboard Admin - Batrip',
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'totalUsers' => $totalUsers,
                'pendingOrders' => $pendingOrders,
                'totalRevenue' => $totalRevenue
            ],
            'recentOrders' => $recentOrders,
            'recentProducts' => $recentProducts
        ], 'admin');
    }
}
