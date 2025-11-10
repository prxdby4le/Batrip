# Configuração de Caminhos e Referências - Batrip E-commerce

## 📋 Resumo das Correções

Este documento descreve as correções realizadas no sistema de caminhos e referências do Batrip para garantir funcionamento correto em diferentes ambientes (localhost, subdiretório, produção).

---

## 🔧 Arquivos Criados/Modificados

### 1. `.htaccess` - Configuração Apache ✅
**Arquivo:** `public/.htaccess`

**Propósito:** Redirecionar todas as requisições para `index.php` e aplicar regras de segurança.

**Configurações principais:**
```apache
RewriteEngine On
RewriteBase /Batrip/  # ← Ajuste conforme ambiente

# Permite acesso direto a arquivos e diretórios existentes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Redireciona tudo para index.php
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Recursos de segurança:**
- ✅ Desabilita listagem de diretórios
- ✅ Headers de segurança (X-Frame-Options, X-Content-Type-Options, etc)
- ✅ Proteção do arquivo `.env`
- ✅ Cache de assets (imagens, CSS, JS)
- ✅ Compressão GZIP

**Como ajustar para diferentes ambientes:**

```apache
# Local em subdiretório
RewriteBase /Batrip/

# Produção na raiz
RewriteBase /

# Produção em subdiretório
RewriteBase /loja/
```

---

### 2. `helpers.php` - Funções Auxiliares Globais ✅
**Arquivo:** `config/helpers.php`

**Propósito:** Centralizar funções auxiliares para geração de URLs, redirecionamentos, e outras utilidades.

#### Funções de URL

##### `url($path)` - URL Completa
```php
echo url('produtos');           // → http://localhost/Batrip/produtos
echo url('cart/add');           // → http://localhost/Batrip/cart/add
echo url();                     // → http://localhost/Batrip/
```

##### `asset($path)` - Assets (CSS, JS, Imagens)
```php
echo asset('css/styles.css');   // → http://localhost/Batrip/assets/css/styles.css
echo asset('js/cart.js');       // → http://localhost/Batrip/assets/js/cart.js
```

##### `route($route)` - Alias Semântico
```php
echo route('login');            // → http://localhost/Batrip/login
```

##### `product_image($id, $imgId)` - Imagens de Produtos
```php
echo product_image(123);        // → http://localhost/Batrip/product-image.php?id=123
echo product_image(123, 456);   // → http://localhost/Batrip/product-image.php?img_id=456
```

#### Funções de Navegação

##### `redirect($path, $code)` - Redirecionamento
```php
redirect('login');              // Redireciona para login
redirect('produtos', 301);      // Redirect permanente
```

##### `back($default)` - Voltar
```php
back('home');                   // Volta para página anterior ou home
```

#### Funções de Navegação Ativa

##### `is_active_route($route, $exact)` - Verificar Rota Ativa
```php
if (is_active_route('produtos')) {
    echo "Estamos em produtos ou subpágina";
}

if (is_active_route('produtos', true)) {
    echo "Estamos exatamente em /produtos";
}
```

##### `active_class($route, $class, $exact)` - Classe CSS Ativa
```php
<a class="nav-link <?php echo active_class('produtos'); ?>" href="...">
    Produtos
</a>
```

#### Funções de Formulário

##### `csrf_token()` - Token CSRF
```php
$token = csrf_token();
```

##### `csrf_field()` - Campo Hidden CSRF
```php
<form method="POST">
    <?php echo csrf_field(); ?>
    <!-- Gera: <input type="hidden" name="csrf_token" value="..."> -->
</form>
```

##### `old($key, $default)` - Valor Antigo (Após Validação)
```php
<input name="email" value="<?php echo old('email', $user['email'] ?? ''); ?>">
```

#### Funções de Mensagens

##### `flash($type, $message)` - Definir Flash
```php
flash('success', 'Produto adicionado!');
flash('error', 'Erro ao salvar.');
```

##### `get_flash($type)` - Recuperar Flash
```php
$message = get_flash('success');  // Lê e limpa da sessão
```

#### Funções de Segurança

##### `e($string)` - Escape HTML
```php
echo e($user_input);  // Protege contra XSS
```

#### Funções de Debug

##### `dd(...$vars)` - Dump and Die
```php
dd($user, $product);  // Exibe variáveis e para execução
```

##### `dump(...$vars)` - Dump
```php
dump($data);  // Exibe variáveis e continua
```

#### Funções de Ambiente

##### `env($key, $default)` - Variável de Ambiente
```php
$debug = env('APP_DEBUG', false);
```

##### `config($key, $default)` - Configuração
```php
$appName = config('app.name');  // → 'Batrip'
```

---

### 3. `config.php` - Atualizado ✅
**Arquivo:** `config/config.php`

**Mudança:** Adicionado carregamento automático do `helpers.php`

```php
// Carrega funções auxiliares globais
require_once __DIR__ . '/helpers.php';
```

Agora todas as funções auxiliares estão disponíveis globalmente em todo o projeto.

---

## 🎯 Estrutura de Caminhos

### Como Funciona

```
┌─────────────────────────────────────────────────────┐
│  Usuário Acessa: http://localhost/Batrip/produtos  │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  1. .htaccess: Redireciona para index.php          │
│     (se arquivo não existe)                         │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  2. Request::parsePath(): Remove /Batrip/           │
│     → /produtos                                      │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  3. Router: Encontra rota /produtos                 │
│     → ProductController::index()                    │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  4. View: Usa BASE_URL ou helpers                   │
│     BASE_URL . 'cart/add' OU url('cart/add')       │
│     → http://localhost/Batrip/cart/add              │
└─────────────────────────────────────────────────────┘
```

### Caminhos no HTML

#### ❌ ANTES (Problemático)
```php
<!-- Pode não funcionar em subdiretórios -->
<link href="assets/css/styles.css" rel="stylesheet">
<a href="produtos">Produtos</a>
```

#### ✅ DEPOIS (Correto)

**Opção 1: Tag `<base>` (Já implementada)**
```html
<head>
    <base href="<?php echo BASE_URL; ?>">
    <!-- Agora caminhos relativos funcionam -->
    <link href="assets/css/styles.css" rel="stylesheet">
</head>

<a href="produtos">Produtos</a>  <!-- Funciona! -->
```

**Opção 2: Helpers (Nova forma recomendada)**
```php
<link href="<?php echo asset('css/styles.css'); ?>" rel="stylesheet">
<a href="<?php echo url('produtos'); ?>">Produtos</a>
```

**Opção 3: Constante BASE_URL (Ainda funciona)**
```php
<a href="<?php echo BASE_URL; ?>produtos">Produtos</a>
```

---

## 📂 Estrutura de Arquivos

```
Batrip/
├── public/
│   ├── .htaccess          ← NOVO! Configuração Apache
│   ├── index.php          ← Entry point
│   ├── product-image.php  ← Serve imagens
│   └── assets/            ← CSS, JS, imagens estáticas
│       ├── css/
│       ├── js/
│       └── img/
├── config/
│   ├── config.php         ← MODIFICADO (carrega helpers)
│   ├── helpers.php        ← NOVO! Funções auxiliares
│   └── database.php
├── app/
│   ├── Core/
│   │   ├── Request.php    ← parsePath() remove base
│   │   └── Router.php
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   │   └── layouts/
│   │       ├── main.php   ← Tem <base href>
│   │       ├── auth.php   ← Tem <base href>
│   │       └── admin.php  ← Tem <base href>
│   └── Helpers/
```

---

## 🌐 Configuração para Diferentes Ambientes

### Local (XAMPP/WAMP em Subdiretório)

**.env**
```env
BASE_URL=http://localhost/Batrip/
```

**public/.htaccess**
```apache
RewriteBase /Batrip/
```

### Local (Servidor de Desenvolvimento PHP)

```bash
cd public
php -S localhost:8000
```

**.env**
```env
BASE_URL=http://localhost:8000/
```

**public/.htaccess**
```apache
RewriteBase /
```

### Produção (Raiz do Domínio)

**.env**
```env
BASE_URL=https://batrip.com/
```

**public/.htaccess**
```apache
RewriteBase /
```

### Produção (Subdiretório)

**.env**
```env
BASE_URL=https://exemplo.com/loja/
```

**public/.htaccess**
```apache
RewriteBase /loja/
```

---

## 🔍 Verificação de Funcionamento

### 1. Teste de Assets (CSS/JS)

Abra o site no navegador e verifique:
- ✅ Estilos estão aplicados
- ✅ JavaScript funciona
- ✅ Console do navegador sem erros 404

### 2. Teste de Links

- ✅ Navegação funciona (Home, Produtos, Login, etc)
- ✅ Formulários submetem corretamente
- ✅ Redirecionamentos funcionam

### 3. Teste de Imagens de Produtos

```php
// No controller ou view
$imageUrl = product_image(123);
// Deve gerar: http://localhost/Batrip/product-image.php?id=123
```

### 4. Verificar .htaccess Funcionando

Acesse URLs limpas:
```
http://localhost/Batrip/produtos       ← Deve funcionar
http://localhost/Batrip/produto/123    ← Deve funcionar
```

Se der 404, verifique:
- ✅ `mod_rewrite` está habilitado no Apache
- ✅ `AllowOverride All` está configurado no Virtual Host
- ✅ Arquivo `.htaccess` existe em `public/`

---

## 🐛 Solução de Problemas

### Assets não carregam (CSS/JS com 404)

**Problema:** `http://localhost/Batrip/assets/css/styles.css` retorna 404

**Soluções:**
1. Verifique se `assets/` está em `public/assets/` (não em `Batrip/assets/`)
2. Verifique se a tag `<base href>` está no layout
3. Use helpers: `asset('css/styles.css')`

### Links quebram ao navegar

**Problema:** Link funciona na home, mas quebra em `/produtos`

**Causa:** Caminhos relativos sem `<base>`

**Soluções:**
1. Certifique-se que todos os layouts têm `<base href="<?php echo BASE_URL; ?>">`
2. Use helper `url()` em vez de caminhos relativos
3. Sempre use barra inicial: `/produtos` em vez de `produtos`

### Erro 500 ao acessar site

**Problema:** Erro interno do servidor

**Verificar:**
1. `mod_rewrite` habilitado: `sudo a2enmod rewrite`
2. `.htaccess` com sintaxe correta
3. `AllowOverride All` no Virtual Host
4. Logs de erro: `LOGS_PATH/error.log`

### Páginas admin não funcionam

**Problema:** `/adm/produtos` dá 404

**Verificar:**
1. Rota registrada em `public/index.php`
2. Middleware de autenticação funcionando
3. Controller existe em `app/Controllers/Admin/`

---

## ✅ Checklist de Implementação

### Desenvolvimento Local
- [x] Criar `public/.htaccess`
- [x] Criar `config/helpers.php`
- [x] Modificar `config/config.php` para carregar helpers
- [x] Tag `<base>` em todos os layouts
- [x] Testar assets (CSS, JS)
- [x] Testar navegação
- [x] Testar formulários
- [ ] Testar imagens de produtos

### Antes de Deploy em Produção
- [ ] Ajustar `BASE_URL` no `.env`
- [ ] Ajustar `RewriteBase` no `.htaccess`
- [ ] Testar em ambiente de staging
- [ ] Verificar logs de erro
- [ ] Testar todas as rotas
- [ ] Verificar imagens carregam
- [ ] Testar checkout completo

---

## 📚 Referências

### Documentação Interna
- `README.md` - Visão geral do projeto
- `STRUCTURE.md` - Estrutura de arquivos
- `ROUTER.md` - Sistema de rotas
- `SECURITY.md` - Práticas de segurança

### Arquivos Relacionados
- `app/Core/Request.php` - Parsing de caminhos
- `app/Core/Router.php` - Sistema de rotas
- `config/config.php` - Configurações gerais
- `public/index.php` - Entry point

---

## 📝 Notas Importantes

1. **Sempre use helpers quando possível** - São mais legíveis e fáceis de manter
2. **Tag `<base>` facilita desenvolvimento** - Permite caminhos relativos simples
3. **`.htaccess` é essencial** - Sem ele, URLs limpas não funcionam
4. **Teste em ambiente similar à produção** - Evita surpresas no deploy

---

**Última atualização:** 09/10/2025  
**Versão:** 1.0.0  
**Autor:** Sistema Batrip MVC
