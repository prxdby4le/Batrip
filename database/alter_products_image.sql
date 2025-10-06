-- Altera a coluna image para armazenar dados binários (BLOB) ou MEDIUMBLOB
-- Execute este script após fazer backup dos dados existentes

-- Adiciona nova coluna para armazenar o tipo MIME da imagem
ALTER TABLE products 
ADD COLUMN image_type VARCHAR(50) DEFAULT 'image/jpeg' AFTER image;

-- Altera a coluna image para MEDIUMBLOB (suporta até 16MB)
ALTER TABLE products 
MODIFY COLUMN image MEDIUMBLOB NULL;

-- Opcional: Renomeia a coluna antiga para backup (descomente se preferir manter os URLs antigos)
-- ALTER TABLE products 
-- ADD COLUMN image_url VARCHAR(255) NULL AFTER image_type;
-- UPDATE products SET image_url = image WHERE image IS NOT NULL;
