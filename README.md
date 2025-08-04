
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

- PHP 7+
- HTML5
- CSS3
- JavaScript (ES6+)
- Bootstrap 5.3

---

## Estrutura de Pastas

Batrip/
├── assets/
│   ├── css/                # Arquivos CSS
│   ├── js/                 # Scripts JS
│   ├── img/                # Imagens do site
│   └── materials/          # Materiais gráficos
├── includes/               # Includes PHP (nav, footer, etc.)
├── public/
│   ├── produtos/           # Páginas de produtos
│   ├── registros/          # Login, cadastro, redefinição de senha, perfil
│   ├── adm/                # Páginas administrativas
│   ├── checkout/           # Processo de compra (carrinho, endereço, frete, revisão, pagamento)
│   ├── index.php           # Página inicial
│   └── sobre.php           # Sobre a marca
└── README.md

---

## Como rodar o projeto

1. Clone o repositório e coloque na pasta do seu servidor local (ex: XAMPP, WAMP)
2. Acesse `http://localhost/Batrip/public/index.php` no navegador
3. Para testar o fluxo de compra, adicione produtos ao carrinho e siga as etapas do checkout

---

## Observações

- O pagamento é simulado e redireciona para serviços third party (ex: Mercado Pago, Pix)
- Para produção, recomenda-se configurar HTTPS e variáveis de ambiente para integrações reais

