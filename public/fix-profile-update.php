<?php
/**
 * Script de diagnóstico e correção do problema de atualização de perfil
 * Acesse: http://localhost:8080/fix-profile-update.php
 */

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico e Correção - Atualização de Perfil</h1>";

$pdo = \App\Core\Database::getInstance()->getConnection();

// 1. Verificar estrutura da tabela
echo "<h2>1. Estrutura Atual da Tabela users</h2>";
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_column($columns, 'Field');

echo "<pre>";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
echo "</pre>";

// 2. Adicionar campos faltantes
echo "<h2>2. Verificando e Adicionando Campos Faltantes</h2>";

$neededColumns = [
    'phone' => "VARCHAR(20) NULL AFTER email",
    'profile_bg' => "VARCHAR(255) NULL AFTER profile_img"
];

foreach ($neededColumns as $colName => $colDef) {
    if (!in_array($colName, $columnNames)) {
        echo "<p>Adicionando coluna <strong>$colName</strong>...</p>";
        try {
            $sql = "ALTER TABLE users ADD COLUMN $colName $colDef";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Coluna $colName adicionada com sucesso!</p>";
            $columnNames[] = $colName; // Atualizar lista
        } catch (\PDOException $e) {
            echo "<p style='color: red;'>✗ Erro ao adicionar $colName: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Coluna $colName já existe</p>";
    }
}

// 3. Limpar cache de colunas do Model
echo "<h2>3. Limpando Cache do Model</h2>";
try {
    $userModel = new \App\Models\User();
    $userModel->clearColumnsCache();
    echo "<p style='color: green;'>✓ Cache limpo</p>";
} catch (\Exception $e) {
    echo "<p style='color: orange;'>⚠ Erro ao limpar cache: " . $e->getMessage() . "</p>";
}

// 4. Testar atualização
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "<h2>4. Testando Atualização (User ID: $userId)</h2>";
    
    // Buscar usuário atual
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<h3>Dados atuais:</h3>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Testar atualização simples
        $testName = $user['name'] . ' [TEST]';
        echo "<h3>Testando update direto no banco:</h3>";
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $result = $stmt->execute([$testName, $userId]);
            
            if ($result) {
                echo "<p style='color: green;'>✓ UPDATE direto funcionou! Linhas: " . $stmt->rowCount() . "</p>";
                
                // Verificar
                $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $check = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($check['name'] === $testName) {
                    echo "<p style='color: green;'>✓ Dados confirmados no banco!</p>";
                } else {
                    echo "<p style='color: red;'>✗ Dados não atualizados!</p>";
                }
                
                // Reverter
                $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$user['name'], $userId]);
                echo "<p>Nome revertido.</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
        }
        
        // Testar via Model
        echo "<h3>Testando update via Model:</h3>";
        try {
            $userModel = new \App\Models\User();
            $userModel->clearColumnsCache(); // Limpar cache primeiro
            
            $testData = [
                'name' => $user['name'] . ' [MODEL]',
                'endereco' => 'Teste Endereço',
                'cidade' => 'Teste Cidade',
                'estado' => 'SP',
                'cep' => '12345678'
            ];
            
            echo "<pre>Dados de teste: " . json_encode($testData) . "</pre>";
            
            $success = $userModel->update($userId, $testData);
            
            if ($success) {
                echo "<p style='color: green;'>✓ Model::update retornou true</p>";
                
                // Verificar
                $updatedUser = $userModel->findById($userId);
                echo "<pre>Usuário após update: " . print_r($updatedUser, true) . "</pre>";
                
                if ($updatedUser) {
                    $match = true;
                    foreach ($testData as $key => $value) {
                        if ($key !== 'cep' && isset($updatedUser[$key]) && $updatedUser[$key] !== $value) {
                            echo "<p style='color: orange;'>⚠ Campo $key não corresponde (esperado: $value, encontrado: " . ($updatedUser[$key] ?? 'null') . ")</p>";
                            $match = false;
                        }
                    }
                    
                    if ($match) {
                        echo "<p style='color: green;'>✓ Todos os campos foram atualizados corretamente!</p>";
                    }
                }
                
                // Reverter
                $userModel->update($userId, [
                    'name' => $user['name'],
                    'endereco' => $user['endereco'] ?? null,
                    'cidade' => $user['cidade'] ?? null,
                    'estado' => $user['estado'] ?? null,
                    'cep' => $user['cep'] ?? null
                ]);
                echo "<p>Dados revertidos.</p>";
            } else {
                echo "<p style='color: red;'>✗ Model::update retornou false</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color: red;'>✗ Erro no Model: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
} else {
    echo "<p>Faça login para testar a atualização: <a href='/login'>Login</a></p>";
}

// 5. Mostrar logs recentes
echo "<h2>5. Logs Recentes</h2>";
$logFile = __DIR__ . '/../logs/' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recent = array_filter(array_slice($lines, -30), function($line) {
        return stripos($line, 'Model::update') !== false || 
               stripos($line, 'ProfileController') !== false ||
               stripos($line, 'ERROR') !== false;
    });
    
    if (!empty($recent)) {
        echo "<pre style='max-height: 400px; overflow-y: auto; background: #1a1a1a; color: #0f0; padding: 15px; border-radius: 5px;'>";
        echo implode("\n", $recent);
        echo "</pre>";
    } else {
        echo "<p>Nenhum log relevante encontrado nas últimas 30 linhas.</p>";
    }
} else {
    echo "<p>Arquivo de log não encontrado: $logFile</p>";
}

echo "<hr>";
echo "<p><a href='/perfil/editar'>← Voltar para Editar Perfil</a> | <a href='/perfil'>Ver Perfil</a></p>";

?>

