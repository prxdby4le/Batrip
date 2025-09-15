<?php
// Teste de conexão com banco de dados
require_once '../includes/db.php';
require_once '../includes/auth.php';

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head><meta charset='UTF-8'><title>Teste de Conexão - Batrip</title></head>";
echo "<body style='font-family: Arial, sans-serif; padding: 20px; background: #111; color: #fff;'>";
echo "<h1>Teste de Conexão com Banco de Dados</h1>";

try {
    // Testar conexão básica
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color: #00ff00;'>✓ Conexão com banco estabelecida com sucesso!</p>";
    
    // Testar estrutura da tabela users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "<h2>Estrutura da tabela 'users':</h2>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #333;'><th style='padding: 8px;'>Campo</th><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Null</th><th style='padding: 8px;'>Key</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Key']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar usuários
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "<p>Total de usuários cadastrados: <strong>" . $result['total'] . "</strong></p>";
    
    // Testar tabela orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $result = $stmt->fetch();
    echo "<p>Total de pedidos: <strong>" . $result['total'] . "</strong></p>";
    
    // Verificar usuário admin
    $stmt = $pdo->query("SELECT name, email, is_admin FROM users WHERE is_admin = 1 LIMIT 1");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "<p>Usuário admin encontrado: <strong>" . htmlspecialchars($admin['name']) . "</strong> (" . htmlspecialchars($admin['email']) . ")</p>";
    } else {
        echo "<p style='color: #ffaa00;'>⚠ Nenhum usuário admin encontrado</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: #ff0000;'>✗ Erro na conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>Informações do Sistema:</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? 'Disponível' : 'Não disponível') . "</p>";
echo "<p><strong>GD Extension:</strong> " . (extension_loaded('gd') ? 'Disponível' : 'Não disponível') . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Ativa' : 'Inativa') . "</p>";

// Testar diretório de upload
$uploadDir = __DIR__ . '/../assets/img/perfil/';
echo "<p><strong>Diretório de perfil:</strong> " . ($uploadDir) . "</p>";
echo "<p><strong>Diretório existe:</strong> " . (is_dir($uploadDir) ? 'Sim' : 'Não') . "</p>";
echo "<p><strong>Diretório gravável:</strong> " . (is_writable($uploadDir) ? 'Sim' : 'Não') . "</p>";

echo "<hr>";
echo "<p><a href='index.php' style='color: #6cf;'>← Voltar para o site</a></p>";
echo "</body></html>";
?>
