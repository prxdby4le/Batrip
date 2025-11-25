-- Garantir que a coluna 'endereco' existe na tabela 'orders' para compatibilidade em qualquer ambiente
ALTER TABLE orders ADD COLUMN IF NOT EXISTS endereco TEXT NOT NULL;
