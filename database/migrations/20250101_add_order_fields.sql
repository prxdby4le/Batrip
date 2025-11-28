-- Migration para adicionar campos necessários na tabela orders
-- Adiciona campos para informações do cliente e pagamento

ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(150) NULL AFTER user_id,
ADD COLUMN IF NOT EXISTS customer_email VARCHAR(150) NULL AFTER customer_name,
ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(20) NULL AFTER customer_email,
ADD COLUMN IF NOT EXISTS shipping_address TEXT NULL AFTER customer_phone,
ADD COLUMN IF NOT EXISTS shipping_city VARCHAR(100) NULL AFTER shipping_address,
ADD COLUMN IF NOT EXISTS shipping_state VARCHAR(2) NULL AFTER shipping_city,
ADD COLUMN IF NOT EXISTS shipping_zipcode VARCHAR(10) NULL AFTER shipping_state,
ADD COLUMN IF NOT EXISTS shipping_method VARCHAR(50) NULL AFTER shipping_zipcode,
ADD COLUMN IF NOT EXISTS shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shipping_method,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL AFTER shipping_cost;

-- Atualiza status padrão para 'pending' se ainda for 'aguardando'
UPDATE orders SET status = 'pending' WHERE status = 'aguardando';

