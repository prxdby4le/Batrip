<?php
/**
 * Base Controller Class
 * 
 * Classe base para todos os controllers
 * Fornece funcionalidades comuns de renderização
 * 
 * @category Core
 * @package  Batrip
 */

namespace App\Core;

class Controller
{
    /**
     * Objeto Request atual
     *
     * @var Request
     */
    protected Request $request;

    /**
     * Parâmetros da rota
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Dados para passar para a view
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Layout padrão
     *
     * @var string
     */
    protected string $layout = 'main';

    /**
     * Construtor
     * 
     * @param Request|null $request
     * @param array $params
     */
    public function __construct(?Request $request = null, array $params = [])
    {
        $this->request = $request ?? new Request();
        $this->params = $params;
    }

    /**
     * Obtém parâmetro da rota
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function param(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * Verifica se usuário é admin
     *
     * @return void
     */
    protected function requireAdmin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/adm';
            $this->redirect('/login');
            return;
        }

        // Verifica se é admin (pode ser 1, true, ou '1')
        $isAdmin = isset($_SESSION['is_admin']) && (
            $_SESSION['is_admin'] == 1 || 
            $_SESSION['is_admin'] === true || 
            $_SESSION['is_admin'] === '1'
        );
        
        if (!$isAdmin) {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores.';
            $this->redirect('/');
            return;
        }
    }

    /**
     * Verifica se usuário está logado
     *
     * @return void
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            $this->redirect(BASE_URL . 'login');
        }
    }

    /**
     * Renderiza uma view
     *
     * @param  string $view Nome da view
     * @param  array  $data Dados para a view
     * @return void
     */
    public function view(string $view, array $data = [], ?string $layout = null): void
    {
        // Ajusta layout se informado
        if ($layout !== null) {
            $this->layout = $layout;
        }
        // Mescla dados
        $data = array_merge($this->data, $data);
        
        // Extrai variáveis
        extract($data);
        
        // Path da view
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View não encontrada: {$view}");
        }
        
        // Inicia buffer
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        // Renderiza com layout
        if ($this->layout) {
            $layoutPath = __DIR__ . '/../Views/layouts/' . $this->layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Retorna JSON
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @return void
     */
    protected function json($data, int $statusCode = 200): void
    {
        // Limpar qualquer output buffer antes de enviar JSON
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Retorna JSON de sucesso
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    protected function jsonSuccess($data = [], int $statusCode = 200): void
    {
        if (is_string($data)) {
            $data = ['message' => $data];
        }
        $data['success'] = true;
        $this->json($data, $statusCode);
    }

    /**
     * Retorna JSON de erro
     *
     * @param string $message
     * @param int $statusCode
     * @return void
     */
    protected function jsonError(string $message, int $statusCode = 400): void
    {
        $this->json([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }

    /**
     * Redireciona para URL
     *
     * @param  string $url
     * @return void
     */
    protected function redirect(string $url): void
    {
        // Limpar qualquer output buffer antes do redirecionamento
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Se a URL começa com /, adiciona BASE_URL
        if (strpos($url, '/') === 0 && strpos($url, 'http') !== 0) {
            $url = BASE_URL . ltrim($url, '/');
        } elseif (strpos($url, 'http') !== 0 && strpos($url, BASE_URL) !== 0) {
            // Se não começa com / nem http, assume que é relativo e adiciona BASE_URL
            $url = BASE_URL . ltrim($url, '/');
        }
        
        header("Location: {$url}");
        exit;
    }

    /**
     * Define dados para view
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    protected function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Define múltiplos dados
     *
     * @param  array $data
     * @return void
     */
    protected function setMultiple(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Valida CSRF token
     *
     * @param  string $token
     * @return bool
     */
    protected function validateCsrf(string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        
        // Suporta dois formatos: string direta ou array do CsrfHelper
        $sessionToken = null;
        
        if (isset($_SESSION['csrf_token'])) {
            if (is_array($_SESSION['csrf_token']) && isset($_SESSION['csrf_token']['token'])) {
                // Formato do CsrfHelper (array com 'token' e 'time')
                $sessionToken = $_SESSION['csrf_token']['token'];
            } elseif (is_string($_SESSION['csrf_token'])) {
                // Formato simples (string direta)
                $sessionToken = $_SESSION['csrf_token'];
            }
        }
        
        if ($sessionToken === null) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }

    /**
     * Gera CSRF token
     *
     * @return string
     */
    protected function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
