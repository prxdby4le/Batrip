<?php
/**
 * Router Class
 * 
 * Gerencia roteamento da aplicação
 * 
 * @category Core
 * @package  Batrip
 */

namespace App\Core;

class Router
{
    /**
     * Rotas registradas
     *
     * @var array
     */
    private array $routes = [];

    /**
     * Controller padrão
     *
     * @var string
     */
    private string $defaultController = 'HomeController';

    /**
     * Action padrão
     *
     * @var string
     */
    private string $defaultAction = 'index';

    /**
     * Objeto Request
     *
     * @var Request
     */
    private Request $request;

    /**
     * Construtor
     * 
     * @param Request|null $request
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
    }

    /**
     * Adiciona rota GET
     *
     * @param  string   $pattern
     * @param  callable|string $handler
     * @return void
     */
    public function get(string $pattern, $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * Adiciona rota POST
     *
     * @param  string   $pattern
     * @param  callable|string $handler
     * @return void
     */
    public function post(string $pattern, $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * Adiciona rota
     *
     * @param  string   $method
     * @param  string   $pattern
     * @param  callable|string $handler
     * @return void
     */
    private function addRoute(string $method, string $pattern, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * Despacha requisição
     *
     * @param  Request|string $request Request object ou URI string
     * @param  string|null $method HTTP method (se $request for string)
     * @return void
     */
    public function dispatch($request, ?string $method = null): void
    {
        // Se receber objeto Request
        if ($request instanceof Request) {
            $uri = $request->getPath(); // Já retorna path limpo
            $method = $request->getMethod();
        } else {
            // Se receber string (compatibilidade)
            $uri = $request;
            $method = $method ?? 'GET';
            
            // Remove query string
            if (($pos = strpos($uri, '?')) !== false) {
                $uri = substr($uri, 0, $pos);
            }

            // Remove trailing slash
            $uri = rtrim($uri, '/');
            if (empty($uri)) {
                $uri = '/';
            }
        }

        // Tenta encontrar rota correspondente
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $route['pattern']) . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                // Remove índices numéricos
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // Nenhuma rota encontrada - 404
        http_response_code(404);
        echo "404 - Página não encontrada";
    }

    /**
     * Chama handler da rota
     *
     * @param  callable|string $handler
     * @param  array           $params
     * @return void
     */
    private function callHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            // Formato: "Controller@action"
            [$controller, $action] = explode('@', $handler);
            
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller não encontrado: {$controller}");
            }
            
            // Passa Request e params para o controller
            $controllerInstance = new $controllerClass($this->request, $params);
            
            if (!method_exists($controllerInstance, $action)) {
                throw new \Exception("Action não encontrada: {$action} em {$controller}");
            }
            
            call_user_func_array([$controllerInstance, $action], array_values($params));
        }
    }
}
