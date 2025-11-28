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
        $router->get('/produto/{id}', 'ProductController@show');
        $router->get('/produto.php', function() {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                header("Location: /produto/{$id}");
                exit;
            }
            header('Location: /produtos');
            exit;
        });
        
        // Conjuntos (Sets)
        $router->get('/conjuntos', 'SetController@index');
        $router->get('/conjunto/{id}', 'SetController@show');
        $router->get('/produtos/conjunto.php', function() {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                header("Location: /conjunto/{$id}");
                exit;
            }
            header('Location: /conjuntos');
            exit;
        });
        
        // Sobre
        $router->get('/sobre', 'PageController@about');
        $router->get('/sobre.php', function() {
            header('Location: /sobre');
            exit;
        });
        
        // Personalização
        $router->get('/personalizacao', 'PageController@customization');
        $router->get('/personalizacao.php', function() {
            header('Location: /personalizacao');
            exit;
        });
        
        // ===== CARRINHO =====
        // Redireciona para o checkout (carrinho agora faz parte do fluxo de checkout)
        $router->get('/cart', function() {
            header('Location: /checkout/carrinho');
            exit;
        });
        $router->get('/carrinho', function() {
            header('Location: /checkout/carrinho');
            exit;
        });
        $router->get('/cart/sidebar', 'CartController@sidebar');
        $router->get('/ajax/cart-sidebar.php', 'CartController@sidebar'); // Compatibilidade
        $router->get('/includes/cart-sidebar.php', 'CartController@sidebar'); // Compatibilidade
        $router->post('/cart/add', 'CartController@add');
        $router->post('/cart/add-set', 'CartController@addSet');
        $router->post('/cart/addSet', 'CartController@addSet'); // Alias
        $router->post('/cart/update', 'CartController@update');
        $router->post('/cart/remove', 'CartController@remove');
        $router->post('/cart/clear', 'CartController@clear');
        
        // Cart Handler (compatibilidade)
        $router->post('/cart-handler.php', 'CartController@handler');
        $router->post('/public/cart-handler.php', 'CartController@handler');
        $router->get('/cart.php', function() {
            header('Location: /carrinho');
            exit;
        });
        
        // ===== CHECKOUT =====
        
        // Fluxo completo de checkout
        $router->get('/checkout/carrinho', 'CheckoutController@cart');
        $router->get('/checkout/endereco', 'CheckoutController@address');
        $router->post('/checkout/endereco', 'CheckoutController@saveAddress');
        $router->get('/checkout/frete', 'CheckoutController@shipping');
        $router->post('/checkout/frete', 'CheckoutController@saveShipping');
        $router->get('/checkout/pagamento', 'CheckoutController@payment');
        $router->post('/checkout/pagamento', 'CheckoutController@processPayment');
        $router->get('/checkout/revisao', 'CheckoutController@review');
        $router->post('/checkout/finalizar', 'CheckoutController@finalize');
        $router->get('/checkout/sucesso', 'CheckoutController@success');
        
        // Rotas legadas - redirecionam para MVC
        $router->get('/checkout/carrinho.php', function() {
            header('Location: /checkout/carrinho');
            exit;
        });
        $router->get('/checkout/endereco.php', function() {
            header('Location: /checkout/endereco');
            exit;
        });
        $router->get('/checkout/frete.php', function() {
            header('Location: /checkout/frete');
            exit;
        });
        $router->get('/checkout/pagamento.php', function() {
            header('Location: /checkout/pagamento');
            exit;
        });
        $router->get('/checkout/revisao.php', function() {
            header('Location: /checkout/revisao');
            exit;
        });
        $router->get('/checkout/finalizar.php', function() {
            header('Location: /checkout/finalizar');
            exit;
        });
        $router->get('/checkout/sucesso.php', function() {
            header('Location: /checkout/sucesso');
            exit;
        });
        
        // API de cálculo de frete
        $router->get('/checkout/calcula-frete', 'CheckoutController@calculateShipping');
        $router->get('/checkout/calcula-frete.php', 'CheckoutController@calculateShipping'); // Compatibilidade legado
        
        // API de chave pública Mercado Pago
        $router->get('/checkout/mp-public-key.php', 'CheckoutController@getMpPublicKey');
        
        // Rotas antigas (compatibilidade)
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
        
        // Rotas legadas de autenticação - redirecionam para MVC
        $router->get('/registros/login.php', function() {
            header('Location: /login', true, 301);
            exit;
        });
        $router->get('/public/registros/login.php', function() {
            header('Location: /login', true, 301);
            exit;
        });
        $router->get('/registros/register.php', function() {
            header('Location: /register', true, 301);
            exit;
        });
        $router->get('/public/registros/register.php', function() {
            header('Location: /register', true, 301);
            exit;
        });
        $router->get('/registros/logout.php', function() {
            header('Location: /logout', true, 301);
            exit;
        });
        $router->get('/public/registros/logout.php', function() {
            header('Location: /logout', true, 301);
            exit;
        });
        
        // ===== PERFIL DO USUÁRIO =====
        
        $router->get('/perfil', 'ProfileController@index');
        $router->get('/perfil/editar', 'ProfileController@edit');
        $router->post('/perfil/atualizar', 'ProfileController@update');
        $router->get('/pedidos', 'ProfileController@orders');
        $router->get('/pedido/{id}', 'ProfileController@showOrder');
        
        // Rotas legadas de perfil - redirecionam para MVC
        $router->get('/registros/pedidos.php', function() {
            header('Location: /pedidos', true, 301);
            exit;
        });
        $router->get('/public/registros/pedidos.php', function() {
            header('Location: /pedidos', true, 301);
            exit;
        });
        $router->get('/registros/pedido.php', function() {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                header("Location: /pedido/{$id}", true, 301);
            } else {
                header('Location: /pedidos', true, 301);
            }
            exit;
        });
        $router->get('/public/registros/pedido.php', function() {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                header("Location: /pedido/{$id}", true, 301);
            } else {
                header('Location: /pedidos', true, 301);
            }
            exit;
        });
        $router->get('/registros/perfil.php', function() {
            header('Location: /perfil', true, 301);
            exit;
        });
        $router->get('/public/registros/perfil.php', function() {
            header('Location: /perfil', true, 301);
            exit;
        });
        $router->get('/registros/perfil_editar.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/public/registros/perfil_editar.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/registros/alterar_senha.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/public/registros/alterar_senha.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/registros/senha.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/public/registros/senha.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/registros/redefinir-senha.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/public/registros/redefinir-senha.php', function() {
            header('Location: /perfil/editar', true, 301);
            exit;
        });
        $router->get('/registros/gerenciar_usuarios.php', function() {
            header('Location: /adm/usuarios', true, 301);
            exit;
        });
        $router->get('/public/registros/gerenciar_usuarios.php', function() {
            header('Location: /adm/usuarios', true, 301);
            exit;
        });
        
        // ===== ÁREA ADMINISTRATIVA =====
        
        // Dashboard
        $router->get('/adm', 'Admin\DashboardController@index');
        $router->get('/adm/dashboard', 'Admin\DashboardController@index');
        
        // Produtos Admin
        $router->get('/adm/produtos', 'Admin\ProductController@index');
        $router->get('/adm/produtos/novo', 'Admin\ProductController@create');
        // Rota de compatibilidade - redireciona /criar para /novo
        $router->get('/adm/produtos/criar', function() {
            header('Location: /adm/produtos/novo', true, 301);
            exit;
        });
        $router->post('/adm/produtos/salvar', 'Admin\ProductController@store');
        $router->get('/adm/produtos/{id}/editar', 'Admin\ProductController@edit');
        $router->post('/adm/produtos/{id}/atualizar', 'Admin\ProductController@update');
        $router->post('/adm/produtos/{id}/deletar', 'Admin\ProductController@destroy');
        $router->post('/adm/produtos/{id}/toggle', 'Admin\ProductController@toggleActive');
        
        // Conjuntos Admin
        $router->get('/adm/conjuntos', 'Admin\SetController@index');
        $router->get('/adm/conjuntos/novo', 'Admin\SetController@create');
        $router->post('/adm/conjuntos/salvar', 'Admin\SetController@store');
        $router->get('/adm/conjuntos/{id}/editar', 'Admin\SetController@edit');
        $router->post('/adm/conjuntos/{id}/atualizar', 'Admin\SetController@update');
        $router->post('/adm/conjuntos/{id}/deletar', 'Admin\SetController@destroy');

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
        
        // Admin legado - redireciona para dashboard
        $router->get('/adm/index-adm.php', function() {
            header('Location: /adm/dashboard');
            exit;
        });
        $router->get('/adm/login-adm.php', function() {
            header('Location: /login');
            exit;
        });
        
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
        
        // Imagem do conjunto
        $router->get('/set-image.php', 'SetController@image');
        $router->get('/public/set-image.php', 'SetController@image');
        
        // Teste de DB
        $router->get('/test_db_connection.php', 'TestController@database');
        $router->get('/public/test_db_connection.php', 'TestController@database');
        
        // Teste de sessão
        $router->get('/test_session.php', 'TestController@session');
        $router->get('/public/test_session.php', 'TestController@session');
    }
}
