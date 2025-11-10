<?php
/**
 * Product Controller
 * 
 * Gerencia produtos
 * 
 * @category Controllers
 * @package  Batrip
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Product model
     *
     * @var Product
     */
    private Product $productModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * Lista todos produtos
     *
     * @return void
     */
    public function index(): void
    {
        $products = $this->productModel->getActive();
        
        $data = [
            'pageTitle' => 'Produtos | Batrip',
            'products' => $products
        ];
        
        $this->view('products.index', $data);
    }

    /**
     * Exibe detalhes do produto
     *
     * @param  int $id
     * @return void
     */
    public function show(int $id): void
    {
        $product = $this->productModel->getActiveById($id);
        
        if (!$product) {
            $this->redirect('/');
            return;
        }
        
        // Processar tamanhos
        $sizes = array_map('trim', explode(',', $product['sizes'] ?? 'P,M,G,GG'));
        
        $data = [
            'pageTitle' => $product['title'] . ' | Batrip',
            'product' => $product,
            'sizes' => $sizes
        ];
        
        $this->view('products.show', $data);
    }

    /**
     * Retorna imagem do produto
     *
     * @param  int $id
     * @return void
     */
    public function image(int $id): void
    {
        $imageData = $this->productModel->getImageBlob($id);
        if ($imageData) {
            header('Content-Type: image/jpeg');
            header('Cache-Control: max-age=86400'); // Cache por 1 dia
            echo $imageData;
            exit;
        }

        // Fallback: usa URL/Path salva em products.image
        $imageUrl = $this->productModel->getImage($id);
        if (!empty($imageUrl)) {
            // Redireciona para a URL/path do arquivo
            if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, '/') === 0) {
                header('Location: ' . $imageUrl);
            } else {
                header('Location: ' . BASE_URL . ltrim($imageUrl, '/'));
            }
            exit;
        }

        // Imagem placeholder
        header('Location: ' . ASSETS_URL . 'img/placeholder.svg');
        exit;
    }

    /**
     * Pesquisa produtos
     *
     * @return void
     */
    public function search(): void
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            $this->redirect('/produtos');
            return;
        }
        
        $products = $this->productModel->search($query);
        
        $data = [
            'pageTitle' => 'Pesquisa: ' . htmlspecialchars($query) . ' | Batrip',
            'products' => $products,
            'query' => htmlspecialchars($query)
        ];
        
        $this->view('products.search', $data);
    }

    /**
     * Produtos por categoria
     *
     * @param  string $category
     * @return void
     */
    public function category(string $category): void
    {
        $products = $this->productModel->getByCategory($category);
        
        $data = [
            'pageTitle' => ucfirst($category) . ' | Batrip',
            'products' => $products,
            'category' => $category
        ];
        
        $this->view('products.category', $data);
    }
}
