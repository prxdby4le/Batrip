<?php
/**
 * Web Routes
 * 
 * Define todas as rotas da aplicação
 * 
 * @category Routes
 * @package  Batrip
 */

use App\Core\Router;

$router = new Router();

// ===== HOME =====
$router->get('/', 'HomeController@index');
$router->get('/sobre', 'HomeController@about');

// ===== PRODUTOS =====
$router->get('/produtos', 'ProductController@index');
$router->get('/produto/{id}', 'ProductController@show');
$router->get('/product-image.php', 'ProductController@image');

// ===== CARRINHO =====
$router->get('/carrinho', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/cart/clear', 'CartController@clear');

// ===== CHECKOUT =====
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');
$router->get('/checkout/frete', 'ShippingController@calculate');

// ===== AUTENTICAÇÃO =====
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/registro', 'AuthController@register');
$router->post('/registro', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// ===== PERFIL =====
$router->get('/perfil', 'ProfileController@index');
$router->get('/perfil/pedidos', 'ProfileController@orders');
$router->get('/perfil/pedido/{id}', 'ProfileController@order');
$router->get('/perfil/editar', 'ProfileController@edit');
$router->post('/perfil/editar', 'ProfileController@update');

// ===== ADMIN =====
$router->get('/adm', 'Admin\\DashboardController@index');
$router->get('/adm/dashboard', 'Admin\\DashboardController@index');

// Admin - Produtos
$router->get('/adm/produtos', 'Admin\\ProductController@index');
$router->get('/adm/produtos/novo', 'Admin\\ProductController@create');
$router->post('/adm/produtos', 'Admin\\ProductController@store');
$router->get('/adm/produtos/{id}/editar', 'Admin\\ProductController@edit');
$router->post('/adm/produtos/{id}', 'Admin\\ProductController@update');
$router->post('/adm/produtos/{id}/delete', 'Admin\\ProductController@destroy');

// Admin - Pedidos
$router->get('/adm/pedidos', 'Admin\\OrdersController@index');
$router->get('/adm/pedidos/{id}', 'Admin\\OrdersController@show');
$router->post('/adm/pedidos/{id}/status', 'Admin\\OrdersController@updateStatus');

// Admin - Conjuntos
$router->get('/adm/conjuntos', 'Admin\\SetController@index');
$router->get('/adm/conjuntos/novo', 'Admin\\SetController@create');
$router->post('/adm/conjuntos', 'Admin\\SetController@store');
$router->get('/adm/conjuntos/{id}/editar', 'Admin\\SetController@edit');
$router->post('/adm/conjuntos/{id}', 'Admin\\SetController@update');
$router->post('/adm/conjuntos/{id}/delete', 'Admin\\SetController@destroy');

// ===== PÁGINAS ESTÁTICAS =====
$router->get('/personalizacao', 'PageController@customization');

// ===== COMPATIBILIDADE COM URLS ANTIGAS =====
// Redireciona URLs antigas para novas
$router->get('/public/index.php', function() {
    header('Location: /');
    exit;
});

$router->get('/public/produto.php', function() {
    $id = $_GET['id'] ?? 0;
    if ($id > 0) {
        header("Location: /produto/{$id}");
    } else {
        header('Location: /produtos');
    }
    exit;
});

$router->get('/public/cart.php', function() {
    header('Location: /carrinho');
    exit;
});

return $router;
