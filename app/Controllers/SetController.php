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
    public function image(int $id = 0): void
    {
        // Limpa qualquer output anterior
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Garante que ROOT_PATH está definido
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(dirname(__DIR__)));
        }
        
        // Garante que BASE_URL está definido
        if (!defined('BASE_URL')) {
            require_once ROOT_PATH . '/config/config.php';
        }
        
        // Se o ID não veio como parâmetro da rota, tenta pegar de $_GET
        if ($id === 0 && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
        }
        
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'assets/img/placeholder.svg');
            exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare('SELECT image FROM sets WHERE id = ?');
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set || empty($set['image'])) {
            header('Location: ' . BASE_URL . 'assets/img/placeholder.svg');
            exit;
        }
        
        $imagePath = $set['image'];
        
        // Se for URL completa (http:// ou https://), redireciona
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            header('Location: ' . $imagePath);
            exit;
        }
        
        // Se a imagem está em assets/img/sets/, verifica se existe (prioridade)
        if (strpos($imagePath, 'assets/img/sets/') !== false || strpos($imagePath, '/assets/img/sets/') !== false) {
            $filename = basename($imagePath);
            // Tenta múltiplos caminhos possíveis (prioridade para public/)
            $possiblePaths = [
                ROOT_PATH . '/public/assets/img/sets/' . $filename,  // public/assets/img/sets/ (preferido)
                ROOT_PATH . '/assets/img/sets/' . $filename,          // assets/img/sets/ (legado, sem public)
            ];
            
            foreach ($possiblePaths as $assetsPath) {
                if (file_exists($assetsPath) && is_file($assetsPath)) {
                    $mimeType = mime_content_type($assetsPath);
                    if ($mimeType && strpos($mimeType, 'image/') === 0) {
                        header('Content-Type: ' . $mimeType);
                        header('Cache-Control: max-age=86400');
                        readfile($assetsPath);
                        exit;
                    }
                }
            }
        }
        
        // Se a imagem está em uploads/products/, verifica se existe
        if (strpos($imagePath, 'uploads/products/') !== false || strpos($imagePath, '/uploads/products/') !== false) {
            $filename = basename($imagePath);
            $uploadPath = ROOT_PATH . '/public/uploads/products/' . $filename;
            if (file_exists($uploadPath) && is_file($uploadPath)) {
                $mimeType = mime_content_type($uploadPath);
                if ($mimeType && strpos($mimeType, 'image/') === 0) {
                    header('Content-Type: ' . $mimeType);
                    header('Cache-Control: max-age=86400');
                    readfile($uploadPath);
                    exit;
                }
            }
        }
        
        // Se for caminho absoluto começando com /, tenta vários caminhos possíveis
        if (strpos($imagePath, '/') === 0) {
            $imagePathClean = ltrim($imagePath, '/');
            $possiblePaths = [
                ROOT_PATH . '/public/' . $imagePathClean,  // Caminho relativo a public/
                ROOT_PATH . '/' . $imagePathClean,         // Caminho relativo a root
            ];
            
            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath) && is_file($fullPath)) {
                    $mimeType = mime_content_type($fullPath);
                    if ($mimeType && strpos($mimeType, 'image/') === 0) {
                        header('Content-Type: ' . $mimeType);
                        header('Cache-Control: max-age=86400');
                        readfile($fullPath);
                        exit;
                    }
                }
            }
        }
        
        // Tenta caminho relativo também
        $possiblePaths = [
            ROOT_PATH . '/public/' . $imagePath,  // Caminho relativo a public/
            ROOT_PATH . '/' . $imagePath,         // Caminho relativo a root
        ];
        
        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath) && is_file($fullPath)) {
                $mimeType = mime_content_type($fullPath);
                if ($mimeType && strpos($mimeType, 'image/') === 0) {
                    header('Content-Type: ' . $mimeType);
                    header('Cache-Control: max-age=86400');
                    readfile($fullPath);
                    exit;
                }
            }
        }
        
        // Fallback para set-image.php legado se existir
        if (file_exists(ROOT_PATH . '/public/set-image.php')) {
            $_GET['id'] = $id;
            $_GET['size'] = $_GET['size'] ?? 'medium';
            include ROOT_PATH . '/public/set-image.php';
            exit;
        }
        
        // Último fallback: placeholder
        header('Location: ' . BASE_URL . 'assets/img/placeholder.svg');
        exit;
    }
}

