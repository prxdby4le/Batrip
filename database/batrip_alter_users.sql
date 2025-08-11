-- Adiciona campos de endereço, cidade, estado e cep na tabela users
ALTER TABLE users
ADD COLUMN endereco VARCHAR(255) DEFAULT NULL,
ADD COLUMN cidade VARCHAR(100) DEFAULT NULL,
ADD COLUMN estado VARCHAR(2) DEFAULT NULL,
ADD COLUMN cep VARCHAR(10) DEFAULT NULL;
