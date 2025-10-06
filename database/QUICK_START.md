# 🚀 GUIA RÁPIDO - Sistema de Múltiplas Imagens

## ⚡ Instalação em 3 Passos

### 1️⃣ Execute o SQL
```bash
docker exec -i batrip-mysql mysql -uroot -proot batrip < database/create_product_images_table.sql
```

### 2️⃣ (Opcional) Migre imagens antigas
Se você já tinha produtos com imagens na tabela `products`:

```sql
-- Abra o MySQL e execute:
INSERT INTO product_images (product_id, image, image_type, display_order, is_primary)
SELECT id, image, COALESCE(image_type, 'image/jpeg'), 0, 1
FROM products
WHERE image IS NOT NULL;
```

### 3️⃣ Teste!
- Acesse: http://localhost/public/adm/index-adm.php
- Clique em "Novo Produto"
- Faça upload de múltiplas imagens
- ✅ Pronto!

---

## 📸 O que mudou?

### ✅ ANTES (sistema antigo):
- ❌ 1 imagem por produto
- ❌ URL do arquivo (assets/img/...)

### ✅ AGORA (sistema novo):
- ✅ Múltiplas imagens por produto
- ✅ Armazenadas no banco (BLOB)
- ✅ Galeria com miniaturas
- ✅ Definir imagem principal
- ✅ Remover imagens individualmente

---

## 🎯 Principais Funcionalidades

### Admin:
1. **Upload múltiplo**: Selecione vários arquivos de uma vez
2. **Gerenciar**: ⭐ Define principal | 🗑️ Remove
3. **Reordenar**: Ordem automática por `display_order`

### Site Público:
1. **Galeria**: Mostra todas as imagens do produto
2. **Miniaturas**: Clique para trocar a imagem principal
3. **Responsivo**: Funciona em desktop e mobile

---

## 📁 Arquivos Criados/Modificados

### ✅ Novos:
- `database/create_product_images_table.sql`
- `public/adm/products/image-set-primary.php`
- `public/adm/products/image-delete.php`
- `database/MULTIPLE_IMAGES_GUIDE.md` (documentação completa)
- `database/QUICK_START.md` (este arquivo)

### ✅ Modificados:
- `public/adm/products/form.php` - Interface de gerenciamento
- `public/adm/products/save.php` - Processa múltiplos uploads
- `public/adm/products/image.php` - Serve imagens do banco
- `public/product-image.php` - API de imagens públicas
- `includes/product-page.php` - Galeria de produto

---

## 🔧 Configurar PHP (se necessário)

Se você precisar fazer upload de muitas imagens grandes:

```ini
# Em docker/php-dev.ini ou php.ini:
upload_max_filesize = 5M
post_max_size = 20M
max_file_uploads = 20
```

Depois:
```bash
docker-compose restart
```

---

## 🎨 Como Usar

### Adicionar Produto:
1. Admin → Novo Produto
2. Preencha dados
3. Escolha múltiplas imagens
4. Salvar → Primeira imagem = principal automaticamente

### Editar Imagens:
1. Admin → Editar produto
2. Veja todas as imagens atuais
3. Adicione mais (selecione novos arquivos)
4. ⭐ = Definir como principal
5. 🗑️ = Remover (mínimo 1 imagem)

---

## 📊 Estrutura do Banco

```
products (1) ──→ (N) product_images
                     ├─ image (MEDIUMBLOB)
                     ├─ image_type (VARCHAR)
                     ├─ display_order (INT)
                     └─ is_primary (TINYINT)
```

---

## ❓ FAQ

**Q: Posso remover todas as imagens?**  
A: Não, todo produto precisa ter pelo menos 1 imagem.

**Q: Quantas imagens posso adicionar?**  
A: Limitado por `max_file_uploads` do PHP (padrão: 20).

**Q: As imagens antigas serão perdidas?**  
A: Não se você executar o script de migração (passo 2).

**Q: Qual o tamanho máximo por imagem?**  
A: 5MB por padrão, até 16MB (limite do MEDIUMBLOB).

**Q: Funciona com produtos já criados?**  
A: Sim! Edite o produto e adicione imagens.

---

## 🐛 Problemas Comuns

### Erro ao salvar:
```bash
# Verifique se a tabela foi criada:
docker exec -it batrip-mysql mysql -uroot -proot batrip -e "SHOW TABLES LIKE 'product_images';"
```

### Imagens não aparecem:
```bash
# Teste diretamente:
curl http://localhost/public/product-image.php?id=1
```

### Botões não funcionam:
- Abra Console (F12) e veja erros JavaScript
- Confirme que os arquivos PHP existem

---

## 📚 Documentação Completa

Para mais detalhes, leia: `database/MULTIPLE_IMAGES_GUIDE.md`

---

**✅ Sistema Pronto para Uso!**
