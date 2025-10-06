# Guia de Migração: Upload de Imagens no Banco de Dados

## 📋 O que foi alterado?

O sistema agora armazena imagens dos produtos diretamente no banco de dados MySQL como BLOB, em vez de usar URLs/caminhos de arquivos.

## 🔧 Passos para Implementação

### 1. Atualizar o Banco de Dados

Execute o script SQL para alterar a estrutura da tabela `products`:

```bash
# No Docker (se estiver usando)
docker exec -i batrip-mysql mysql -uroot -proot batrip < database/alter_products_image.sql

# Ou via phpMyAdmin/MySQL Workbench
# Abra e execute o arquivo: database/alter_products_image.sql
```

**Ou execute manualmente:**

```sql
ALTER TABLE products 
ADD COLUMN image_type VARCHAR(50) DEFAULT 'image/jpeg' AFTER image;

ALTER TABLE products 
MODIFY COLUMN image MEDIUMBLOB NULL;
```

### 2. Configurar PHP para aceitar uploads maiores (Opcional)

No arquivo `docker/php-dev.ini` ou `php.ini`:

```ini
upload_max_filesize = 5M
post_max_size = 6M
memory_limit = 256M
```

Reinicie o container Docker se necessário:

```bash
docker-compose restart
```

### 3. Testar o Sistema

1. **Acesse a área administrativa:**
   - http://localhost/public/adm/index-adm.php

2. **Adicione um novo produto:**
   - Clique em "Novo Produto"
   - Preencha os dados
   - Faça upload de uma imagem (JPG, PNG, WEBP ou GIF até 5MB)
   - Salve

3. **Edite um produto existente:**
   - Se tinha imagem antiga (URL), ela será mantida até você fazer upload de uma nova
   - Faça upload de uma nova imagem para substituir

4. **Visualize no site:**
   - Acesse http://localhost/public/index.php
   - As imagens devem carregar normalmente

## 📁 Arquivos Modificados

### Novos Arquivos:
- ✅ `database/alter_products_image.sql` - Script de migração do banco
- ✅ `public/adm/products/image.php` - Serve imagens no admin
- ✅ `public/product-image.php` - Serve imagens na área pública
- ✅ `database/MIGRATION_GUIDE.md` - Este arquivo

### Arquivos Alterados:
- ✅ `public/adm/products/form.php` - Formulário com upload
- ✅ `public/adm/products/save.php` - Processa upload e salva no banco
- ✅ `public/adm/index-adm.php` - Lista produtos com imagens do banco
- ✅ `public/index.php` - Exibe produtos com imagens do banco
- ✅ `public/produto.php` - Página de produto individual
- ✅ `includes/product-page.php` - Template de exibição

## 🎯 Como Funciona?

### Upload de Imagem:
1. Usuário seleciona arquivo no formulário
2. `save.php` valida tipo MIME e tamanho
3. Imagem é lida como binário com `file_get_contents()`
4. Dados binários são salvos na coluna `image` (MEDIUMBLOB)
5. Tipo MIME é salvo na coluna `image_type`

### Exibição de Imagem:
1. HTML chama: `<img src="product-image.php?id=123">`
2. `product-image.php` busca imagem no banco pelo ID
3. Define header `Content-Type` com o tipo MIME correto
4. Envia dados binários da imagem
5. Navegador renderiza a imagem normalmente

## ✨ Vantagens

- ✅ Não precisa gerenciar arquivos no servidor
- ✅ Backup do banco inclui as imagens
- ✅ Controle de acesso centralizado
- ✅ Cache automático (1 hora no admin, 1 dia no público)
- ✅ Validação de tipo MIME e tamanho

## ⚠️ Considerações

- 📦 Imagens aumentam o tamanho do banco de dados
- 🔒 Limite de 16MB por imagem (MEDIUMBLOB)
- 🚀 Para muitas imagens, considere CDN no futuro
- 💾 Faça backups regulares do banco de dados

## 🐛 Troubleshooting

### Erro "headers already sent"
✅ Já resolvido com `ob_start()` no início dos arquivos

### Imagem não carrega
- Verifique se a coluna `image_type` existe
- Confirme que o tipo MIME está correto
- Teste acessando diretamente: `product-image.php?id=1`

### Upload falha
- Verifique permissões da pasta `/tmp` no Docker
- Confirme `upload_max_filesize` no PHP
- Veja logs de erro: `docker logs batrip-php`

### Produtos antigos sem imagem
- Execute uma migração manual dos URLs antigos
- Ou faça re-upload das imagens através do admin

## 📝 Próximos Passos

1. ✅ Testar criação de produtos com imagem
2. ✅ Testar edição de produtos existentes
3. ✅ Validar exibição na área pública
4. ⬜ Opcional: Criar ferramenta de migração para produtos antigos
5. ⬜ Opcional: Adicionar compressão/resize automático de imagens

---

**Criado em:** 06/10/2025  
**Versão:** 1.0
