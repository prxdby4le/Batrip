-- Criação da tabela de pagamentos para registrar transações do checkout
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    metodo VARCHAR(32) NOT NULL,
    status VARCHAR(32) NOT NULL,
    payment_id VARCHAR(64), -- id do Mercado Pago ou outro gateway
    valor DECIMAL(10,2) NOT NULL,
    email VARCHAR(128),
    raw_data TEXT, -- JSON com resposta completa do gateway
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
