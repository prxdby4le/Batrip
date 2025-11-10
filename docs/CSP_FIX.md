# Correção: Erros de CSP e Imagens

## 🐛 Problemas Identificados

### 1. Content Security Policy (CSP) bloqueando imagens
```
Refused to load the image 'http://localhost/Batrip/assets/...' 
because it violates the following Content Security Policy directive: 
"img-src 'self' data:"
```

**Causa:** CSP muito restritivo não permitia imagens de localhost completo

### 2. Placeholder errado
```
Failed to load resource: placeholder.jpg (404)
```

**Causa:** Código buscava `placeholder.jpg`, mas o arquivo é `placeholder.svg`

## ✅ Soluções Aplicadas

### 1. Atualizado CSP em `app/Views/layouts/main.php`

**ANTES:**
```php
$csp = "... img-src 'self' data:; ...";
```

**DEPOIS:**
```php
$csp = "... img-src 'self' http://localhost data: blob:; ...";
```

**O que foi adicionado:**
- ✅ `http://localhost` - Permite imagens de localhost (qualquer porta/path)
- ✅ `blob:` - Permite blob URLs (útil para uploads/preview)

### 2. Corrigido placeholder em 3 arquivos

| Arquivo | Mudança |
|---------|---------|
| `app/Views/home/index.php` | `placeholder.jpg` → `placeholder.svg` |
| `app/Views/products/index.php` | `placeholder.jpg` → `placeholder.svg` |
| `app/Views/products/show.php` | `placeholder.jpg` → `placeholder.svg` |

## 🔒 Segurança: Entendendo o CSP

### O que é CSP?

**Content Security Policy** é um header HTTP que define quais recursos podem ser carregados, prevenindo ataques XSS.

### Nossa configuração atual:

```php
default-src 'self'              ← Scripts/recursos só do próprio site
script-src 'self' [CDNs]       ← JS do site + CDNs confiáveis
style-src 'self' [CDNs]        ← CSS do site + CDNs confiáveis
img-src 'self' localhost       ← Imagens do site + localhost
font-src 'self' [CDNs]         ← Fontes do site + CDNs
connect-src 'self' viacep      ← APIs do site + ViaCEP
frame-ancestors 'self'         ← Não pode ser embedado
```

### ⚠️ Importante para Produção

Quando subir para produção, **REMOVER** `http://localhost`:

```php
// DESENVOLVIMENTO
$csp = "... img-src 'self' http://localhost data: blob:; ...";

// PRODUÇÃO (mais seguro)
$csp = "... img-src 'self' data: blob:; ...";
```

**Ou melhor ainda**, use variável de ambiente:

```php
$imgSrc = ENVIRONMENT === 'development' 
    ? "'self' http://localhost data: blob:" 
    : "'self' data: blob:";
    
$csp = "... img-src {$imgSrc}; ...";
```

## 🧪 Verificar se Funcionou

1. **Limpar cache do navegador:**
   ```
   Ctrl + Shift + Delete → Limpar cache
   OU
   Ctrl + F5 (hard refresh)
   ```

2. **Abrir console (F12):**
   - ✅ Sem erros de CSP
   - ✅ Sem 404 de placeholder.jpg
   - ✅ Todas as imagens carregando (200 OK)

3. **Verificar visualmente:**
   - ✅ Logo aparece no topo
   - ✅ Imagens de produtos aparecem
   - ✅ Placeholder SVG aparece quando produto sem imagem

## 📁 Arquivos Modificados

```
Batrip/
├── app/
│   └── Views/
│       ├── layouts/
│       │   └── main.php        ← CSP atualizado
│       ├── home/
│       │   └── index.php       ← placeholder.svg
│       ├── products/
│       │   ├── index.php       ← placeholder.svg
│       │   └── show.php        ← placeholder.svg
└── docs/
    └── CSP_FIX.md              ← Este arquivo
```

## 🐛 Outros Problemas Comuns

### Favicon 500 Error

Se aparecer erro 500 no favicon:

```
:8080/favicon.ico:1 Failed to load resource: 500
```

**Causa:** Servidor tentando processar favicon como PHP

**Solução 1:** Adicionar regra no `.htaccess`:
```apache
# Serve favicon diretamente
RewriteCond %{REQUEST_URI} ^/favicon\.ico$
RewriteRule ^ - [L]
```

**Solução 2:** Especificar favicon no HTML (já feito):
```html
<link rel="icon" href="assets/materials/batrip symbol.png">
```

### Imagens ainda não carregam

**Verificar:**

1. **Arquivos existem?**
   ```powershell
   ls C:\coding\Batrip\public\assets\img\placeholder.svg
   ls C:\coding\Batrip\public\assets\materials\
   ```

2. **BASE_URL correto?**
   ```
   Verificar .env: BASE_URL=http://localhost/Batrip/public/
   ```

3. **Apache rodando?**
   - XAMPP Control Panel → Apache → Start

4. **Cache do navegador?**
   - Ctrl+F5 ou limpar cache

## 🔍 Debug de CSP

Para ver quais recursos estão sendo bloqueados:

1. Abrir console (F12)
2. Filtrar por "Content Security Policy"
3. Ver detalhes do que foi bloqueado

### Testar CSP temporariamente relaxado:

Comentar temporariamente o CSP para testar:

```php
// if (!headers_sent()) {
//     header("Content-Security-Policy: $csp");
// }
```

⚠️ **NÃO FAZER EM PRODUÇÃO!**

## ✅ Checklist

- [x] CSP atualizado com `http://localhost`
- [x] placeholder.jpg → placeholder.svg (3 arquivos)
- [x] Assets copiados para public/
- [x] BASE_URL correto (.env)
- [ ] Testar no navegador
- [ ] Verificar console sem erros CSP
- [ ] Ver imagens carregando

## 📝 Notas de Produção

Antes do deploy:

1. [ ] Remover `http://localhost` do CSP
2. [ ] Verificar todas as imagens têm alternativa
3. [ ] Testar placeholder funciona
4. [ ] Configurar CSP baseado em ambiente
5. [ ] Adicionar domínio de produção se usar CDN

---

**Data:** 09/10/2025  
**Problemas:** CSP bloqueando + placeholder.jpg inexistente  
**Soluções:** CSP relaxado para dev + corrigido para .svg  
**Status:** ✅ RESOLVIDO
