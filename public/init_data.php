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
    
    // Criar produtos FRAGMENTOS se não existirem
    $fragmentosProducts = [
        [
            'title' => 'Camiseta FRAGMENTOS - Boxy',
            'description' => 'Camiseta boxy com estampa fragmentada. Modelo exclusivo da Batrip com design único e conforto incomparável.',
            'price' => 149.99,
            'image' => 'assets/img/fragmentado-costa.jpeg',
            'sizes' => 'P,M,G,GG'
        ],
        [
            'title' => 'Camiseta FRAGMENTOS - Oversized',
            'description' => 'Camiseta oversized com estampa fragmentada. Modelo exclusivo da Batrip com design único e conforto incomparável.',
            'price' => 149.99,
            'image' => 'assets/img/fragmentado-frente.jpeg',
            'sizes' => 'P,M,G,GG'
        ]
    ];
    
    foreach ($fragmentosProducts as $product) {
        // Verificar se o produto já existe
        $stmt = $pdo->prepare('SELECT id FROM products WHERE title = ?');
        $stmt->execute([$product['title']]);
        
        if (!$stmt->fetch()) {
            // Inserir produto
            $stmt = $pdo->prepare('
                INSERT INTO products (title, description, price, image, sizes, active, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ');
            
            $result = $stmt->execute([
                $product['title'],
                $product['description'],
                $product['price'],
                $product['image'],
                $product['sizes']
            ]);
            
            if ($result) {
                $productId = $pdo->lastInsertId();
                echo "<p style='color: #00ff00;'>✓ Produto '{$product['title']}' criado com ID: $productId</p>";
                
                // Adicionar imagem principal à galeria
                try {
                    $stmtImg = $pdo->prepare('
                        INSERT INTO product_images (product_id, url, position, is_primary) 
                        VALUES (?, ?, 0, 1)
                    ');
                    $stmtImg->execute([$productId, $product['image']]);
                } catch (PDOException $e) {
                    error_log('Erro ao adicionar imagem do produto: ' . $e->getMessage());
                }
            }
        } else {
            echo "<p style='color: #ffaa00;'>⚠ Produto '{$product['title']}' já existe</p>";
        }
    }
    
    // Criar Conjunto FRAGMENTOS se não existir
    $setTitle = 'Conjunto FRAGMENTOS';
    $stmt = $pdo->prepare('SELECT id FROM sets WHERE title = ?');
    $stmt->execute([$setTitle]);
    
    if (!$stmt->fetch()) {
        // Buscar IDs dos produtos FRAGMENTOS criados
        $productIds = [];
        foreach ($fragmentosProducts as $product) {
            $stmt = $pdo->prepare('SELECT id FROM products WHERE title = ?');
            $stmt->execute([$product['title']]);
            $row = $stmt->fetch();
            if ($row) {
                $productIds[] = $row['id'];
            }
        }
        
        if (count($productIds) >= 2) {
            // Calcular preço do conjunto (soma dos produtos com desconto)
            $setPrice = 279.99; // Preço especial do conjunto (menor que a soma individual)
            $setImage = 'assets/img/fragmentado-frente.jpeg'; // Imagem do conjunto
            $setDescription = 'Conjunto completo FRAGMENTOS contendo as camisetas Boxy e Oversized. Modelo exclusivo da Batrip com design único e conforto incomparável.';
            
            // Inserir conjunto
            $stmt = $pdo->prepare('
                INSERT INTO sets (title, description, price, image, active, created_at) 
                VALUES (?, ?, ?, ?, 1, NOW())
            ');
            
            $result = $stmt->execute([
                $setTitle,
                $setDescription,
                $setPrice,
                $setImage
            ]);
            
            if ($result) {
                $setId = $pdo->lastInsertId();
                echo "<p style='color: #00ff00;'>✓ Conjunto '{$setTitle}' criado com ID: $setId</p>";
                
                // Adicionar produtos ao conjunto (set_items)
                try {
                    $stmtItems = $pdo->prepare('
                        INSERT INTO set_items (set_id, product_id, quantity) 
                        VALUES (?, ?, 1)
                    ');
                    
                    // Adicionar ambos os produtos ao conjunto
                    foreach ($productIds as $productId) {
                        $stmtItems->execute([$setId, $productId]);
                    }
                    
                    echo "<p style='color: #00ff00;'>✓ Produtos adicionados ao conjunto '{$setTitle}'</p>";
                } catch (PDOException $e) {
                    error_log('Erro ao adicionar produtos ao conjunto: ' . $e->getMessage());
                    echo "<p style='color: #ffaa00;'>⚠ Erro ao adicionar produtos ao conjunto: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        } else {
            echo "<p style='color: #ffaa00;'>⚠ Não foi possível criar o conjunto: produtos FRAGMENTOS não encontrados</p>";
        }
    } else {
        echo "<p style='color: #ffaa00;'>⚠ Conjunto '{$setTitle}' já existe</p>";
    }
    
    // Exibir estatísticas finais
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE active = 1");
    $totalProducts = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sets WHERE active = 1");
    $totalSets = $stmt->fetch()['total'];
    
    echo "<hr>";
    echo "<h2>Estatísticas Atuais:</h2>";
    echo "<p><strong>Total de usuários:</strong> $totalUsers</p>";
    echo "<p><strong>Total de pedidos:</strong> $totalOrders</p>";
    echo "<p><strong>Total de produtos ativos:</strong> $totalProducts</p>";
    echo "<p><strong>Total de conjuntos ativos:</strong> $totalSets</p>";
    
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
