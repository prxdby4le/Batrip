<?php
/**
 * Product Model
 * 
 * Gerencia produtos no banco de dados
 * 
 * @category Models
 * @package  Batrip
 */

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    /**
     * Nome da tabela
     *
     * @var string
     */
    protected string $table = 'products';

    /**
     * Busca produtos ativos
     *
     * @param  int $limit
     * @return array
     */
    public function getActive(int $limit = 0): array
    {
        return $this->all(['active' => 1], 'created_at DESC', $limit);
    }

    /**
     * Busca produto ativo por ID
     *
     * @param  int $id
     * @return array|false
     */
    public function getActiveById(int $id)
    {
        return $this->findWhere(['id' => $id, 'active' => 1]);
    }

    /**
     * Busca produtos por categoria
     *
     * @param  string $category
     * @param  int    $limit
     * @return array
     */
    public function getByCategory(string $category, int $limit = 0): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE category = :category AND active = 1 ORDER BY created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT {$limit}";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['category' => $category]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Se coluna 'category' não existe, retorna array vazio
            if (strpos($e->getMessage(), 'category') !== false) {
                return [];
            }
            throw $e;
        }
    }

    /**
     * Busca produtos com estoque
     *
     * @param  int $limit
     * @return array
     */
    public function getInStock(int $limit = 0): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE active = 1 AND stock > 0 ORDER BY created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT {$limit}";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Se coluna 'stock' não existe, retorna todos produtos ativos
            if (strpos($e->getMessage(), 'stock') !== false) {
                $sql = "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY created_at DESC";
                if ($limit > 0) {
                    $sql .= " LIMIT {$limit}";
                }
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll();
            }
            throw $e;
        }
    }

    /**
     * Busca produtos em destaque
     *
     * @param  int $limit
     * @return array
     */
    public function getFeatured(int $limit = 6): array
    {
        // Verifica se a coluna 'featured' existe
        try {
            // Excluir produtos com type='set' da seção de lançamentos
            // Verificar se coluna 'type' existe primeiro
            $checkType = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'type'");
            $hasTypeColumn = $checkType->rowCount() > 0;
            
            if ($hasTypeColumn) {
                $sql = "SELECT * FROM {$this->table} WHERE active = 1 AND featured = 1 AND (type IS NULL OR type != 'set' OR type = 'product' OR type = '') ORDER BY created_at DESC LIMIT {$limit}";
            } else {
                $sql = "SELECT * FROM {$this->table} WHERE active = 1 AND featured = 1 ORDER BY created_at DESC LIMIT {$limit}";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Se coluna 'featured' não existe, retorna produtos mais recentes (excluindo conjuntos)
            if (strpos($e->getMessage(), 'featured') !== false) {
                // Verificar se coluna 'type' existe
                try {
                    $checkType = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'type'");
                    $hasTypeColumn = $checkType->rowCount() > 0;
                    
                    if ($hasTypeColumn) {
                        $sql = "SELECT * FROM {$this->table} WHERE active = 1 AND (type IS NULL OR type != 'set' OR type = 'product' OR type = '') ORDER BY created_at DESC LIMIT {$limit}";
                    } else {
                        $sql = "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY created_at DESC LIMIT {$limit}";
                    }
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    return $stmt->fetchAll();
                } catch (\PDOException $e2) {
                    // Se houver erro ao verificar type, retorna todos os produtos ativos
                    $sql = "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY created_at DESC LIMIT {$limit}";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    return $stmt->fetchAll();
                }
            }
            throw $e;
        }
    }

    /**
     * Pesquisa produtos
     *
     * @param  string $query
     * @param  int    $limit
     * @return array
     */
    public function search(string $query, int $limit = 20): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE active = 1 
                    AND (title LIKE :query OR description LIKE :query OR category LIKE :query)
                    ORDER BY created_at DESC 
                    LIMIT {$limit}";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['query' => "%{$query}%"]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Se coluna 'category' não existe, busca apenas em title e description
            if (strpos($e->getMessage(), 'category') !== false) {
                $sql = "SELECT * FROM {$this->table} 
                        WHERE active = 1 
                        AND (title LIKE :query OR description LIKE :query)
                        ORDER BY created_at DESC 
                        LIMIT {$limit}";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['query' => "%{$query}%"]);
                return $stmt->fetchAll();
            }
            throw $e;
        }
    }

    /**
     * Atualiza estoque do produto
     *
     * @param  int $id
     * @param  int $quantity
     * @return bool
     */
    public function updateStock(int $id, int $quantity): bool
    {
        $sql = "UPDATE {$this->table} SET stock = stock - :quantity WHERE id = :id AND active = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'quantity' => $quantity]);
    }

    /**
     * Verifica se produto tem estoque
     *
     * @param  int $id
     * @param  int $quantity
     * @return bool
     */
    public function hasStock(int $id, int $quantity = 1): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }
        
        return isset($product['stock']) && $product['stock'] >= $quantity;
    }

    /**
     * Retorna imagem do produto
     *
     * @param  int $id
     * @return string|null
     */
    public function getImage(int $id): ?string
    {
        $product = $this->find($id);
        return $product['image'] ?? null;
    }

    /**
     * Retorna blob da imagem do produto
     *
     * @param  int $id
     * @return string|null
     */
    public function getImageBlob(int $id): ?string
    {
        $sql = "SELECT image_data FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        return $result['image_data'] ?? null;
    }
}
