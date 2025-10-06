-- Remove as colunas antigas de imagem da tabela products
-- Execute este script para corrigir o erro "Field 'image' doesn't have a default value"

-- Opção 1: Tornar as colunas NULL (recomendado para manter compatibilidade)
ALTER TABLE products 
MODIFY COLUMN image MEDIUMBLOB NULL,
MODIFY COLUMN image_type VARCHAR(50) NULL;

-- Opção 2: Remover as colunas completamente (descomente se preferir)
-- Atenção: Faça backup antes de executar!
-- ALTER TABLE products DROP COLUMN image;
-- ALTER TABLE products DROP COLUMN image_type;
