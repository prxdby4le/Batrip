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
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
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
}
