<?php
/**
 * Script para garantir que as colunas de perfil existam na tabela users
 * Execute via: docker-compose exec web php database/ensure_user_profile_columns.php
 */

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/database.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

echo "Verificando e adicionando colunas necessárias na tabela users...\n\n";

// Listar colunas atuais
$stmt = $pdo->query("SHOW COLUMNS FROM users");
$existingColumns = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existingColumns[] = $row['Field'];
}

echo "Colunas existentes: " . implode(', ', $existingColumns) . "\n\n";

// Campos necessários
$requiredColumns = [
    'phone' => [
        'type' => 'VARCHAR(20)',
        'null' => 'NULL',
        'after' => 'email'
    ],
    'profile_bg' => [
        'type' => 'VARCHAR(255)',
        'null' => 'NULL',
        'after' => 'profile_img'
    ]
];

foreach ($requiredColumns as $columnName => $columnDef) {
    if (!in_array($columnName, $existingColumns)) {
        echo "Adicionando coluna: $columnName\n";
        
        $afterClause = !empty($columnDef['after']) ? " AFTER {$columnDef['after']}" : '';
        $sql = "ALTER TABLE users ADD COLUMN {$columnName} {$columnDef['type']} {$columnDef['null']}{$afterClause}";
        
        try {
            $pdo->exec($sql);
            echo "✓ Coluna $columnName adicionada com sucesso!\n";
        } catch (\PDOException $e) {
            echo "✗ Erro ao adicionar $columnName: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓ Coluna $columnName já existe\n";
    }
}

echo "\nVerificação concluída!\n";

// Mostrar estrutura final
echo "\nEstrutura final da tabela users:\n";
$stmt = $pdo->query("DESCRIBE users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

