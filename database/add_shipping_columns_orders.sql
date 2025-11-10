ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS shipping_method VARCHAR(50) NULL AFTER shipping_zipcode,
ADD COLUMN IF NOT EXISTS shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shipping_method;

-- Ensure subtotal column exists (if schema differs)
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER items;
