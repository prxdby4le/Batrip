-- Script de inicialização para garantir estrutura mínima do banco
-- Crie a tabela orders se não existir
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    endereco TEXT NOT NULL,
    frete JSON NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'aguardando',
    items JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
-- Garante que a coluna endereco existe (caso a tabela já exista)
ALTER TABLE orders ADD COLUMN endereco TEXT NOT NULL;
