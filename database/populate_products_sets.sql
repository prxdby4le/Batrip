-- ============================================================
-- Script SQL para Popular Banco de Dados com Produtos e Conjuntos
-- ============================================================
-- Este script insere os produtos (camisetas) e conjuntos existentes
-- no banco de dados, preservando os dados existentes.
-- 
-- Uso: Execute este script após criar o banco de dados
-- ============================================================

USE batrip;

-- ============================================================
-- PRODUTOS (CAMISETAS)
-- ============================================================

-- Limpa produtos existentes (opcional - descomente se quiser limpar)
-- DELETE FROM product_images;
-- DELETE FROM set_items WHERE product_id IN (SELECT id FROM products);
-- DELETE FROM products;

-- Insere produtos
INSERT INTO products (id, title, description, price, image, sizes, active, created_at) VALUES
-- Produto 1: Camiseta FRAGMENTOS - Oversized
(2, 'Camiseta FRAGMENTOS - Oversized', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 150.00, 'assets/img/uploads/img_5104-57b5a322.jpg', 'P,M,G,GG', 1, NOW()),

-- Produto 2: Camiseta FRAGMENTOS - Boxy
(3, 'Camiseta FRAGMENTOS - Boxy', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 150.00, 'assets/img/uploads/img_5105-383e1eaa.jpg', 'P,M,G,GG', 1, NOW()),

-- Produto 3: Camiseta Teste (com múltiplas imagens)
(10, 'camiseta teste', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum', 1569.00, 'http://localhost:8080/uploads/products/p10-GoxRi-ZXUAARj0a-17d74b7f.jpeg', 'P,M,G,GG', 1, NOW())

ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    price = VALUES(price),
    image = VALUES(image),
    sizes = VALUES(sizes),
    active = VALUES(active),
    updated_at = NOW();

-- ============================================================
-- IMAGENS ADICIONAIS DOS PRODUTOS (GALERIA)
-- ============================================================

-- Limpa imagens existentes do produto 10 (opcional)
-- DELETE FROM product_images WHERE product_id = 10;

-- Insere imagens adicionais para o produto 2 (FRAGMENTOS - Oversized)
-- Remove imagens existentes antes de inserir (evita duplicatas)
DELETE FROM product_images WHERE product_id = 2;

INSERT INTO product_images (product_id, url, position, is_primary) VALUES
(2, 'assets/img/uploads/img_5104-57b5a322.jpg', 0, 1),
(2, 'assets/img/uploads/img_5108-876484bd.jpg', 1, 0),
(2, 'assets/img/uploads/img_5113-6586faba.jpg', 2, 0),
(2, 'assets/img/uploads/img_5116-b51ccd2c.jpg', 3, 0),
(2, 'assets/img/uploads/img_5120-a17751bb.jpg', 4, 0),
(2, 'assets/img/uploads/img_5124-b7a1348e.jpg', 5, 0);

-- Insere imagens adicionais para o produto 3 (FRAGMENTOS - Boxy)
-- Remove imagens existentes antes de inserir (evita duplicatas)
DELETE FROM product_images WHERE product_id = 3;

INSERT INTO product_images (product_id, url, position, is_primary) VALUES
(3, 'assets/img/uploads/img_5105-383e1eaa.jpg', 0, 1),
(3, 'assets/img/uploads/img_5108-e9f8c5e2.jpg', 1, 0),
(3, 'assets/img/uploads/img_5115-b2832e7c.jpg', 2, 0),
(3, 'assets/img/uploads/img_5125-ff701ae1.jpg', 3, 0);

-- Insere imagens adicionais para o produto 10 (Camiseta Teste)
-- Remove imagens existentes antes de inserir (evita duplicatas)
DELETE FROM product_images WHERE product_id = 10;

INSERT INTO product_images (product_id, url, position, is_primary) VALUES
(10, 'http://localhost:8080/uploads/products/p10-GoxRi-ZXUAARj0a-17d74b7f.jpeg', 0, 1),
(10, 'http://localhost:8080/uploads/products/p10-GoxRi-XXIAAEm2J-ec5ff006.jpeg', 1, 0),
(10, 'http://localhost:8080/uploads/products/p10-GoxRi-YWsAEJWSK-9766e96a.jpeg', 2, 0),
(10, 'http://localhost:8080/uploads/products/p10-GoxRi-ZWQAAc2ga-98770b32.jpeg', 3, 0),
(10, 'http://localhost:8080/uploads/products/p10-GpE-mE5WQAA-BoL-a1de90a4.jpeg', 4, 0),
(10, 'http://localhost:8080/uploads/products/p10-GpE-mE7W0AADa3m-e2fb1c06.jpeg', 5, 0),
(10, 'http://localhost:8080/uploads/products/p10-GpE-mE9XkAACxqb-c9a1fa1b.jpeg', 6, 0);

-- ============================================================
-- CONJUNTOS (SETS)
-- ============================================================

-- Limpa conjuntos existentes (opcional - descomente se quiser limpar)
-- DELETE FROM set_items;
-- DELETE FROM sets;

-- Insere conjuntos
INSERT INTO sets (id, title, description, price, image, active, created_at) VALUES
-- Conjunto 1: FRAGMENTOS
(1, 'Conjunto FRAGMENTOS', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 250.00, '/assets/img/sets/set_20251126_221642_fa43f8.jpg', 1, NOW()),

-- Conjunto 2: ALL IN ONE
(2, 'conjunto ALL IN ONE', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum', 677.00, 'http://localhost:8080/uploads/products/s-3239998-itstealsgamepic2-3cf52756.png', 1, NOW())

ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    price = VALUES(price),
    image = VALUES(image),
    active = VALUES(active),
    updated_at = NOW();

-- ============================================================
-- ITENS DOS CONJUNTOS (set_items)
-- ============================================================

-- Limpa itens dos conjuntos antes de inserir (evita duplicatas)
DELETE FROM set_items WHERE set_id IN (1, 2);

-- Conjunto FRAGMENTOS (ID 1) - contém os produtos FRAGMENTOS
INSERT INTO set_items (set_id, product_id, quantity) VALUES
(1, 3, 1),  -- Camiseta FRAGMENTOS - Boxy
(1, 2, 1);  -- Camiseta FRAGMENTOS - Oversized

-- Conjunto ALL IN ONE (ID 2) - contém múltiplos produtos
INSERT INTO set_items (set_id, product_id, quantity) VALUES
(2, 10, 1),  -- Camiseta Teste
(2, 3, 1),   -- Camiseta FRAGMENTOS - Boxy
(2, 2, 1);   -- Camiseta FRAGMENTOS - Oversized

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================
-- 
-- Para executar este script:
-- 
-- 1. Via Docker:
--    docker compose exec db mysql -u batrip_user -pbatrip_pass_2024 batrip < database/populate_products_sets.sql
-- 
-- 2. Via linha de comando MySQL:
--    mysql -u batrip_user -pbatrip_pass_2024 batrip < database/populate_products_sets.sql
-- 
-- 3. Via phpMyAdmin:
--    Acesse http://localhost:8081, selecione o banco 'batrip' e execute este script
-- 
-- ============================================================

