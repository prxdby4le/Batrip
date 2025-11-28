<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

/**
 * SetController - Gerencia conjuntos (sets)
 */
class SetController extends Controller
{
    /**
     * Exibe detalhes de um conjunto
     */
    public function show(int $id): void
    {
        $pdo = Database::getInstance()->getConnection();
        
        // Busca conjunto
        $stmt = $pdo->prepare('SELECT * FROM sets WHERE id = ? AND active = 1');
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set) {
            http_response_code(404);
            $this->view('errors.404', [
                'pageTitle' => 'Conjunto não encontrado',
                'message' => 'O conjunto que você procura não está disponível.'
            ], 'main');
            return;
        }
        
        // Busca itens do conjunto
        $stmt = $pdo->prepare('
            SELECT si.quantity, p.id as product_id, p.title, p.price, p.sizes, p.image
            FROM set_items si 
            JOIN products p ON p.id = si.product_id 
            WHERE si.set_id = ? AND p.active = 1
            ORDER BY p.title
        ');
        $stmt->execute([$id]);
        $setItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processa tamanhos de cada produto
        foreach ($setItems as &$item) {
            $item['sizes'] = array_map('trim', explode(',', $item['sizes'] ?? 'P,M,G,GG'));
        }
        
        $data = [
            'pageTitle' => $set['title'] . ' | Batrip',
            'set' => $set,
            'setItems' => $setItems,
            'layout' => 'main'
        ];
        
        $this->view('sets.show', $data);
    }
    
    /**
     * Lista todos os conjuntos
     */
    public function index(): void
    {
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->query('SELECT * FROM sets WHERE active = 1 ORDER BY created_at DESC');
        $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'pageTitle' => 'Conjuntos | Batrip',
            'sets' => $sets,
            'layout' => 'main'
        ];
        
        $this->view('sets.index', $data);
    }
    
    /**
     * Retorna imagem do conjunto
     */
    public function image(int $id): void
    {
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare('SELECT image FROM sets WHERE id = ?');
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set || empty($set['image'])) {
            header('Location: ' . BASE_URL . 'assets/img/placeholder.svg');
            exit;
        }
        
        $imagePath = $set['image'];
        
        // Se for URL completa, redireciona
        if (strpos($imagePath, 'http') === 0) {
            header('Location: ' . $imagePath);
            exit;
        }
        
        // Se for path relativo, verifica se existe
        $fullPath = ROOT_PATH . '/public/' . ltrim($imagePath, '/');
        if (file_exists($fullPath)) {
            $mimeType = mime_content_type($fullPath);
            header('Content-Type: ' . $mimeType);
            header('Cache-Control: max-age=86400');
            readfile($fullPath);
            exit;
        }
        
        // Fallback para set-image.php legado se existir
        if (file_exists(ROOT_PATH . '/public/set-image.php')) {
            $_GET['id'] = $id;
            $_GET['size'] = $_GET['size'] ?? 'medium';
            include ROOT_PATH . '/public/set-image.php';
            exit;
        }
        
        header('Location: ' . BASE_URL . 'assets/img/placeholder.svg');
        exit;
    }
}

