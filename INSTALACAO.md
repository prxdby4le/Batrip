# Guia de Instalação - Batrip

## Instalação Rápida

### Pré-requisitos
- Docker e Docker Compose instalados
- Portas 8080, 8081 e 3307 disponíveis

### Passos

1. **Clone o repositório** (se aplicável) ou extraia os arquivos

2. **Execute o Docker Compose:**
   ```bash
   docker compose up -d --build
   ```

3. **Aguarde a inicialização** (pode levar 1-2 minutos na primeira vez)

4. **Acesse a aplicação:**
   - **Site:** http://localhost:8080/
   - **phpMyAdmin:** http://localhost:8081/ (Server: `db`, User: `root`, Password: `root`)

5. **Inicialize dados de teste (opcional):**
   - Acesse: http://localhost:8080/init_data.php
   - Isso criará:
     - Admin: `admin@batrip.com` / `admin123`
     - Usuário demo: `usuario@exemplo.com` / `123456`

## Configurações Automáticas

O sistema está configurado para funcionar automaticamente:

- ✅ **Banco de dados:** Criado automaticamente com todas as tabelas necessárias
- ✅ **Dependências PHP:** Instaladas automaticamente via Composer
- ✅ **Apache:** Configurado para servir de `/public` com mod_rewrite habilitado
- ✅ **Bootstrap:** Carregado via CDN (não requer arquivos locais)
- ✅ **Assets:** Servidos diretamente de `public/assets/`

## Variáveis de Ambiente Opcionais

O site funciona **sem** essas variáveis, mas algumas funcionalidades podem ter comportamento limitado:

### SuperFrete (Cálculo de Frete)
```bash
SUPERFRETE_TOKEN=seu_token_aqui
SUPERFRETE_CEP_ORIGEM=04696906
SUPERFRETE_SERVICES=1,2
```

**Sem o token:** O cálculo de frete não funcionará, mas o resto do site funciona normalmente.

### Mercado Pago (Pagamentos)
```bash
MERCADOPAGO_ACCESS_TOKEN=seu_access_token
MERCADOPAGO_PUBLIC_KEY=sua_public_key
```

**Sem as chaves:** O pagamento real não funcionará, mas o checkout pode ser finalizado em modo de teste.

## Estrutura de Diretórios Importante

```
Batrip/
├── public/              # DocumentRoot do Apache
│   ├── assets/          # CSS, JS, imagens (servidos diretamente)
│   ├── uploads/         # Uploads de usuários (profile_bg, etc)
│   └── index-mvc.php    # Front controller MVC
├── assets/              # Assets originais (copiados para public/assets/)
├── app/                  # Código MVC (Controllers, Models, Views)
├── config/               # Configurações (routes, database, etc)
└── database/             # Scripts SQL de inicialização
```

## Verificação Pós-Instalação

Após executar `docker compose up -d --build`, verifique:

1. **Containers rodando:**
   ```bash
   docker compose ps
   ```
   Deve mostrar 3 containers: `batrip-web`, `batrip-db`, `batrip-phpmyadmin`

2. **Acesse o site:**
   - http://localhost:8080/ deve carregar a página inicial

3. **Verifique logs (se houver problemas):**
   ```bash
   docker compose logs web
   docker compose logs db
   ```

## Troubleshooting

### Site não carrega (404)
- Verifique se os containers estão rodando: `docker compose ps`
- Verifique os logs: `docker compose logs web`
- Limpe o cache do navegador (Ctrl+Shift+R)

### CSS/JS não carregam
- O Bootstrap é carregado via CDN, então deve funcionar automaticamente
- Verifique o console do navegador para erros

### Banco de dados não conecta
- Aguarde alguns segundos após `docker compose up` (o MySQL precisa inicializar)
- Verifique: `docker compose logs db`

### Imagens não aparecem
- As imagens de produtos são servidas via `product-image.php` e `set-image.php`
- As imagens de perfil são servidas via `serve-upload.php`
- Verifique se os diretórios `public/uploads/` existem e têm permissões corretas

## Funcionalidades que Funcionam Sem Configuração Adicional

✅ Página inicial
✅ Catálogo de produtos
✅ Detalhes de produto
✅ Carrinho de compras
✅ Cadastro e login de usuários
✅ Perfil do usuário
✅ Área administrativa (após criar admin via init_data.php)
✅ Checkout completo (sem pagamento real)
✅ Finalização de pedidos (modo teste)

## Funcionalidades que Requerem Configuração

⚠️ **Cálculo de frete real:** Requer `SUPERFRETE_TOKEN`
⚠️ **Pagamento real (PIX/Cartão):** Requer `MERCADOPAGO_ACCESS_TOKEN` e `MERCADOPAGO_PUBLIC_KEY`

**Nota:** O site funciona completamente sem essas configurações, mas essas funcionalidades específicas terão comportamento limitado ou simulado.

## Suporte

Em caso de problemas, verifique:
1. Logs do Docker: `docker compose logs web`
2. Logs do Apache: `docker compose exec web tail -f /var/log/apache2/error.log`
3. Logs do PHP: `docker compose exec web tail -f /var/log/apache2/error.log`

