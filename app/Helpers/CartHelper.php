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
     * Sincronizado com CART_SESSION_KEY do sistema legado para compatibilidade
     * Usa CART_SESSION_KEY se definida, senão 'cart' como fallback
     *
     * @var string
     */
    private static function getSessionKey(): string
    {
        // Garante que config.php foi carregado
        if (!defined('CART_SESSION_KEY')) {
            $configPath = dirname(dirname(__DIR__)) . '/config/config.php';
            if (file_exists($configPath)) {
                require_once $configPath;
            }
        }
        return defined('CART_SESSION_KEY') ? CART_SESSION_KEY : 'cart';
    }

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

        $sessionKey = self::getSessionKey();
        
        // Migração automática: se existe carrinho na chave antiga 'cart' e a nova chave está vazia, migra
        if ($sessionKey !== 'cart' && !isset($_SESSION[$sessionKey]) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $_SESSION[$sessionKey] = $_SESSION['cart'];
            // Opcional: limpar chave antiga após migração
            // unset($_SESSION['cart']);
        }
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
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

        $sessionKey = self::getSessionKey();
        
        // Gera chave única (id + size)
        $key = $product['id'] . '_' . ($product['size'] ?? 'M');

        // Verifica se já existe no carrinho
        foreach ($_SESSION[$sessionKey] as $index => $item) {
            $itemKey = $item['id'] . '_' . ($item['size'] ?? 'M');
            if ($itemKey === $key) {
                // Atualiza quantidade
                $_SESSION[$sessionKey][$index]['qty'] += $qty;
                return true;
            }
        }

        // Adiciona novo item
        $_SESSION[$sessionKey][] = [
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
        
        $sessionKey = self::getSessionKey();
        if (isset($_SESSION[$sessionKey][$index])) {
            unset($_SESSION[$sessionKey][$index]);
            // Reindexar array
            $_SESSION[$sessionKey] = array_values($_SESSION[$sessionKey]);
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
        
        $sessionKey = self::getSessionKey();
        if (!isset($_SESSION[$sessionKey][$index])) {
            return false;
        }

        if ($qty < self::$minQty || $qty > self::$maxQty) {
            return false;
        }

        $_SESSION[$sessionKey][$index]['qty'] = $qty;
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
        $sessionKey = self::getSessionKey();
        return $_SESSION[$sessionKey] ?? [];
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
        $sessionKey = self::getSessionKey();
        $_SESSION[$sessionKey] = [];
    }

    /**
     * Verifica se carrinho está vazio
     *
     * @return bool
     */
    public static function isEmpty(): bool
    {
        self::initSession();
        $sessionKey = self::getSessionKey();
        return empty($_SESSION[$sessionKey]);
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

    /**
     * Adiciona conjunto ao carrinho
     * Usa offset de 1000000 para diferenciar de produtos
     *
     * @param  array $set Dados do conjunto (id, title, price, image)
     * @param  int   $qty Quantidade
     * @return bool
     */
    public static function addSet(array $set, int $qty = 1): bool
    {
        self::initSession();
        
        // Valida dados obrigatórios
        if (!isset($set['id'], $set['title'], $set['price'])) {
            return false;
        }

        // Valida quantidade
        if ($qty < self::$minQty || $qty > self::$maxQty) {
            return false;
        }

        // Offset para não colidir com IDs de produto
        $offset = 1000000;
        $cartId = $offset + (int)$set['id'];

        $sessionKey = self::getSessionKey();
        
        // Verifica se já existe no carrinho
        foreach ($_SESSION[$sessionKey] as $index => $item) {
            // Se for um conjunto com mesmo ID
            if (isset($item['type']) && $item['type'] === 'set' 
                && isset($item['set_id']) && (int)$item['set_id'] === (int)$set['id']) {
                // Atualiza quantidade
                $_SESSION[$sessionKey][$index]['qty'] += $qty;
                return true;
            }
        }

        // Adiciona novo conjunto
        $_SESSION[$sessionKey][] = [
            'id' => $cartId,
            'set_id' => (int)$set['id'],
            'title' => $set['title'],
            'price' => (float)$set['price'],
            'size' => 'SET', // Tamanho simbólico para conjuntos
            'qty' => $qty,
            'image' => $set['image'] ?? '',
            'type' => 'set'
        ];

        return true;
    }

    /**
     * Verifica se um item é um conjunto
     *
     * @param  array $item
     * @return bool
     */
    public static function isSet(array $item): bool
    {
        return isset($item['type']) && $item['type'] === 'set';
    }

    /**
     * Retorna o ID original do conjunto (sem offset)
     *
     * @param  array $item
     * @return int|null
     */
    public static function getSetId(array $item): ?int
    {
        if (self::isSet($item) && isset($item['set_id'])) {
            return (int)$item['set_id'];
        }
        return null;
    }
}
