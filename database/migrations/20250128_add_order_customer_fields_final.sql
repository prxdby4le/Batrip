-- Migration: Adicionar colunas de cliente e endereço de entrega na tabela orders
-- Data: 2025-01-28
-- Execute este arquivo no MySQL
-- Se alguma coluna já existir, você verá um erro que pode ser ignorado

-- Colunas de informações do cliente
ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255) NULL AFTER user_id;
ALTER TABLE orders ADD COLUMN customer_email VARCHAR(255) NULL AFTER customer_name;
ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) NULL AFTER customer_email;

-- Colunas de endereço de entrega
ALTER TABLE orders ADD COLUMN shipping_address VARCHAR(500) NULL AFTER customer_phone;
ALTER TABLE orders ADD COLUMN shipping_city VARCHAR(100) NULL AFTER shipping_address;
ALTER TABLE orders ADD COLUMN shipping_state VARCHAR(2) NULL AFTER shipping_city;
ALTER TABLE orders ADD COLUMN shipping_zipcode VARCHAR(10) NULL AFTER shipping_state;

-- Colunas de método de envio e pagamento
ALTER TABLE orders ADD COLUMN shipping_method VARCHAR(100) NULL AFTER shipping_zipcode;
ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shipping_method;
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL DEFAULT 'pix' AFTER shipping_cost;

