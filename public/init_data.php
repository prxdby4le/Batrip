<?php
// Script para inicializar dados de exemplo
require_once '../includes/db.php';

// Garantir tabela de imagens de produto para galeria múltipla
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        url VARCHAR(255) NOT NULL,
        position INT NOT NULL DEFAULT 0,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )');
} catch (PDOException $e) {
    // Apenas loga; a página continua para não quebrar inicialização
    error_log('Falha ao criar tabela product_images: ' . $e->getMessage());
}

// Garantir tabela de conjuntos (sets)
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS sets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        image VARCHAR(255) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    )');
} catch (PDOException $e) {
    error_log('Falha ao criar tabela sets: ' . $e->getMessage());
}

// Garantir tabela set_items
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS set_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        set_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (set_id) REFERENCES sets(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )');
} catch (PDOException $e) {
    error_log('Falha ao criar tabela set_items: ' . $e->getMessage());
}

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head><meta charset='UTF-8'><title>Inicializar Dados - Batrip</title></head>";
echo "<body style='font-family: Arial, sans-serif; padding: 20px; background: #111; color: #fff;'>";
echo "<h1>Inicialização de Dados de Exemplo</h1>";

try {
    // Criar usuário de exemplo se não existir
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['usuario@exemplo.com']);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare('
            INSERT INTO users (name, display_name, email, password, endereco, cidade, estado, cep, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $result = $stmt->execute([
            'Usuário de Exemplo',
            'usuario_exemplo',
            'usuario@exemplo.com',
            password_hash('123456', PASSWORD_DEFAULT),
            'Rua das Flores, 123',
            'São Paulo',
            'SP',
            '01234567',
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "<p style='color: #00ff00;'>✓ Usuário de exemplo criado com ID: $userId</p>";
            
            // Criar alguns pedidos de exemplo
            for ($i = 1; $i <= 3; $i++) {
                $subtotal = rand(50, 200);
                $shipping = 15.00;
                $total = $subtotal + $shipping;
                
                $stmt = $pdo->prepare('
                    INSERT INTO orders (user_id, subtotal, shipping, total, created_at) 
                    VALUES (?, ?, ?, ?, ?)
                ');
                
                $orderResult = $stmt->execute([
                    $userId,
                    $subtotal,
                    $shipping,
                    $total,
                    date('Y-m-d H:i:s', strtotime("-{$i} days"))
                ]);
                
                if ($orderResult) {
                    $orderId = $pdo->lastInsertId();
                    echo "<p style='color: #00ff00;'>✓ Pedido de exemplo #{$orderId} criado</p>";
                    
                    // Criar itens do pedido
                    $items = [
                        ['Camiseta Batrip Original', 'M', 45.90, 2, 'camiseta-original.jpg'],
                        ['Moletom Batrip Deluxe', 'G', 89.90, 1, 'moletom-deluxe.jpg']
                    ];
                    
                    foreach ($items as $item) {
                        if ($i <= count($items)) {
                            $stmt = $pdo->prepare('
                                INSERT INTO order_items (order_id, title, size, price, qty, image) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ');
                            $stmt->execute(array_merge([$orderId], $item));
                        }
                    }
                }
            }
        }
    } else {
        echo "<p style='color: #ffaa00;'>⚠ Usuário de exemplo já existe</p>";
    }
    
    // Verificar se admin existe
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND is_admin = 1');
    $stmt->execute(['admin@batrip.com']);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare('
            INSERT INTO users (name, display_name, email, password, is_admin, created_at) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE is_admin = 1, password = VALUES(password)
        ');
        
        $result = $stmt->execute([
            'Administrador',
            'admin',
            'admin@batrip.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            1,
            date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            echo "<p style='color: #00ff00;'>✓ Usuário administrador criado/atualizado</p>";
        }
    } else {
        echo "<p style='color: #00ff00;'>✓ Usuário administrador já existe</p>";
    }
    
    // Exibir estatísticas finais
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    echo "<hr>";
    echo "<h2>Estatísticas Atuais:</h2>";
    echo "<p><strong>Total de usuários:</strong> $totalUsers</p>";
    echo "<p><strong>Total de pedidos:</strong> $totalOrders</p>";
    
    echo "<hr>";
    echo "<h2>Dados para Teste:</h2>";
    echo "<p><strong>Usuário comum:</strong><br>";
    echo "Email: usuario@exemplo.com<br>";
    echo "Senha: 123456</p>";
    
    echo "<p><strong>Administrador:</strong><br>";
    echo "Email: admin@batrip.com<br>";
    echo "Senha: admin123</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: #ff0000;'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='registros/login.php' style='color: #6cf;'>→ Ir para Login</a></p>";
echo "<p><a href='index.php' style='color: #6cf;'>← Voltar para o site</a></p>";
echo "</body></html>";
?>
