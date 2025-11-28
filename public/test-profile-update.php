<?php
/**
 * Script de teste para verificar atualização de perfil
 * Acesse: http://localhost:8080/test-profile-update.php
 */

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

session_start();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Teste de Atualização de Perfil</h1>";

if (!isset($_SESSION['user_id'])) {
    echo "<p>Você precisa estar logado. <a href='/login'>Fazer Login</a></p>";
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = \App\Core\Database::getInstance()->getConnection();

echo "<h2>1. Estrutura da Tabela users</h2>";
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
echo "</pre>";

echo "<h2>2. Dados Atuais do Usuário (ID: $userId)</h2>";
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($user);
echo "</pre>";

echo "<h2>3. Teste de Update Simples</h2>";

// Tentar atualizar apenas o nome
$newName = $user['name'] . " (teste)";
try {
    $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
    $result = $stmt->execute([$newName, $userId]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ UPDATE executado com sucesso! Linhas afetadas: " . $stmt->rowCount() . "</p>";
        
        // Verificar se foi atualizado
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($updated['name'] === $newName) {
            echo "<p style='color: green;'>✓ Dados confirmados no banco!</p>";
        } else {
            echo "<p style='color: red;'>✗ Dados não foram atualizados no banco!</p>";
            echo "<p>Esperado: $newName</p>";
            echo "<p>Encontrado: " . $updated['name'] . "</p>";
        }
        
        // Reverter
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$user['name'], $userId]);
        echo "<p>Nome revertido para o original.</p>";
    } else {
        echo "<p style='color: red;'>✗ Falha ao executar UPDATE</p>";
        $error = $stmt->errorInfo();
        echo "<pre>" . print_r($error, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Teste com Model</h2>";
try {
    $userModel = new \App\Models\User();
    
    // Testar getTableColumns
    $reflection = new ReflectionClass($userModel);
    $method = $reflection->getMethod('getTableColumns');
    $method->setAccessible(true);
    $columns = $method->invoke($userModel);
    
    echo "<h3>Colunas da tabela (via Model):</h3>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Testar update
    $testData = [
        'name' => $user['name'] . ' (via model)'
    ];
    
    echo "<h3>Tentando atualizar via Model:</h3>";
    echo "<pre>Dados: " . json_encode($testData) . "</pre>";
    
    $success = $userModel->update($userId, $testData);
    
    if ($success) {
        echo "<p style='color: green;'>✓ Model::update retornou true</p>";
        
        // Verificar
        $updatedUser = $userModel->findById($userId);
        if ($updatedUser && $updatedUser['name'] === $testData['name']) {
            echo "<p style='color: green;'>✓ Dados confirmados!</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Update retornou true mas dados não mudaram</p>";
            echo "<pre>Usuário: " . print_r($updatedUser, true) . "</pre>";
        }
        
        // Reverter
        $userModel->update($userId, ['name' => $user['name']]);
        echo "<p>Nome revertido.</p>";
    } else {
        echo "<p style='color: red;'>✗ Model::update retornou false</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro no teste do Model: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>5. Verificar Logs Recentes</h2>";
$logFile = __DIR__ . '/../logs/' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recent = array_slice($lines, -20);
    echo "<pre style='max-height: 300px; overflow-y: auto;'>";
    echo implode("\n", $recent);
    echo "</pre>";
} else {
    echo "<p>Arquivo de log não encontrado: $logFile</p>";
}

?>

