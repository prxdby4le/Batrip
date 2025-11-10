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
use App\Models\Product;

class HomeController extends Controller
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
     * Página inicial
     *
     * @return void
     */
    public function index(): void
    {
        // Busca produtos em destaque
        $featuredProducts = $this->productModel->getFeatured(6);
        
        // Dados para a view
        $data = [
            'pageTitle' => 'Batrip - not all bats are dead!',
            'products' => $featuredProducts,
            'showHero' => true
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
