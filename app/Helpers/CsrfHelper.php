<?php
/**
 * CSRF Helper
 * 
 * Gerencia tokens CSRF para proteção contra ataques Cross-Site Request Forgery
 * 
 * @category Helpers
 * @package  Batrip
 */

namespace App\Helpers;

class CsrfHelper
{
    /**
     * Chave da sessão
     *
     * @var string
     */
    private static string $sessionKey = 'csrf_token';

    /**
     * Tempo de vida do token (segundos)
     *
     * @var int
     */
    private static int $tokenLifetime = 3600; // 1 hora

    /**
     * Inicia sessão se necessário
     *
     * @return void
     */
    private static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Gera novo token CSRF
     *
     * @return string
     */
    public static function generate(): string
    {
        self::initSession();

        // Gera token aleatório
        $token = bin2hex(random_bytes(32));

        // Armazena na sessão com timestamp
        $_SESSION[self::$sessionKey] = [
            'token' => $token,
            'time' => time()
        ];

        return $token;
    }

    /**
     * Retorna token atual ou gera novo se não existir
     *
     * @return string
     */
    public static function get(): string
    {
        self::initSession();

        // Se não tem token ou expirou, gera novo
        if (!isset($_SESSION[self::$sessionKey]) || self::isExpired()) {
            return self::generate();
        }

        return $_SESSION[self::$sessionKey]['token'];
    }

    /**
     * Valida token CSRF
     *
     * @param  string $token Token a validar
     * @return bool
     */
    public static function validate(string $token): bool
    {
        self::initSession();

        // Verifica se existe token na sessão
        if (!isset($_SESSION[self::$sessionKey]['token'])) {
            return false;
        }

        // Verifica se expirou
        if (self::isExpired()) {
            self::invalidate();
            return false;
        }

        // Compara tokens (timing-safe)
        return hash_equals($_SESSION[self::$sessionKey]['token'], $token);
    }

    /**
     * Verifica se token expirou
     *
     * @return bool
     */
    private static function isExpired(): bool
    {
        if (!isset($_SESSION[self::$sessionKey]['time'])) {
            return true;
        }

        return (time() - $_SESSION[self::$sessionKey]['time']) > self::$tokenLifetime;
    }

    /**
     * Invalida token atual
     *
     * @return void
     */
    public static function invalidate(): void
    {
        self::initSession();
        unset($_SESSION[self::$sessionKey]);
    }

    /**
     * Retorna campo hidden para formulário
     *
     * @return string
     */
    public static function field(): string
    {
        $token = self::get();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Retorna meta tag para uso em AJAX
     *
     * @return string
     */
    public static function meta(): string
    {
        $token = self::get();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }

    /**
     * Valida requisição atual
     * Verifica POST ou cabeçalho X-CSRF-Token
     *
     * @return bool
     */
    public static function validateRequest(): bool
    {
        // Token do POST
        $postToken = $_POST['csrf_token'] ?? '';
        
        // Token do header (para AJAX)
        $headerToken = '';
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $headerToken = $headers['X-CSRF-Token'] ?? '';
        } else if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        $token = $postToken ?: $headerToken;

        return !empty($token) && self::validate($token);
    }
}
