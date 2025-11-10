<?php
/**
 * Cart Helper
 * 
 * Gerencia carrinho de compras na sessão
 * 
 * @category Helpers
 * @package  Batrip
 */

namespace App\Helpers;

class CartHelper
{
    /**
     * Chave da sessão do carrinho
     *
     * @var string
     */
    private static string $sessionKey = 'shopping_cart';

    /**
     * Quantidade mínima por produto
     *
     * @var int
     */
    private static int $minQty = 1;

    /**
     * Quantidade máxima por produto
     *
     * @var int
     */
    private static int $maxQty = 99;

    /**
     * Inicia sessão se necessário
     *
     * @return void
     */
    private static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = [];
        }
    }

    /**
     * Adiciona produto ao carrinho
     *
     * @param  array $product
     * @return bool
     */
    public static function add(array $product): bool
    {
        self::initSession();
        
        // Valida dados obrigatórios
        if (!isset($product['id'], $product['title'], $product['price'])) {
            return false;
        }

        // Valida quantidade
        $qty = $product['qty'] ?? 1;
        if ($qty < self::$minQty || $qty > self::$maxQty) {
            return false;
        }

        // Gera chave única (id + size)
        $key = $product['id'] . '_' . ($product['size'] ?? 'M');

        // Verifica se já existe no carrinho
        foreach ($_SESSION[self::$sessionKey] as $index => $item) {
            $itemKey = $item['id'] . '_' . ($item['size'] ?? 'M');
            if ($itemKey === $key) {
                // Atualiza quantidade
                $_SESSION[self::$sessionKey][$index]['qty'] += $qty;
                return true;
            }
        }

        // Adiciona novo item
        $_SESSION[self::$sessionKey][] = [
            'id' => (int)$product['id'],
            'title' => $product['title'],
            'price' => (float)$product['price'],
            'size' => $product['size'] ?? 'M',
            'qty' => $qty,
            'image' => $product['image'] ?? ''
        ];

        return true;
    }

    /**
     * Remove produto do carrinho
     *
     * @param  int $index
     * @return bool
     */
    public static function remove(int $index): bool
    {
        self::initSession();
        
        if (isset($_SESSION[self::$sessionKey][$index])) {
            unset($_SESSION[self::$sessionKey][$index]);
            // Reindexar array
            $_SESSION[self::$sessionKey] = array_values($_SESSION[self::$sessionKey]);
            return true;
        }
        return false;
    }

    /**
     * Atualiza quantidade de um item
     *
     * @param  int $index
     * @param  int $qty
     * @return bool
     */
    public static function updateQuantity(int $index, int $qty): bool
    {
        self::initSession();
        
        if (!isset($_SESSION[self::$sessionKey][$index])) {
            return false;
        }

        if ($qty < self::$minQty || $qty > self::$maxQty) {
            return false;
        }

        $_SESSION[self::$sessionKey][$index]['qty'] = $qty;
        return true;
    }

    /**
     * Retorna itens do carrinho
     *
     * @return array
     */
    public static function getItems(): array
    {
        self::initSession();
        return $_SESSION[self::$sessionKey] ?? [];
    }

    /**
     * Retorna quantidade total de itens
     *
     * @return int
     */
    public static function getCount(): int
    {
        $count = 0;
        foreach (self::getItems() as $item) {
            $count += $item['qty'];
        }
        return $count;
    }

    /**
     * Retorna total do carrinho
     *
     * @return float
     */
    public static function getTotal(): float
    {
        $total = 0;
        foreach (self::getItems() as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }

    /**
     * Limpa carrinho
     *
     * @return void
     */
    public static function clear(): void
    {
        self::initSession();
        $_SESSION[self::$sessionKey] = [];
    }

    /**
     * Verifica se carrinho está vazio
     *
     * @return bool
     */
    public static function isEmpty(): bool
    {
        self::initSession();
        return empty($_SESSION[self::$sessionKey]);
    }

    /**
     * Valida tamanho do produto
     *
     * @param  string $size
     * @return bool
     */
    public static function validateSize(string $size): bool
    {
        $validSizes = ['P', 'M', 'G', 'GG', 'XG'];
        return in_array(strtoupper($size), $validSizes);
    }

    /**
     * Alias para getItems() (compatibilidade)
     *
     * @return array
     */
    public static function getCart(): array
    {
        return self::getItems();
    }

    /**
     * Alias para getCount() (compatibilidade)
     *
     * @return int
     */
    public static function getItemCount(): int
    {
        return self::getCount();
    }
}
