<?php
/**
 * Set Model
 * 
 * Gerencia conjuntos (sets) no banco de dados
 * 
 * @category Models
 * @package  Batrip
 */

namespace App\Models;

use App\Core\Model;

class Set extends Model
{
    /**
     * Nome da tabela
     *
     * @var string
     */
    protected string $table = 'sets';

    /**
     * Busca conjuntos ativos
     *
     * @param  int $limit
     * @return array
     */
    public function getActive(int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca conjunto ativo por ID
     *
     * @param  int $id
     * @return array|false
     */
    public function getActiveById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca itens de um conjunto
     *
     * @param  int $setId
     * @return array
     */
    public function getItems(int $setId): array
    {
        $sql = "
            SELECT si.quantity, p.id as product_id, p.title, p.price, p.sizes, p.image
            FROM set_items si 
            JOIN products p ON p.id = si.product_id 
            WHERE si.set_id = ? AND p.active = 1
            ORDER BY p.title
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$setId]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Processa tamanhos de cada produto
        foreach ($items as &$item) {
            $item['sizes'] = !empty($item['sizes']) 
                ? array_map('trim', explode(',', $item['sizes'])) 
                : ['P', 'M', 'G', 'GG'];
        }
        
        return $items;
    }
}
