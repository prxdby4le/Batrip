USE batrip;

-- Usuário administrador padrão
INSERT INTO users (name, email, password, is_admin)
VALUES (
  'Administrador',
  'admin@batrip.com',
  '$2y$10$wQwQwQwQwQwQwQwQwQwQwOQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQw',
  1
)
ON DUPLICATE KEY UPDATE is_admin=1;
-- Senha: admin123 (hash do password_hash('admin123', PASSWORD_DEFAULT))
