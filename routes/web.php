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
$router->get('/product-image/{id}', 'ProductController@image');
$router->get('/pesquisa', 'ProductController@search');
$router->get('/categoria/{category}', 'ProductController@category');

// ===== CARRINHO =====
$router->get('/carrinho', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/cart/clear', 'CartController@clear');

// ===== COMPATIBILIDADE COM URLS ANTIGAS =====
// Redireciona URLs antigas para novas
$router->get('/public/index.php', function() {
    header('Location: /');
    exit;
});

$router->get('/public/produto.php', function() {
    $id = $_GET['id'] ?? 0;
    header("Location: /produto/{$id}");
    exit;
});

$router->get('/public/cart.php', function() {
    header('Location: /carrinho');
    exit;
});

return $router;
