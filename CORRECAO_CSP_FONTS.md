# ✅ Correção CSP - Fontes Bootstrap Icons

## 🔧 Alterações Realizadas

O CSP (Content Security Policy) foi atualizado para permitir fontes do Bootstrap Icons de `https://cdn.jsdelivr.net`.

### Arquivos Corrigidos:

1. **`public/.htaccess`** (linha 46)
   - Adicionado `Header always set` ao invés de `Header set`
   - CSP inclui: `font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net data:;`

2. **`docker/apache-vhost.conf`** (linha 26)
   - Já estava correto com `https://cdn.jsdelivr.net` no `font-src`

### CSP Configurado:

```apache
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' https://sdk.mercadopago.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com 'unsafe-inline' blob:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net data:; img-src 'self' data: blob:; connect-src 'self' https://viacep.com.br; frame-ancestors 'self';"
```

## 🚀 Próximos Passos

### 1. Reiniciar o Docker (se ainda não fez)
```bash
docker-compose restart web
```

### 2. **LIMPAR O CACHE DO NAVEGADOR** ⚠️ IMPORTANTE

O navegador pode estar usando uma versão antiga do CSP em cache. É necessário limpar o cache:

#### Chrome/Edge:
1. Pressione `Ctrl + Shift + Delete`
2. Selecione "Imagens e arquivos em cache"
3. Escolha "Todo o período"
4. Clique em "Limpar dados"

#### Firefox:
1. Pressione `Ctrl + Shift + Delete`
2. Selecione "Cache"
3. Escolha "Tudo"
4. Clique em "Limpar agora"

#### Alternativa (Hard Refresh):
- Windows/Linux: `Ctrl + F5` ou `Ctrl + Shift + R`
- Mac: `Cmd + Shift + R`

### 3. Verificar se está funcionando

1. Abra o DevTools (F12)
2. Vá para a aba **Console**
3. Recarregue a página com `Ctrl + F5`
4. **Não deve aparecer** mais os erros:
   - ❌ `Loading the font 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2' violates...`

## ✅ Como Verificar o CSP Ativo

1. Abra o DevTools (F12)
2. Vá para a aba **Network**
3. Recarregue a página
4. Clique em qualquer requisição (ex: a própria página)
5. Procure por **Response Headers**
6. Encontre `Content-Security-Policy`
7. Verifique se `font-src` contém `https://cdn.jsdelivr.net`

## 🔍 Se o Problema Persistir

Se mesmo após limpar o cache o problema persistir:

1. **Verificar logs do Apache:**
   ```bash
   docker-compose logs web | grep -i csp
   ```

2. **Verificar se o módulo headers está habilitado:**
   ```bash
   docker-compose exec web apache2ctl -M | grep headers
   ```

3. **Testar o CSP diretamente:**
   - Abra qualquer página
   - Inspecione os headers HTTP
   - Verifique se o `Content-Security-Policy` está presente e correto

## 📝 Nota

O servidor Docker já foi reiniciado após as alterações. Se você ainda vê os erros, é quase certamente um problema de **cache do navegador**. Limpe o cache completamente.

