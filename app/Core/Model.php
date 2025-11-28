<?php
/**
 * Base Model Class
 * 
 * Classe base para todos os models
 * Fornece funcionalidades comuns de acesso ao banco
 * 
 * @category Core
 * @package  Batrip
 */

namespace App\Core;

use PDO;

abstract class Model
{
    /**
     * Conexão PDO
     *
     * @var PDO
     */
    protected PDO $db;

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected string $table;

    /**
     * Chave primária
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Cache de colunas conhecidas por tabela
     *
     * @var array<string, array<int, string>>
     */
    protected static array $tableColumnsCache = [];

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca todos os registros
     *
     * @param  array  $conditions Condições WHERE
     * @param  string $orderBy    Ordenação
     * @param  int    $limit      Limite de registros
     * @return array
     */
    public function all(array $conditions = [], string $orderBy = '', int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll();
    }

    /**
     * Busca um registro por ID
     *
     * @param  int $id
     * @return array|false
     */
    public function find(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Busca um registro por condições
     *
     * @param  array $conditions
     * @return array|false
     */
    public function findWhere(array $conditions)
    {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetch();
    }

    /**
     * Insere um novo registro
     *
     * @param  array $data
     * @return int|false ID do registro inserido ou false
     */
    public function create(array $data)
    {
        $data = $this->filterDataForPersistence($data);
        if (empty($data)) {
            return false;
        }

        $fields = array_keys($data);
        $values = ':' . implode(', :', $fields);
        $fields = implode(', ', $fields);
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Atualiza um registro
     *
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        // Guardar dados originais para comparação
        $originalData = $data;
        
        // Limpar cache antes de filtrar para garantir colunas atualizadas
        $this->clearColumnsCache();
        
        // Log dos dados recebidos (antes do filtro)
        error_log("Model::update - Tabela: {$this->table}, ID: $id");
        error_log("Model::update - Dados recebidos: " . json_encode($data));
        
        $data = $this->filterDataForPersistence($data);
        
        // Log dos dados após filtro
        error_log("Model::update - Dados após filtro: " . json_encode($data));
        
        // Verificar se algum campo foi removido
        $removedFields = array_diff_key($originalData, $data);
        if (!empty($removedFields)) {
            error_log("Model::update - Campos removidos pelo filtro: " . json_encode(array_keys($removedFields)));
        }
        
        if (empty($data)) {
            error_log("Model::update - ERRO: Nenhum dado para atualizar após filtro!");
            error_log("Model::update - Campos originais tentados: " . json_encode(array_keys($originalData)));
            return false;
        }

        $fields = [];
        foreach ($data as $key => $value) {
            // Tratamento de valores NULL
            if ($value === null) {
                $fields[] = "{$key} = NULL";
            } else {
                $fields[] = "{$key} = :{$key}";
            }
        }
        
        // Remover campos NULL dos parâmetros
        $params = [];
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $params[$key] = $value;
            }
        }
        $params['id'] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
        
        error_log("Model::update - SQL: " . $sql);
        error_log("Model::update - Params: " . json_encode($params));
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Model::update - Erro ao executar: " . json_encode($errorInfo));
            } else {
                error_log("Model::update - Sucesso! Linhas afetadas: " . $stmt->rowCount());
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Model::update - PDOException: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta um registro
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Conta registros
     *
     * @param  array $conditions
     * @return int
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Obtém conexão PDO (método estático)
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    /**
     * Executa query SQL com parâmetros
     *
     * @param string $sql
     * @param array $params
     * @return bool
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Executa query e retorna resultados
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca registros com condições WHERE
     *
     * @param array $conditions
     * @param string $orderBy
     * @param int $limit
     * @return array
     */
    protected function where(array $conditions, string $orderBy = '', int $limit = 0): array
    {
        return $this->all($conditions, $orderBy, $limit);
    }

    /**
     * Insere registro (alias para create)
     *
     * @param array $data
     * @return int|false
     */
    public function insert(array $data)
    {
        return $this->create($data);
    }

    /**
     * Filtra dados informados mantendo apenas colunas existentes na tabela
     *
     * @param array $data
     * @return array
     */
    protected function filterDataForPersistence(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $columns = $this->getTableColumns();
        if (empty($columns)) {
            error_log("Model::filterDataForPersistence - Nenhuma coluna encontrada para a tabela {$this->table}");
            return $data;
        }

        $filtered = array_intersect_key($data, array_flip($columns));
        
        // Log dos campos filtrados para debug
        $removed = array_diff_key($data, $filtered);
        if (!empty($removed)) {
            error_log("Model::filterDataForPersistence - Campos removidos (não existem na tabela): " . json_encode(array_keys($removed)));
        }
        
        return $filtered;
    }

    /**
     * Retorna lista de colunas da tabela atual
     *
     * @return array<int, string>
     */
    protected function getTableColumns(): array
    {
        if (isset(self::$tableColumnsCache[$this->table])) {
            return self::$tableColumnsCache[$this->table];
        }

        $sql = "SHOW COLUMNS FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            if (isset($column['Field'])) {
                $columns[] = $column['Field'];
            }
        }

        self::$tableColumnsCache[$this->table] = $columns;
        return $columns;
    }
    
    /**
     * Limpa o cache de colunas da tabela
     * Útil após alterações na estrutura da tabela
     *
     * @return void
     */
    public function clearColumnsCache(): void
    {
        if (isset(self::$tableColumnsCache[$this->table])) {
            unset(self::$tableColumnsCache[$this->table]);
        }
    }
}
