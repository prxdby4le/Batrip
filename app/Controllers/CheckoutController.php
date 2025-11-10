<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Helpers\CartHelper;

/**
 * CheckoutController - Processo de finalização de compra
 */
class CheckoutController extends Controller
{
    private Order $orderModel;
    
    public function __construct()
    {
        $this->orderModel = new Order();
    }
    
    /**
     * Página de checkout
     */
    public function index(): void
    {
        // Verifica se tem itens no carrinho
        $cart = CartHelper::getCart();
        
        if (empty($cart)) {
            $_SESSION['error'] = 'Seu carrinho está vazio';
            $this->redirect('/cart');
            return;
        }
        
        // Recupera frete selecionado (se houver)
        $shipping = $_SESSION['shipping'] ?? null;
        $shippingCost = $shipping['cost'] ?? 0.0;

        // Salva total com frete
        $orderSubtotal = CartHelper::getTotal();
        $orderTotal = $orderSubtotal + $shippingCost;

        // Prefill endereço caso tenha vindo da página de frete
        $shippingInput = $_SESSION['shipping_input'] ?? [];

        $data = [
            'pageTitle' => 'Finalizar Compra - Batrip',
            'cart' => $cart,
            'total' => $orderSubtotal,
            'shipping' => $shipping,
            'shippingCost' => $shippingCost,
            'grandTotal' => $orderTotal,
            'prefill' => $shippingInput,
            'layout' => 'main'
        ];
        
        $this->view('checkout.index', $data);
    }
    
    /**
     * Processa o pedido
     */
    public function process(): void
    {
        if (!$this->request->isPost()) {
            $this->redirect('/checkout');
            return;
        }
        
            // CSRF
            $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
            if (!$this->validateCsrf($token)) {
                $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
                $this->redirect('/checkout');
                return;
            }
        
        // Validação básica
        $name = $this->request->post('name');
        $email = $this->request->post('email');
        $phone = $this->request->post('phone');
        $address = $this->request->post('address');
        $city = $this->request->post('city');
        $state = $this->request->post('state');
        $zipcode = $this->request->post('zipcode');
        $paymentMethod = $this->request->post('payment_method');
        
        $errors = [];
        
        if (empty($name)) $errors[] = 'Nome é obrigatório';
        if (empty($email)) $errors[] = 'Email é obrigatório';
        if (empty($phone)) $errors[] = 'Telefone é obrigatório';
        if (empty($address)) $errors[] = 'Endereço é obrigatório';
        if (empty($city)) $errors[] = 'Cidade é obrigatória';
        if (empty($state)) $errors[] = 'Estado é obrigatório';
        if (empty($zipcode)) $errors[] = 'CEP é obrigatório';
        if (empty($paymentMethod)) $errors[] = 'Forma de pagamento é obrigatória';
        
        $cart = CartHelper::getCart();
        if (empty($cart)) {
            $errors[] = 'Carrinho está vazio';
        }

        // Inclui frete selecionado (opcional)
        $shipping = $_SESSION['shipping'] ?? null;
        $shippingCost = $shipping['cost'] ?? 0.0;
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/checkout');
            return;
        }
        
        // Cria pedido
        $userId = $_SESSION['user_id'] ?? null;
            $subtotal = CartHelper::getTotal();
            $shipping = $_SESSION['shipping'] ?? null;
            $shippingCost = $shipping['cost'] ?? 0.0;
            $orderTotal = $subtotal + $shippingCost;
        
        $orderId = $this->orderModel->create([
            'user_id' => $userId,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'shipping_address' => $address,
            'shipping_city' => $city,
            'shipping_state' => $state,
            'shipping_zipcode' => $zipcode,
            'shipping_method' => $shipping['method'] ?? null,
            'shipping_cost' => $shippingCost,
            'payment_method' => $paymentMethod,
            'items' => json_encode($cart),
                'subtotal' => $subtotal,
                'total' => $orderTotal,
            'status' => 'pending'
        ]);
        
        if ($orderId) {
            // Limpa carrinho e frete
            CartHelper::clear();
            unset($_SESSION['shipping'], $_SESSION['shipping_quotes']);
            
            // Salva ID do pedido na sessão
            $_SESSION['last_order_id'] = $orderId;
            
            $this->redirect('/checkout/success');
        } else {
            $_SESSION['error'] = 'Erro ao processar pedido. Tente novamente.';
            $this->redirect('/checkout');
        }
    }
    
    /**
     * Página de sucesso
     */
    public function success(): void
    {
        $orderId = $_SESSION['last_order_id'] ?? null;
        
        if (!$orderId) {
            $this->redirect('/');
            return;
        }
        
    $order = $this->orderModel->getFullDetails($orderId);
        
        // Limpa da sessão
        unset($_SESSION['last_order_id']);
        
        $data = [
            'pageTitle' => 'Pedido Confirmado - Batrip',
            'order' => $order,
            'layout' => 'main'
        ];
        
        $this->view('checkout.success', $data);
    }
}
