-- Adicionar colunas faltantes na tabela products
-- Executar este script no banco de dados

USE batrip;

-- Verificar estrutura atual
DESCRIBE products;

-- Adicionar coluna 'featured' (produtos em destaque) se não existir
SET @dbname = DATABASE();
SET @tablename = 'products';
SET @columnname = 'featured';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER active'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar coluna 'category' (categoria do produto) se não existir
SET @columnname = 'category';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN category VARCHAR(50) DEFAULT NULL AFTER description'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar coluna 'stock' (estoque) se não existir
SET @columnname = 'stock';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER sizes'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Marcar alguns produtos como destaque (opcional)
-- UPDATE products SET featured = 1 WHERE id IN (1, 2, 3, 4, 5, 6);

-- Verificar estrutura final da tabela
DESCRIBE products;

-- Mensagem de sucesso
SELECT 'Colunas adicionadas com sucesso!' AS mensagem;
