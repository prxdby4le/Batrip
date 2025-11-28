-- Adiciona campos de perfil do usuário (phone e profile_bg)
-- Verifica se as colunas existem antes de adicionar

SET @dbname = DATABASE();
SET @tablename = 'users';

-- Adicionar coluna 'phone' se não existir
SET @columnname = 'phone';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  'SELECT 1',
  'ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar coluna 'profile_bg' se não existir
SET @columnname = 'profile_bg';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  'SELECT 1',
  'ALTER TABLE users ADD COLUMN profile_bg VARCHAR(255) NULL AFTER profile_img'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
