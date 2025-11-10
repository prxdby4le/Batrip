<?php

namespace App\Core;

/**
 * Request - Encapsula dados da requisição HTTP
 */
class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array $query;
    private array $post;
    private array $server;
    private array $cookies;
    private array $files;
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->server = $_SERVER;
        $this->query = $_GET;
        $this->post = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        
        // Parse o path (remove query string e base path)
        $this->path = $this->parsePath();
    }
    
    /**
     * Parse o path da URI
     */
    private function parsePath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        
        // Remove base path se existir (ex: /Batrip/)
        $scriptName = $this->server['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        
        if ($basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Garante que comece com /
        return '/' . trim($path, '/');
    }
    
    /**
     * Retorna o método HTTP
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * Retorna o path da requisição
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * Retorna a URI completa
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    
    /**
     * Verifica se é GET
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }
    
    /**
     * Verifica se é POST
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }
    
    /**
     * Retorna parâmetro GET
     */
    public function get(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }
    
    /**
     * Retorna parâmetro POST
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }
    
    /**
     * Retorna parâmetro (GET ou POST)
     */
    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }
    
    /**
     * Retorna todos os dados POST
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }
    
    /**
     * Retorna arquivo enviado
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Verifica se tem arquivo
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }
    
    /**
     * Retorna cookie
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }
    
    /**
     * Retorna header
     */
    public function header(string $key, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }
    
    /**
     * Verifica se é requisição AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Retorna IP do cliente
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Retorna user agent
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}
