-- Script de Otimização: Adição de Índices
-- Criado em: 2025-10-08
-- Descrição: Adiciona índices para melhorar performance de queries

-- Índice para buscar produtos ativos ordenados por data
CREATE INDEX IF NOT EXISTS idx_products_active_created 
ON products(active, created_at);

-- Índice para buscar pedidos por usuário e status
CREATE INDEX IF NOT EXISTS idx_orders_user_status 
ON orders(user_id, status);

-- Índice para buscar pedidos por data
CREATE INDEX IF NOT EXISTS idx_orders_created 
ON orders(created_at);

-- Mostrar índices criados
SHOW INDEX FROM products;
SHOW INDEX FROM orders;

-- Query para testar performance
EXPLAIN SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC LIMIT 6;
EXPLAIN SELECT * FROM orders WHERE user_id = 1 AND status = 'pending';
