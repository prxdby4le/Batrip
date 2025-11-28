<?php
/**
 * Home Controller
 * 
 * Gerencia página inicial
 * 
 * @category Controllers
 * @package  Batrip
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Models\ProductImage;
use PDO;

class HomeController extends Controller
{
    /**
     * Product model
     *
     * @var Product
     */
    private Product $productModel;
    
    /**
     * ProductImage model
     *
     * @var ProductImage
     */
    private ProductImage $productImageModel;

    /**
     * Construtor
     */
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
        $this->productModel = new Product();
        $this->productImageModel = new ProductImage();
    }

    /**
     * Página inicial
     *
     * @return void
     */
    public function index(): void
    {
        $pdo = Database::getInstance()->getConnection();
        
        // Busca produtos em destaque (6 mais recentes)
        $featuredProducts = $this->productModel->getFeatured(6);
        
        // Busca conjuntos ativos (4 mais recentes)
        $homeSets = [];
        try {
            $stmt = $pdo->prepare('SELECT id, title, price, image, description FROM sets WHERE active = 1 ORDER BY created_at DESC LIMIT 4');
            $stmt->execute();
            $homeSets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar conjuntos: " . $e->getMessage());
            $homeSets = [];
        }
        
        // Busca imagens dos produtos para galeria
        $imagesByProduct = [];
        if (!empty($featuredProducts)) {
            $ids = array_map(fn($p) => (int)($p['id'] ?? 0), $featuredProducts);
            $ids = array_values(array_filter($ids));
            if (!empty($ids)) {
                try {
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("SELECT product_id, url FROM product_images WHERE product_id IN ($in) ORDER BY is_primary DESC, position ASC, id ASC");
                    $stmt->execute($ids);
                    $counters = [];
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $pid = (int)$row['product_id'];
                        $idx = $counters[$pid] ?? 0;
                        $imagesByProduct[$pid][] = BASE_URL . 'product-image.php?id=' . $pid . '&idx=' . $idx;
                        $counters[$pid] = $idx + 1;
                    }
                } catch (\Throwable $e) {
                    $imagesByProduct = [];
                }
            }
        }
        
        // Dados para a view
        $data = [
            'pageTitle' => 'Batrip - not all bats are dead!',
            'products' => $featuredProducts,
            'sets' => $homeSets,
            'imagesByProduct' => $imagesByProduct,
            'artists' => ['Chard la Plaga', 'Link do Zap', 'Ugovhb', 'EF', 'pradasoueu', 'prxdby4le', 'TheJoia', 'Mugi', 'Yung Loof'],
            'showHero' => true,
            'layout' => 'main'
        ];
        
        $this->view('home.index', $data);
    }

    /**
     * Página sobre
     *
     * @return void
     */
    public function about(): void
    {
        $data = [
            'pageTitle' => 'Sobre | Batrip'
        ];
        
        $this->view('home.about', $data);
    }
}
