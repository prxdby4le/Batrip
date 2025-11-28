# 🔧 Configuração das APIs - Mercado Pago e SuperFrete

Este guia explica como configurar as APIs do Mercado Pago e SuperFrete para que o site funcione completamente.

## 📋 Pré-requisitos

- Conta no Mercado Pago (para pagamentos)
- Conta no SuperFrete (para cálculo de frete)

## 🚀 Configuração Rápida

### 1. Criar arquivo `.env`

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

### 2. Preencher credenciais

Edite o arquivo `.env` e preencha com suas credenciais reais:

```env
MERCADOPAGO_ACCESS_TOKEN=seu_access_token_aqui
MERCADOPAGO_PUBLIC_KEY=sua_public_key_aqui
SUPERFRETE_TOKEN=seu_token_superfrete_aqui
```

### 3. Reiniciar containers

```bash
docker compose down
docker compose up -d
```

## 🔑 Mercado Pago

### Obter Credenciais

1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Faça login na sua conta
3. Vá em **Suas integrações** → **Credenciais**
4. Escolha entre:
   - **Credenciais de teste** (para desenvolvimento)
   - **Credenciais de produção** (para ambiente real)

### Variáveis Necessárias

- **MERCADOPAGO_ACCESS_TOKEN**: Token de acesso para processar pagamentos no backend
- **MERCADOPAGO_PUBLIC_KEY**: Chave pública para usar no frontend (JavaScript)

### Credenciais de Teste (Sandbox)

Para testar sem usar dinheiro real:

1. Acesse: https://www.mercadopago.com.br/developers/panel/credentials
2. Use as credenciais de **TESTE**
3. Para testar pagamentos, use os cartões de teste:
   - **Aprovado**: 5031 4332 1540 6351 (CVV: 123)
   - **Recusado**: 5031 4332 1540 6351 (CVV: 123)

## 📦 SuperFrete

### Obter Token

1. Acesse: https://superfrete.com/
2. Crie uma conta ou faça login
3. Vá em **Integrações** → **API**
4. Gere um token de API

### Variáveis Necessárias

- **SUPERFRETE_TOKEN**: Token de autenticação da API
- **SUPERFRETE_CEP_ORIGEM**: CEP de origem dos envios (padrão: 04696906)
- **SUPERFRETE_SERVICES**: Serviços disponíveis (padrão: 1,2 = PAC e SEDEX)

### Ambiente de Teste (Sandbox)

Para testar sem custos:

1. Acesse: https://sandbox.superfrete.com/
2. Crie uma conta de teste
3. Obtenha o token de API do ambiente de teste

## ⚙️ Configuração no Docker

O `docker-compose.yml` já está configurado para:

1. **Ler variáveis do arquivo `.env`** automaticamente
2. **Usar valores padrão** se as variáveis não estiverem definidas
3. **Passar as variáveis** para o container web

### Estrutura no docker-compose.yml

```yaml
services:
  web:
    env_file:
      - .env  # Carrega variáveis do .env
    environment:
      # Variáveis com valores padrão se não definidas
      SUPERFRETE_TOKEN: ${SUPERFRETE_TOKEN:-}
      MERCADOPAGO_ACCESS_TOKEN: ${MERCADOPAGO_ACCESS_TOKEN:-}
      MERCADOPAGO_PUBLIC_KEY: ${MERCADOPAGO_PUBLIC_KEY:-}
```

## ✅ Verificação

Após configurar, verifique se está funcionando:

### 1. Verificar variáveis no container

```bash
docker compose exec web env | grep -E "MERCADOPAGO|SUPERFRETE"
```

### 2. Testar Mercado Pago

1. Acesse o checkout do site
2. Tente fazer um pagamento de teste
3. Use um cartão de teste do Mercado Pago

### 3. Testar SuperFrete

1. Acesse a página de frete
2. Digite um CEP válido
3. Verifique se as opções de frete aparecem

## 🔒 Segurança

⚠️ **IMPORTANTE:**

- **NUNCA** commite o arquivo `.env` no Git
- O `.env` já está no `.gitignore`
- Use credenciais de **teste** durante desenvolvimento
- Use credenciais de **produção** apenas em ambiente real

## 🐛 Troubleshooting

### Variáveis não estão sendo carregadas

1. Verifique se o arquivo `.env` existe na raiz do projeto
2. Verifique se as variáveis estão escritas corretamente (sem espaços extras)
3. Reinicie os containers: `docker compose restart web`

### Mercado Pago não funciona

1. Verifique se o `MERCADOPAGO_ACCESS_TOKEN` está correto
2. Verifique se está usando credenciais de teste em ambiente de desenvolvimento
3. Verifique os logs: `docker compose logs web`

### SuperFrete não funciona

1. Verifique se o `SUPERFRETE_TOKEN` está correto
2. Verifique se o `SUPERFRETE_CEP_ORIGEM` é um CEP válido (8 dígitos)
3. Verifique se está usando o ambiente correto (sandbox vs produção)

## 📚 Links Úteis

- **Mercado Pago:**
  - Documentação: https://www.mercadopago.com.br/developers/pt/docs
  - Painel: https://www.mercadopago.com.br/developers/panel
  - Cartões de teste: https://www.mercadopago.com.br/developers/pt/docs/checkout-api/integration-test/test-cards

- **SuperFrete:**
  - Documentação: https://docs.superfrete.com/
  - Sandbox: https://sandbox.superfrete.com/
  - Produção: https://superfrete.com/

