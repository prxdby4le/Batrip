# Migração Gradual: URLs Legadas → Rotas Limpas (MVC)

Este guia descreve uma estratégia segura para migrar endpoints legados (arquivos .php públicos) para rotas limpas servidas pelo roteador MVC, sem quebrar links existentes.

## Objetivos

- Preservar compatibilidade de links antigos (SEO e bookmarks) usando redirecionamentos 301 em GET e 307 em POST.
- Padronizar a navegação em rotas como `/produto/{id}`, `/sobre`, `/login`.
- Permitir ativar/desativar a migração por variável de ambiente.

## Como Funciona

1. Shims nos scripts legados chamam `legacy_redirect_if_enabled()` (em `includes/legacy-redirect.php`).
   - A função só redireciona quando `LEGACY_REDIRECTS=1` (ou `true`/`on`).
   - Sempre registra o acesso em `logs/legacy-access.log` para análise.
2. `includes/head.php` já define `baseHref`, usado para montar URLs corretas mesmo em subpastas.
3. `.htaccess` pode ter regras de rewrite opcionais para redirecionar arquivos legados para rotas novas (ver abaixo), mas os shims já resolvem sem mexer no Apache.

## Endpoints mapeados (propostos)

- `produto.php?id=123` → `/produto/123`
- `produtos/conjunto.php?id=55` → `/conjunto/55`
- `sobre.php` → `/sobre`
- `registros/login.php` → `/login`
- `registros/register.php` → `/register`

Endpoints de imagem podem migrar depois:

- `product-image.php` → `/produto/{id}/imagem` (futuro)
- `set-image.php` → `/conjunto/{id}/imagem` (futuro)

## Ativar Migração

No ambiente (Docker ou servidor), defina a variável:

```bash
LEGACY_REDIRECTS=1
```

Sem essa variável, nada muda; as páginas legadas continuam funcionando.

## Regras .htaccess (opcionais)

Caso prefira mover a lógica para o Apache, adicione antes do rewrite para `index-mvc.php`:

```apache
# Produto: produto.php?id=123 → /produto/123
RewriteCond %{THE_REQUEST} \s/[^\s]*produto\.php\s [NC]
RewriteCond %{QUERY_STRING} ^id=([0-9]+)$ [NC]
RewriteRule ^produto\.php$ /produto/%1? [R=301,L]

# Conjunto: produtos/conjunto.php?id=55 → /conjunto/55
RewriteCond %{THE_REQUEST} \s/[^\s]*produtos/conjunto\.php\s [NC]
RewriteCond %{QUERY_STRING} ^id=([0-9]+)$ [NC]
RewriteRule ^produtos/conjunto\.php$ /conjunto/%1? [R=301,L]

# Páginas simples
RewriteRule ^sobre\.php$ /sobre [R=301,L]
RewriteRule ^registros/login\.php$ /login [R=301,L]
RewriteRule ^registros/register\.php$ /register [R=301,L]
```

Observações:

- As regras acima não são obrigatórias se os shims já estiverem ativos com `LEGACY_REDIRECTS=1`.
- Faça deploy destas regras apenas após validar em staging.

## Testes (Pest) sugeridos

Crie `tests/Feature/LegacyRedirectsTest.php` com asserts como:

- GET `/produto.php?id=1` → 301 Location `/produto/1`
- GET `/sobre.php` → 301 `/sobre`
- GET `/produto/1` → 200

Caso não exista servidor HTTP no ambiente de testes, unit-test a função `legacy_redirect()` com `$emitHeaders=false` para validar status e Location.

## Plano de Remoção

- Monitore `logs/legacy-access.log` por 30 dias.
- Quando o volume de acessos legados for baixo, remova os shims e/ou mantenha apenas as regras do Apache.
- Atualize o conteúdo e links internos para usarem apenas as rotas limpas.
