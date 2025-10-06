# Sistema de Múltiplas Imagens por Produto

## 📸 Visão Geral

O sistema agora suporta **múltiplas imagens por produto**, armazenadas no banco de dados MySQL.

## 🗄️ Estrutura do Banco de Dados

### Nova Tabela: `product_images`

```sql
CREATE TABLE product_images (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,           -- FK para products.id
  image MEDIUMBLOB NOT NULL,         -- Imagem em formato binário (até 16MB)
  image_type VARCHAR(50),            -- MIME type (image/jpeg, image/png, etc)
  display_order INT DEFAULT 0,       -- Ordem de exibição
  is_primary TINYINT(1) DEFAULT 0,   -- Se é a imagem principal (capa)
  created_at TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### Relacionamento:
- `products` 1 → N `product_images`
- Cada produto pode ter várias imagens
- Uma imagem é marcada como "principal" (`is_primary = 1`)
- Imagens são ordenadas por `display_order`

## 🚀 Instalação

### 1. Execute o script SQL:

```bash
# Via Docker
docker exec -i batrip-mysql mysql -uroot -proot batrip < database/create_product_images_table.sql

# Ou via MySQL client
mysql -u root -p batrip < database/create_product_images_table.sql
```

### 2. (Opcional) Migrar imagens antigas

Se você tinha imagens na tabela `products`, descomente a seção de migração no arquivo SQL.

## 📋 Funcionalidades

### ✅ Área Administrativa

#### Upload de Múltiplas Imagens
- Selecione vários arquivos de uma vez
- Formatos: JPG, PNG, WEBP, GIF
- Tamanho máximo: 5MB por imagem
- A primeira imagem é automaticamente definida como principal

#### Gerenciar Imagens
- **Visualizar** todas as imagens do produto
- **Definir como principal**: clique no ícone ⭐
- **Remover**: clique no ícone 🗑️ (mínimo 1 imagem por produto)
- **Reordenar**: ordem baseada em `display_order`

### ✅ Área Pública

#### Galeria de Produtos
- Exibe a imagem principal como capa
- Página de produto mostra todas as imagens
- Miniaturas clicáveis para alternar entre imagens
- Fallback para placeholder se não houver imagem

## 🔧 Arquivos do Sistema

### Novos Arquivos:
- ✅ `database/create_product_images_table.sql` - Cria tabela product_images
- ✅ `public/adm/products/image-set-primary.php` - Define imagem principal
- ✅ `public/adm/products/image-delete.php` - Remove imagem
- ✅ `database/MULTIPLE_IMAGES_GUIDE.md` - Este guia

### Arquivos Modificados:
- ✅ `public/adm/products/form.php` - Formulário com upload múltiplo + gerenciamento
- ✅ `public/adm/products/save.php` - Processa múltiplos uploads
- ✅ `public/adm/products/image.php` - Serve imagens (suporta img_id e product_id)
- ✅ `public/product-image.php` - Serve imagens públicas + endpoint JSON
- ✅ `includes/product-page.php` - Galeria com miniaturas

## 🎯 Como Usar

### Adicionar Produto com Imagens:

1. Acesse `/public/adm/index-adm.php`
2. Clique em "Novo Produto"
3. Preencha os dados
4. Clique em "Escolher arquivos" e selecione múltiplas imagens
5. Salve

### Editar Imagens de um Produto:

1. Clique em "Editar" no produto
2. Você verá todas as imagens atuais
3. Para adicionar mais imagens: selecione novos arquivos
4. Para definir como principal: clique no botão ⭐
5. Para remover: clique no botão 🗑️ (mínimo 1 imagem)

### API de Imagens:

```php
// Pegar imagem principal do produto (binário)
GET product-image.php?id=123

// Pegar imagem específica (binário)
GET product-image.php?img_id=456

// Pegar lista de todas as imagens (JSON)
GET product-image.php?id=123&all=1
// Retorna: {"success": true, "images": [{id, url, display_order, is_primary}, ...]}
```

## 🔄 Fluxo de Upload

```
1. Usuário seleciona múltiplas imagens no formulário
   ↓
2. save.php valida cada imagem (tipo MIME, tamanho)
   ↓
3. Inicia transação no banco
   ↓
4. Salva/atualiza dados do produto
   ↓
5. Para cada imagem:
   - Lê conteúdo binário com file_get_contents()
   - Insere em product_images com display_order sequencial
   - Primeira imagem = is_primary se não houver outra
   ↓
6. Commit da transação
   ↓
7. Sucesso!
```

## 🎨 Interface de Gerenciamento

### Formulário de Produto:
```
┌─────────────────────────────────────┐
│ [Imagem 1]  [Imagem 2]  [Imagem 3] │
│  Principal    ⭐ 🗑️      ⭐ 🗑️     │
│  Ordem: 0     Ordem: 1   Ordem: 2  │
└─────────────────────────────────────┘

[Escolher arquivos...] (múltiplos)
Formatos: JPG, PNG, WEBP, GIF
Tamanho máximo: 5MB por imagem
```

### Página do Produto (Público):
```
┌───────────────────────┐
│                       │
│   [Imagem Principal]  │
│                       │
└───────────────────────┘
┌──┐ ┌──┐ ┌──┐ ┌──┐
│ 1│ │ 2│ │ 3│ │ 4│  <- Miniaturas clicáveis
└──┘ └──┘ └──┘ └──┘
```

## ⚙️ Configurações

### Limites de Upload (php.ini):
```ini
upload_max_filesize = 5M
post_max_size = 20M      # Deve ser maior que upload_max_filesize × num_imagens
max_file_uploads = 20    # Máximo de arquivos por request
```

### Tipos MIME Aceitos:
- `image/jpeg` - JPG/JPEG
- `image/png` - PNG
- `image/jpg` - JPG alternativo
- `image/webp` - WebP
- `image/gif` - GIF

## 🐛 Troubleshooting

### ❌ "Erro ao salvar no banco de dados"
- Verifique se a tabela `product_images` foi criada
- Confirme a FK para `products.id`
- Veja logs: `docker logs batrip-php`

### ❌ Imagens não carregam
- Verifique se `product_images` tem dados: `SELECT * FROM product_images`
- Teste diretamente: `product-image.php?img_id=1`
- Verifique console do navegador para erros JS

### ❌ Upload falha para múltiplas imagens
- Aumente `post_max_size` no php.ini
- Aumente `max_file_uploads`
- Verifique permissões de /tmp no Docker

### ❌ Botões de ⭐/🗑️ não funcionam
- Abra Console do navegador (F12)
- Verifique se há erros JavaScript
- Confirme que os arquivos `image-set-primary.php` e `image-delete.php` existem

### ❌ "O produto deve ter ao menos uma imagem"
- Isso é uma proteção. Todo produto precisa ter pelo menos 1 imagem
- Faça upload de uma nova antes de remover a última

## ✨ Vantagens vs URLs de Imagens

| Aspecto | BLOB no Banco | URLs/Arquivos |
|---------|--------------|---------------|
| Backup | ✅ Junto com banco | ❌ Separado |
| Controle de acesso | ✅ Via PHP | ⚠️ Via .htaccess |
| Múltiplas imagens | ✅ Fácil com FK | ⚠️ Múltiplos campos |
| Migração | ✅ Simples | ⚠️ Copiar arquivos |
| Performance | ⚠️ Pode ser mais lento | ✅ Servido direto |
| Escalabilidade | ⚠️ Aumenta banco | ✅ Usa filesystem |

## 📊 Exemplo de Query

```sql
-- Buscar produto com todas as imagens
SELECT 
  p.id,
  p.title,
  p.price,
  pi.id as image_id,
  pi.is_primary,
  pi.display_order
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.id = 1
ORDER BY pi.is_primary DESC, pi.display_order ASC;
```

## 🔐 Segurança

- ✅ Validação de tipo MIME no servidor
- ✅ Limite de tamanho por arquivo (5MB)
- ✅ CSRF token em todas as operações
- ✅ Verificação de propriedade (produto/imagem)
- ✅ Transações SQL para atomicidade
- ✅ Apenas admins podem gerenciar imagens
- ✅ ON DELETE CASCADE para limpeza automática

## 📝 Próximos Passos

- ⬜ Implementar drag-and-drop para reordenar imagens
- ⬜ Adicionar compressão automática de imagens
- ⬜ Criar thumbs otimizadas para listagem
- ⬜ Implementar zoom na imagem principal
- ⬜ Adicionar lightbox/modal para galeria
- ⬜ Suporte a legendas/alt text por imagem
- ⬜ Estatísticas de uso de storage

---

**Criado em:** 06/10/2025  
**Versão:** 2.0 - Sistema de Múltiplas Imagens  
**Autor:** GitHub Copilot
