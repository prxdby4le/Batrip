<?php
/**
 * Funções auxiliares globais do Batrip
 * 
 * Este arquivo contém funções helpers para facilitar
 * o desenvolvimento e manter o código limpo.
 */

if (!function_exists('url')) {
    /**
     * Gera URL completa baseada no BASE_URL
     * 
     * @param string $path Caminho relativo (ex: 'produtos', 'cart/add')
     * @return string URL completa
     * 
     * @example
     * url('produtos') → http://localhost/Batrip/produtos
     * url('cart/add') → http://localhost/Batrip/cart/add
     */
    function url(string $path = ''): string
    {
        return BASE_URL . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Gera URL para assets (CSS, JS, imagens, etc)
     * 
     * @param string $path Caminho do asset (ex: 'css/styles.css')
     * @return string URL completa do asset
     * 
     * @example
     * asset('css/styles.css') → http://localhost/Batrip/assets/css/styles.css
     * asset('js/cart.js') → http://localhost/Batrip/assets/js/cart.js
     */
    function asset(string $path): string
    {
        return ASSETS_URL . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    /**
     * Alias para url() - mais semântico para rotas
     * 
     * @param string $route Nome da rota
     * @return string URL completa
     */
    function route(string $route): string
    {
        return url($route);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redireciona para uma URL
     * 
     * @param string $path Caminho para redirecionar
     * @param int $code Código HTTP (301, 302, 303, etc)
     * @return void
     * 
     * @example
     * redirect('login'); // Redireciona para login
     * redirect('produtos', 301); // Redirect permanente
     */
    function redirect(string $path, int $code = 302): void
    {
        $url = url($path);
        http_response_code($code);
        header("Location: $url");
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redireciona de volta para a página anterior
     * 
     * @param string $default URL padrão se não houver referrer
     * @return void
     */
    function back(string $default = ''): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        
        if ($referer && strpos($referer, BASE_URL) === 0) {
            header("Location: $referer");
        } else {
            redirect($default);
        }
        exit;
    }
}

if (!function_exists('product_image')) {
    /**
     * Gera URL para imagem de produto
     * 
     * @param int $productId ID do produto
     * @param int|null $imageId ID específico da imagem (opcional)
     * @return string URL da imagem
     * 
     * @example
     * product_image(123) → http://localhost/Batrip/product-image.php?id=123
     * product_image(123, 456) → http://localhost/Batrip/product-image.php?img_id=456
     */
    function product_image(int $productId, ?int $imageId = null): string
    {
        if ($imageId) {
            return url("product-image.php?img_id=$imageId");
        }
        return url("product-image.php?id=$productId");
    }
}

if (!function_exists('is_active_route')) {
    /**
     * Verifica se a rota atual é a especificada (útil para navegação)
     * 
     * @param string $route Rota para verificar
     * @param bool $exact Se deve ser match exato (padrão: false)
     * @return bool
     * 
     * @example
     * is_active_route('produtos') → true se estiver em /produtos ou /produtos/123
     * is_active_route('produtos', true) → true apenas se for exatamente /produtos
     */
    function is_active_route(string $route, bool $exact = false): bool
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $currentPath = parse_url($currentPath, PHP_URL_PATH);
        
        // Remove base path se existir
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        if ($basePath !== '/' && strpos($currentPath, $basePath) === 0) {
            $currentPath = substr($currentPath, strlen($basePath));
        }
        
        $currentPath = '/' . trim($currentPath, '/');
        $route = '/' . trim($route, '/');
        
        if ($exact) {
            return $currentPath === $route;
        }
        
        return strpos($currentPath, $route) === 0;
    }
}

if (!function_exists('active_class')) {
    /**
     * Retorna classe 'active' se a rota for a atual
     * Útil para menus de navegação
     * 
     * @param string $route Rota para verificar
     * @param string $class Classe a retornar (padrão: 'active')
     * @param bool $exact Se deve ser match exato
     * @return string
     * 
     * @example
     * <a class="nav-link <?php echo active_class('produtos'); ?>">
     */
    function active_class(string $route, string $class = 'active', bool $exact = false): string
    {
        return is_active_route($route, $exact) ? $class : '';
    }
}

if (!function_exists('old')) {
    /**
     * Recupera valor antigo de input (útil após validação falhar)
     * 
     * @param string $key Chave do input
     * @param mixed $default Valor padrão se não existir
     * @return mixed
     * 
     * @example
     * <input value="<?php echo old('email', $user['email'] ?? ''); ?>">
     */
    function old(string $key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    /**
     * Define uma mensagem flash (usada uma vez)
     * 
     * @param string $type Tipo da mensagem (success, error, warning, info)
     * @param string $message Mensagem
     * @return void
     * 
     * @example
     * flash('success', 'Produto adicionado com sucesso!');
     */
    function flash(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
    }
}

if (!function_exists('get_flash')) {
    /**
     * Recupera e limpa mensagem flash
     * 
     * @param string $type Tipo da mensagem
     * @return string|null
     */
    function get_flash(string $type): ?string
    {
        if (isset($_SESSION[$type])) {
            $message = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $message;
        }
        return null;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Gera token CSRF
     * 
     * @return string
     */
    function csrf_token(): string
    {
        require_once ROOT_PATH . '/app/Helpers/CsrfHelper.php';
        return \App\Helpers\CsrfHelper::generate();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Gera campo hidden com token CSRF
     * 
     * @return string HTML do campo hidden
     * 
     * @example
     * <form method="POST">
     *     <?php echo csrf_field(); ?>
     * </form>
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     * Alias para htmlspecialchars com configuração padrão
     * 
     * @param string $string String para escapar
     * @return string
     * 
     * @example
     * echo e($user_input); // Seguro contra XSS
     */
    function e(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and Die - Debug helper
     * Exibe variável e para a execução
     * 
     * @param mixed ...$vars Variáveis para debugar
     * @return void
     */
    function dd(...$vars): void
    {
        echo '<pre style="background: #1e1e1e; color: #dcdcdc; padding: 20px; margin: 20px; border-radius: 5px;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump - Debug helper sem parar execução
     * 
     * @param mixed ...$vars Variáveis para debugar
     * @return void
     */
    function dump(...$vars): void
    {
        echo '<pre style="background: #1e1e1e; color: #dcdcdc; padding: 20px; margin: 20px; border-radius: 5px;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}

if (!function_exists('env')) {
    /**
     * Recupera variável de ambiente
     * 
     * @param string $key Chave da variável
     * @param mixed $default Valor padrão
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Conversão de valores booleanos
        $value = trim($value);
        if (strtolower($value) === 'true') return true;
        if (strtolower($value) === 'false') return false;
        if (strtolower($value) === 'null') return null;
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Recupera valor de configuração
     * 
     * @param string $key Chave no formato 'arquivo.chave'
     * @param mixed $default Valor padrão
     * @return mixed
     * 
     * @example
     * config('app.name') → 'Batrip'
     */
    function config(string $key, $default = null)
    {
        static $config = null;
        
        if ($config === null) {
            $config = [
                'app.name' => 'Batrip',
                'app.url' => BASE_URL,
                'app.env' => env('APP_ENV', 'production'),
                'app.debug' => env('APP_DEBUG', false),
            ];
        }
        
        return $config[$key] ?? $default;
    }
}
