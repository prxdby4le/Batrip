-- Script para garantir que os campos de perfil existam na tabela users
USE batrip;

-- Verificar e adicionar phone
SET @phone_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = 'batrip' 
                     AND TABLE_NAME = 'users' 
                     AND COLUMN_NAME = 'phone');

SET @sql_phone = IF(@phone_exists = 0,
    'ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email',
    'SELECT "Coluna phone já existe" AS message');

PREPARE stmt FROM @sql_phone;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar profile_bg
SET @bg_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = 'batrip' 
                  AND TABLE_NAME = 'users' 
                  AND COLUMN_NAME = 'profile_bg');

SET @sql_bg = IF(@bg_exists = 0,
    'ALTER TABLE users ADD COLUMN profile_bg VARCHAR(255) NULL AFTER profile_img',
    'SELECT "Coluna profile_bg já existe" AS message');

PREPARE stmt FROM @sql_bg;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mostrar estrutura final
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'batrip' 
AND TABLE_NAME = 'users' 
ORDER BY ORDINAL_POSITION;

