
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
- Bootstrap 5.3

---

## Estrutura de Pastas

Batrip/
├── includes/               # Includes PHP (nav, footer, etc.)
├── public/
│   ├── assets/             # Arquivos estáticos servidos (css, js, img, materials)
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── materials/
│   ├── produtos/           # Páginas de produtos
│   ├── registros/          # Login, cadastro, redefinição de senha, perfil
│   ├── adm/                # Páginas administrativas
│   ├── checkout/           # Processo de compra (carrinho, endereço, frete, revisão, pagamento)
│   ├── index.php           # Página inicial
│   └── sobre.php           # Sobre a marca
└── README.md

---

## Como rodar com Docker

Pré-requisitos:

- Docker e Docker Compose instalados

Passos:

1. Build e subir containers

   ```bash
   docker compose up --build -d
   ```

1. Acesse a aplicação

   - App: <http://localhost:8080/>
   - phpMyAdmin: <http://localhost:8081/> (Server: db, user: root, pass: root)

1. (Opcional) Ver logs

   ```bash
   docker compose logs -f web
   ```

Variáveis importantes:

- BASE_URL já configurada no docker-compose para `http://localhost:8080/`
- DocumentRoot aponta para `/public` (URL base é a raiz `/`)

### Inicialização de dados

- O MySQL é criado com base no arquivo `database/batrip_full.sql`.
- Para criar um admin com senha compatível (hash de senha) e alguns dados de teste, acesse:
   - <http://localhost:8080/init_data.php>
   - Contas criadas:
      - Admin: `admin@batrip.com` / `admin123`
      - Usuário demo: `usuario@exemplo.com` / `123456`
   - Você pode remover esse arquivo em produção.

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

