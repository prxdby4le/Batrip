-- Corrige a coluna image da tabela products para aceitar NULL
-- Agora usamos a tabela product_images para armazenar as imagens

ALTER TABLE products 
MODIFY COLUMN image VARCHAR(255) NULL DEFAULT NULL;
