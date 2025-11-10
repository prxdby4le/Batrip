<?php
/**
 * Definição de Rotas
 */

use App\Core\Router;

class Routes
{
    public static function register(Router $router)
    {
        // ===== ROTAS PÚBLICAS =====
        
        // Home
        $router->get('/', 'HomeController@index');
        $router->get('/public/', 'HomeController@index');
        $router->get('/public/index.php', 'HomeController@index');
        
        // Produtos
        $router->get('/produtos', 'ProductController@index');
        $router->get('/produto', 'ProductController@show');
        $router->get('/produto/{id}', 'ProductController@show');
        
        // Sobre
        $router->get('/sobre', 'PageController@about');
        
        // Personalização
        $router->get('/personalizacao', 'PageController@customization');
        
        // ===== CARRINHO =====
        
        $router->get('/cart', 'CartController@index');
        $router->post('/cart/add', 'CartController@add');
        $router->post('/cart/update', 'CartController@update');
        $router->post('/cart/remove', 'CartController@remove');
        $router->post('/cart/clear', 'CartController@clear');
        
        // Cart Handler (compatibilidade)
        $router->post('/cart-handler.php', 'CartController@handler');
        $router->post('/public/cart-handler.php', 'CartController@handler');
        
        // ===== CHECKOUT =====
        
        $router->get('/checkout', 'CheckoutController@index');
        $router->post('/checkout/process', 'CheckoutController@process');
        $router->get('/checkout/success', 'CheckoutController@success');

    // ===== FRETE =====
    $router->get('/frete', 'ShippingController@index');
    $router->post('/frete/calcular', 'ShippingController@calculate');
    $router->post('/frete/selecionar', 'ShippingController@select');
        
        // ===== AUTENTICAÇÃO =====
        
        $router->get('/login', 'AuthController@loginForm');
        $router->post('/login', 'AuthController@login');
        $router->get('/register', 'AuthController@registerForm');
        $router->post('/register', 'AuthController@register');
        $router->get('/logout', 'AuthController@logout');
        $router->post('/logout', 'AuthController@logout');
        
        // ===== ÁREA ADMINISTRATIVA =====
        
        // Dashboard
        $router->get('/adm', 'Admin\DashboardController@index');
        $router->get('/adm/dashboard', 'Admin\DashboardController@index');
        
        // Produtos Admin
        $router->get('/adm/produtos', 'Admin\ProductController@index');
        $router->get('/adm/produtos/novo', 'Admin\ProductController@create');
        $router->post('/adm/produtos/salvar', 'Admin\ProductController@store');
        $router->get('/adm/produtos/{id}/editar', 'Admin\ProductController@edit');
        $router->post('/adm/produtos/{id}/atualizar', 'Admin\ProductController@update');
        $router->post('/adm/produtos/{id}/deletar', 'Admin\ProductController@destroy');
        $router->post('/adm/produtos/{id}/toggle', 'Admin\ProductController@toggleActive');

    // Galeria de Imagens (Admin)
    $router->post('/adm/produtos/{id}/imagens/upload', 'Admin\ProductController@uploadImages');
    $router->post('/adm/produtos/{id}/imagens/reordenar', 'Admin\ProductController@reorderImages');
    $router->post('/adm/produtos/{id}/imagens/{imageId}/remover', 'Admin\ProductController@deleteImage');
    $router->post('/adm/produtos/{id}/imagens/{imageId}/principal', 'Admin\ProductController@setPrimaryImage');
        
        // Pedidos Admin
        $router->get('/adm/pedidos', 'Admin\OrderController@index');
        $router->get('/adm/pedidos/{id}', 'Admin\OrderController@show');
        $router->post('/adm/pedidos/{id}/status', 'Admin\OrderController@updateStatus');
        $router->post('/adm/pedidos/{id}/deletar', 'Admin\OrderController@destroy');
        
        // Usuários Admin
        $router->get('/adm/usuarios', 'Admin\UserController@index');
        $router->get('/adm/usuarios/{id}', 'Admin\UserController@show');
        $router->get('/adm/usuarios/{id}/editar', 'Admin\UserController@edit');
        $router->post('/adm/usuarios/{id}/atualizar', 'Admin\UserController@update');
        $router->post('/adm/usuarios/{id}/deletar', 'Admin\UserController@destroy');
        $router->post('/adm/usuarios/{id}/toggle-admin', 'Admin\UserController@toggleAdmin');
        
        // ===== API (JSON) =====
        
        $router->get('/api/products', 'Api\ProductController@index');
        $router->get('/api/products/{id}', 'Api\ProductController@show');
        
        // ===== UTILITÁRIOS =====
        
        // Imagem do produto
        $router->get('/product-image.php', 'ProductController@image');
        $router->get('/public/product-image.php', 'ProductController@image');
        
        // Teste de DB
        $router->get('/test_db_connection.php', 'TestController@database');
        $router->get('/public/test_db_connection.php', 'TestController@database');
        
        // Teste de sessão
        $router->get('/test_session.php', 'TestController@session');
        $router->get('/public/test_session.php', 'TestController@session');
    }
}
