-- Migration: Adicionar colunas de cliente e endereço de entrega na tabela orders
-- Data: 2025-01-28
-- Descrição: Adiciona colunas necessárias para armazenar informações do cliente e endereço de entrega
-- Nota: Este script verifica se as colunas existem antes de adicioná-las

-- Procedimento para adicionar coluna apenas se não existir
DELIMITER $$

DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(64),
    IN columnName VARCHAR(64),
    IN columnDefinition TEXT
)
BEGIN
    DECLARE columnExists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO columnExists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tableName
      AND COLUMN_NAME = columnName;
    
    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Adicionar colunas de informações do cliente
CALL AddColumnIfNotExists('orders', 'customer_name', 'VARCHAR(255) NULL AFTER user_id');
CALL AddColumnIfNotExists('orders', 'customer_email', 'VARCHAR(255) NULL AFTER customer_name');
CALL AddColumnIfNotExists('orders', 'customer_phone', 'VARCHAR(20) NULL AFTER customer_email');

-- Adicionar colunas de endereço de entrega
CALL AddColumnIfNotExists('orders', 'shipping_address', 'VARCHAR(500) NULL AFTER customer_phone');
CALL AddColumnIfNotExists('orders', 'shipping_city', 'VARCHAR(100) NULL AFTER shipping_address');
CALL AddColumnIfNotExists('orders', 'shipping_state', 'VARCHAR(2) NULL AFTER shipping_city');
CALL AddColumnIfNotExists('orders', 'shipping_zipcode', 'VARCHAR(10) NULL AFTER shipping_state');

-- Adicionar colunas de método de envio e pagamento
CALL AddColumnIfNotExists('orders', 'shipping_method', 'VARCHAR(100) NULL AFTER shipping_zipcode');
CALL AddColumnIfNotExists('orders', 'shipping_cost', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shipping_method');
CALL AddColumnIfNotExists('orders', 'payment_method', 'VARCHAR(50) NULL DEFAULT ''pix'' AFTER shipping_cost');

-- Limpar procedimento
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
