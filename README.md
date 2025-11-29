
# Batrip

Loja online da marca Batrip, desenvolvida em PHP, HTML, CSS e JavaScript. O projeto possui estrutura modular, páginas dinâmicas e fluxo de compra completo, pronto para integração com serviços de pagamento.

---

## Funcionalidades

- Página inicial com destaques, lançamentos e carrossel de artistas
- Catálogo de produtos (camisetas, conjuntos, etc.)
- Página de detalhes de produto
- Carrinho de compras dinâmico
- Fluxo de checkout completo:
  - Endereço com busca de CEP automática
  - Cálculo e escolha de frete
  - Revisão do pedido
  - Redirecionamento para pagamento externo (ex: Mercado Pago, Pix)
- Cadastro, login, redefinição de senha e perfil de usuário
- Área administrativa para gestão
- Layout responsivo (desktop, tablet, mobile)

### Includes e Organização

- Cabeçalho, navbar, footer e barra lateral do carrinho como includes reutilizáveis
- Código otimizado para manutenção e expansão

---

## Tecnologias

- PHP 8.2+
- HTML5
- CSS3
- JavaScript (ES6+)
- Bootstrap 5.x (via CDN)

---

## Como rodar o projeto (Linux-friendly)

### 1. Clonar o repositório e ir para a branch correta

```bash
git clone git@github.com:SEU_USUARIO/Batrip.git
cd Batrip

# Branch otimizada para rodar em Linux/WSL
git checkout linux-friendy-batrip
```

> Caso já tenha o repositório clonado, apenas entre na pasta do projeto e rode:
>
> ```bash
> git fetch origin
> git checkout linux-friendy-batrip
> git pull origin linux-friendy-batrip
> ```

### 2. Configurar variáveis de ambiente (opcional, mas recomendado)

```bash
cp .env.example .env
nano .env   # ou o editor de texto de sua preferência
```

Preencha as chaves das integrações desejadas (ex.: Mercado Pago e SuperFrete).  
Se não configurar nada, o projeto ainda sobe normalmente, mas pagamentos reais e cálculo de frete em produção não estarão disponíveis.

### 3. Subir os containers (Docker)

> Todos os comandos abaixo são compatíveis com Linux nativo e WSL2.

```bash
docker compose up -d --build
```

- **Primeira vez:** pode levar 1–2 minutos para baixar as imagens, instalar dependências e inicializar o banco.
- As portas padrão são:
  - Aplicação: `http://localhost:8080`
  - phpMyAdmin: `http://localhost:8081`

### 4. Popular dados de teste (opcional)

Acesse, no navegador:

- `http://localhost:8080/init_data.php`

Isso criará, entre outros:

- Admin: `admin@batrip.com` / `admin123`
- Usuário demo: `usuario@exemplo.com` / `123456`

### 5. Encerrar o ambiente

Para desligar os containers:

```bash
docker compose down
```

Se quiser limpar os volumes (banco de dados e outros dados persistidos):

```bash
docker compose down -v
```

---

## Estrutura de Pastas

```text
Batrip/
├── app/                    # MVC (Controllers, Models, Views, Core, Helpers)
├── assets/                 # Assets de desenvolvimento (separados de public quando necessário)
├── config/                 # Configurações gerais (DB, rotas, helpers globais)
├── database/               # Migrations, seeds e scripts SQL auxiliares
├── docker/                 # Configurações do Apache/PHP e vhosts
├── docs/                   # Documentação adicional do projeto
├── includes/               # Includes PHP legados/compartilhados
├── logs/                   # Logs de aplicação
├── public/                 # Raiz pública do servidor (DocumentRoot)
│   ├── assets/             # Arquivos estáticos servidos (css, js, img, fonts, materials)
│   ├── adm/                # Área administrativa
│   ├── ajax/               # Endpoints AJAX
│   ├── checkout/           # Fluxo de checkout e frete
│   ├── produtos/           # Listagem e páginas de produtos
│   ├── registros/          # Login, cadastro, recuperação de senha, perfil
│   ├── includes/           # Partials usados no front (header, footer etc.)
│   ├── uploads/            # Uploads públicos (imagens de produto, perfil, etc.)
│   ├── index.php           # Entrada principal da aplicação
│   └── sobre.php           # Página de informações sobre a marca
├── routes/                 # Definições de rotas (ex.: web.php)
├── tests/                  # Testes automatizados
├── tools/                  # Scripts utilitários
├── uploads/                # Diretório de uploads usado por scripts internos
├── vendor/                 # Dependências instaladas via Composer
├── autoload.php
├── composer.json
├── composer.lock
├── docker-compose.yml
├── Dockerfile
├── phinx.php               # Configuração do Phinx para migrations
└── README.md
```

---

## Como rodar com Docker

### Instalação Rápida

**Pré-requisitos:**
- Docker e Docker Compose instalados
- Portas 8080, 8081 e 3307 disponíveis

**Passos:**

1. **Execute o Docker Compose:**
   ```bash
   docker compose up -d --build
   ```

2. **Aguarde a inicialização** (1-2 minutos na primeira vez)

3. **Acesse a aplicação:**
   - **Site:** http://localhost:8080/
   - **phpMyAdmin:** http://localhost:8081/ (Server: `db`, User: `root`, Password: `root`)

4. **Inicialize dados de teste (opcional):**
   - Acesse: http://localhost:8080/init_data.php
   - Isso criará:
     - Admin: `admin@batrip.com` / `admin123`
     - Usuário demo: `usuario@exemplo.com` / `123456`

### Configurações Automáticas

O sistema está configurado para funcionar automaticamente:

- ✅ **Banco de dados:** Criado automaticamente com todas as tabelas necessárias
- ✅ **Dependências PHP:** Instaladas automaticamente via Composer
- ✅ **Apache:** Configurado para servir de `/public` com mod_rewrite habilitado
- ✅ **Bootstrap:** Carregado via CDN (não requer arquivos locais)
- ✅ **Assets:** Servidos diretamente de `public/assets/`
- ✅ **Diretórios de upload:** Criados automaticamente com permissões corretas

### Configuração de APIs (Opcional mas Recomendado)

Para funcionalidades completas de pagamento e frete, configure as APIs:

1. **Copie o arquivo de exemplo:**
   ```bash
   cp .env.example .env
   ```

2. **Edite o `.env`** e preencha com suas credenciais:
   - **Mercado Pago**: Obtenha em https://www.mercadopago.com.br/developers/panel
   - **SuperFrete**: Obtenha em https://superfrete.com/

3. **Reinicie os containers:**
   ```bash
   docker compose restart web
   ```

**Variáveis necessárias:**
- `MERCADOPAGO_ACCESS_TOKEN` - Token de acesso do Mercado Pago
- `MERCADOPAGO_PUBLIC_KEY` - Chave pública do Mercado Pago
- `SUPERFRETE_TOKEN` - Token da API SuperFrete
- `SUPERFRETE_CEP_ORIGEM` - CEP de origem (padrão: 04696906)
- `SUPERFRETE_SERVICES` - Serviços disponíveis (padrão: 1,2)

📖 **Guia completo:** Consulte os comentários em `.env.example`, `docker-compose.yml` e nos controllers de checkout para entender cada variável e integração.

**Nota:** O site funciona sem essas configurações, mas pagamentos reais e cálculo de frete não estarão disponíveis.

### Verificação Pós-Instalação

Após executar `docker compose up -d --build`:

1. **Verifique containers:**
   ```bash
   docker compose ps
   ```
   Deve mostrar 3 containers rodando.

2. **Acesse o site:**
   - http://localhost:8080/ deve carregar a página inicial

3. **Verifique logs (se necessário):**
   ```bash
   docker compose logs web
   ```

### Troubleshooting

- **Site não carrega:** Verifique `docker compose ps` e `docker compose logs web`
- **CSS/JS não carregam:** Limpe o cache do navegador (Ctrl+Shift+R)
- **Banco não conecta:** Aguarde alguns segundos após `docker compose up` (MySQL precisa inicializar)

Troubleshooting rápido:

- 404 nas rotas limpas: verifique se `mod_rewrite` está ativo (já habilitado na imagem) e `.htaccess` existe em `public/`
- CSS/JS não carregam: confirme que assets estão em `public/assets/` e que o `<base href>` aponta para `BASE_URL`
- CSP bloqueando recursos: o projeto evita CDNs para ícones (usa SVGs locais). Google Fonts/cdnjs são permitidos; ajuste `includes/head.php` e `docker/apache-vhost.conf` se preferir CSP mais rígida.

### Fluxo de checkout

1) Carrinho → 2) Checkout (dados pessoais + endereço) → 3) Frete (`/frete`) → 4) Revisão no checkout (com frete selecionado) → 5) Processar → 6) Sucesso.

#### Frete

- Página `/frete` recebe CEP, endereço e dimensões/peso.
- Cálculo gera cotações simuladas (PAC, SEDEX) com custo e prazo.
- Seleção de frete salva na sessão em `$_SESSION['shipping']` (`method`, `cost`, `zipcode`).
- Total mostrado no checkout = subtotal do carrinho + custo do frete.

#### Persistência do Pedido

`CheckoutController@process` grava em `orders` (ver `database/add_shipping_columns_orders.sql` para colunas complementares):

| Campo | Origem |
|-------|--------|
| subtotal | Soma dos itens (`CartHelper::getTotal()`) |
| shipping_method | Método escolhido (PAC/SEDEX) |
| shipping_cost | Custo calculado |
| total | subtotal + shipping_cost |
| items | JSON dos itens `{id,title,price,size,qty}` |

Se sua tabela não tiver `shipping_method` / `shipping_cost` use o script de migração.

#### CSRF

Todas as ações POST sensíveis validam token: `cart/*`, `frete/*`, `checkout/process`.

- Token gerado na sessão e exposto via `<meta name="csrf-token">` e campo oculto `csrf_token` nos forms.
- Para AJAX, enviar header `X-CSRF-Token`.

#### Sem JavaScript

- Adição de produto via form redireciona para `/cart` com mensagem.
- Update/remove/clear do carrinho possuem fallback; com JS usam JSON.
- Calcular e selecionar frete funcionam apenas com formulário.

#### Validações

- Tamanho validado e normalizado em `CartController@add` (`CartHelper::validateSize`).
- Quantidade limitada (1–99).
- CEP validado por regex `\d{5}-?\d{3}` em `ShippingController`.
- CSRF obrigatório em todos POSTs.

#### Próximas melhorias sugeridas

- Integração real de frete (Correios/Ship API).
- Verificação e reserva de estoque antes de criar pedido.
- Rotação periódica de CSRF + SameSite=Strict nos cookies.
- Testes unitários para `CartHelper`, `ShippingController` e fluxo de checkout.

#### Tabela de tamanhos no produto

Se você cadastrar uma "tabela de tamanhos" no admin, a página de produto exibirá um bloco dobrável (collapse) chamado "Tabela de tamanhos" automaticamente.

Campos suportados no produto (o primeiro não vazio será usado):

- `size_table`, `size_table_html`, `size_chart`, `size_chart_html`
- `tabela_tamanhos`, `guia_tamanhos`, `tabela_medidas`
- Imagem opcional: `size_table_image`, `size_chart_image`, `tabela_tamanhos_imagem`

Observações:

- O conteúdo HTML passado pelo admin sofre uma sanitização básica para remover `<script>`, `<style>`, atributos que começam com `on*` e protocolos `javascript:`.
- Caso prefira, você pode fornecer apenas uma imagem da tabela que também será exibida.
- Se nenhum desses campos estiver presente, a seção não será mostrada.

### Área administrativa

- Login com admin acima e acesse `/adm/index-adm.php`.
- Produtos: criar/editar, ativar/desativar, excluir, upload de imagens (com miniaturas geradas quando GD estiver disponível), ordenação por arrastar e soltar.
- Dica: use imagens menores para melhor desempenho; derivados `--thumb/--medium/--large` são gerados quando possível.

---

## Observações

- O pagamento é simulado e redireciona para serviços third party (ex: Mercado Pago, Pix)
- Para produção, recomenda-se configurar HTTPS e variáveis de ambiente para integrações reais

