-- Adds address fields to users if they don't exist yet
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS endereco VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS cidade VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS estado CHAR(2) NULL,
    ADD COLUMN IF NOT EXISTS cep VARCHAR(10) NULL;
