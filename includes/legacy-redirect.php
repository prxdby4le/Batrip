<?php
/**
 * Legacy redirect helper
 * - Permite redirecionar endpoints legados para rotas limpas (MVC)
 * - Controlado por variável de ambiente LEGACY_REDIRECTS=1 (desligado por padrão)
 * - Faz log simples dos acessos em logs/legacy-access.log
 */

if (!function_exists('legacy_redirect_enabled')) {
    function legacy_redirect_enabled(): bool {
        $env = getenv('LEGACY_REDIRECTS');
        return $env === '1' || $env === 'true' || $env === 'on';
    }
}

if (!function_exists('legacy_log')) {
    function legacy_log(string $script, array $params = []): void {
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf(
            "%s\t%s\t%s\t%s\n",
            date('c'),
            $script,
            $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            json_encode($params, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
        );
        @file_put_contents($dir . '/legacy-access.log', $line, FILE_APPEND);
    }
}

if (!function_exists('legacy_build_location')) {
    /**
     * Monta a URL de destino baseada em baseHref (head.php) ou BASE_URL
     */
    function legacy_build_location(string $targetPath): string {
        $baseHref = $GLOBALS['baseHref'] ?? '/';
        $baseHref = rtrim($baseHref, '/') . '/';
        $targetPath = ltrim($targetPath, '/');
        return $baseHref . $targetPath;
    }
}

if (!function_exists('legacy_redirect')) {
    /**
     * Executa o redirecionamento (ou retorna cabeçalhos simulados se $emitHeaders=false)
     * - Para GET/HEAD usa 301 por padrão; para POST usa 307 por padrão
     */
    function legacy_redirect(string $targetPath, ?int $status = null, bool $emitHeaders = true): array {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $status = $status ?? (($method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') ? 307 : 301);
        $location = legacy_build_location($targetPath);

        $headers = [
            ['Location', $location, $status],
            ['Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', null],
        ];

        if ($emitHeaders && php_sapi_name() !== 'cli') {
            http_response_code($status);
            foreach ($headers as [$name, $value]) {
                header($name . ': ' . $value);
            }
            exit;
        }
        return ['status' => $status, 'location' => $location, 'headers' => $headers];
    }
}

if (!function_exists('legacy_redirect_if_enabled')) {
    /**
     * Redireciona apenas quando LEGACY_REDIRECTS estiver habilitado
     */
    function legacy_redirect_if_enabled(string $targetPath, ?int $status = null, bool $emitHeaders = true): ?array {
        legacy_log($_SERVER['SCRIPT_NAME'] ?? 'unknown', $_GET ?? []);
        if (!legacy_redirect_enabled()) { return null; }
        return legacy_redirect($targetPath, $status, $emitHeaders);
    }
}
