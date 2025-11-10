<?php
/**
 * ProductImage Model
 * 
 * Gerencia imagens (galeria) associadas a produtos
 */

namespace App\Models;

use App\Core\Model;

class ProductImage extends Model
{
    protected string $table = 'product_images';

    /**
     * Retorna imagens de um produto ordenadas por posição
     *
     * @param int $productId
     * @return array
     */
    public function getByProduct(int $productId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = :pid ORDER BY position ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll();
    }

    /**
     * Insere várias imagens de uma vez
     *
     * @param int   $productId
     * @param array $images Array de ['url' => string, 'position' => int, 'is_primary' => 0|1]
     * @return void
     */
    public function insertMany(int $productId, array $images): void
    {
        if (empty($images)) return;
        $sql = "INSERT INTO {$this->table} (product_id, url, position, is_primary) VALUES (:pid, :url, :pos, :primary)";
        $stmt = $this->db->prepare($sql);
        foreach ($images as $i) {
            $stmt->execute([
                'pid' => $productId,
                'url' => $i['url'],
                'pos' => (int)($i['position'] ?? 0),
                'primary' => (int)($i['is_primary'] ?? 0),
            ]);
        }
    }

    /**
     * Define imagem principal para um produto
     */
    public function setPrimary(int $productId, int $imageId): bool
    {
        // zera todas
        $sql = "UPDATE {$this->table} SET is_primary = 0 WHERE product_id = :pid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);

        // define uma
        $sql = "UPDATE {$this->table} SET is_primary = 1 WHERE id = :id AND product_id = :pid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $imageId, 'pid' => $productId]);
    }

    /**
     * Atualiza posições conforme ordem enviada de IDs
     */
    public function updatePositions(int $productId, array $orderedIds): void
    {
        $sql = "UPDATE {$this->table} SET position = :pos WHERE id = :id AND product_id = :pid";
        $stmt = $this->db->prepare($sql);
        $pos = 0;
        foreach ($orderedIds as $id) {
            $stmt->execute(['pos' => $pos++, 'id' => (int)$id, 'pid' => $productId]);
        }
    }

    /**
     * Busca uma imagem por id (limitada ao produto opcionalmente)
     */
    public function findById(int $id, ?int $productId = null)
    {
        if ($productId === null) return $this->find($id);
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND product_id = :pid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'pid' => $productId]);
        return $stmt->fetch();
    }

    /**
     * Retorna a primeira imagem de um produto (por posição)
     */
    public function getFirstForProduct(int $productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = :pid ORDER BY position ASC, id ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetch();
    }
}
