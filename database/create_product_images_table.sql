-- Tabela para armazenar múltiplas imagens por produto
CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image MEDIUMBLOB NOT NULL,
  image_type VARCHAR(50) DEFAULT 'image/jpeg',
  display_order INT DEFAULT 0,
  is_primary TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_id (product_id),
  INDEX idx_primary (product_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Opcional: Migrar imagens existentes da tabela products para product_images
-- Descomente se quiser migrar dados antigos:
/*
INSERT INTO product_images (product_id, image, image_type, display_order, is_primary)
SELECT id, image, COALESCE(image_type, 'image/jpeg'), 0, 1
FROM products
WHERE image IS NOT NULL;
*/

-- Opcional: Remover colunas antigas da tabela products após migração
-- Descomente APENAS após confirmar que tudo está funcionando:
/*
ALTER TABLE products DROP COLUMN image;
ALTER TABLE products DROP COLUMN image_type;
*/
