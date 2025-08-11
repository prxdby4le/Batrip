-- Criação do banco de dados e tabela de usuários para autenticação
CREATE DATABASE IF NOT EXISTS batrip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE batrip;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
