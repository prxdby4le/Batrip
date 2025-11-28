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
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
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
        
        // Buscar imagens da galeria para cada produto
        $imagesByProduct = [];
        if (!empty($products)) {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $ids = array_map(fn($p) => (int)($p['id'] ?? 0), $products);
            $ids = array_values(array_filter($ids));
            if (!empty($ids)) {
                try {
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("SELECT product_id, url FROM product_images WHERE product_id IN ($in) ORDER BY is_primary DESC, position ASC, id ASC");
                    $stmt->execute($ids);
                    $counters = [];
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
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
        
        $data = [
            'pageTitle' => 'Produtos | Batrip',
            'products' => $products,
            'imagesByProduct' => $imagesByProduct,
            'layout' => 'main'
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
            http_response_code(404);
            $this->view('errors.404', [
                'pageTitle' => 'Produto não encontrado',
                'message' => 'O produto que você procura não existe ou foi removido.'
            ], 'main');
            return;
        }
        
        // Processar tamanhos
        $sizes = array_map('trim', explode(',', $product['sizes'] ?? 'P,M,G,GG'));

        // Processar tabela de medidas (size_chart)
        $sizeTableHtml = '';
        $sizeTableImage = '';
        if (!empty($product['size_chart'])) {
            $sizeChart = json_decode($product['size_chart'], true);
            if (is_array($sizeChart)) {
                // Se for uma tabela HTML
                if (!empty($sizeChart['html'])) {
                    $sizeTableHtml = $sizeChart['html'];
                }
                // Se for uma imagem
                if (!empty($sizeChart['image'])) {
                    $sizeTableImage = $sizeChart['image'];
                }
            }
        }
        
        // Buscar imagens da galeria
        $productImageModel = new \App\Models\ProductImage();
        $galleryImages = $productImageModel->getByProduct($id);
        $productImages = [];
        if (!empty($galleryImages)) {
            foreach (array_values($galleryImages) as $i => $img) {
                $productImages[] = BASE_URL . 'product-image.php?id=' . (int)$id . '&idx=' . (int)$i;
            }
        } else {
            // Se não houver galeria, usa imagem principal
            if (!empty($product['image'])) {
                $productImages[] = BASE_URL . 'product-image.php?id=' . (int)$id;
            }
        }
        
        // Buscar produtos relacionados (mesmo tipo ou categoria)
        $relatedProducts = $this->productModel->getActive(4);
        $relatedProducts = array_filter($relatedProducts, function($p) use ($id) {
            return $p['id'] != $id;
        });
        $relatedProducts = array_slice($relatedProducts, 0, 4);
        
        // Buscar imagens da galeria para produtos relacionados
        $relatedImagesByProduct = [];
        if (!empty($relatedProducts)) {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $ids = array_map(fn($p) => (int)($p['id'] ?? 0), $relatedProducts);
            $ids = array_values(array_filter($ids));
            if (!empty($ids)) {
                try {
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("SELECT product_id, url FROM product_images WHERE product_id IN ($in) ORDER BY is_primary DESC, position ASC, id ASC");
                    $stmt->execute($ids);
                    $counters = [];
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        $pid = (int)$row['product_id'];
                        $idx = $counters[$pid] ?? 0;
                        $relatedImagesByProduct[$pid][] = BASE_URL . 'product-image.php?id=' . $pid . '&idx=' . $idx;
                        $counters[$pid] = $idx + 1;
                    }
                } catch (\Throwable $e) {
                    $relatedImagesByProduct = [];
                }
            }
        }

        $data = [
            'pageTitle' => $product['title'] . ' | Batrip',
            'product' => $product,
            'sizes' => $sizes,
            'sizeTableHtml' => $sizeTableHtml,
            'sizeTableImage' => $sizeTableImage,
            'productImages' => $productImages,
            'relatedProducts' => $relatedProducts,
            'relatedImagesByProduct' => $relatedImagesByProduct,
            'layout' => 'main'
        ];
        
        $this->view('products.show', $data);
    }

    /**
     * Retorna imagem do produto
     * Suporta galeria (idx) e tamanhos (size)
     *
     * @param  int $id
     * @return void
     */
    public function image($id = null): void
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        
        // Pega ID do parâmetro da rota ou do GET
        if ($id === null) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        } else {
            $id = (int)$id;
        }
        
        if ($id <= 0) {
            http_response_code(404);
            header('Content-Type: image/png');
            // Retorna imagem placeholder vazia
            exit;
        }
        
        $idx = isset($_GET['idx']) ? max(0, (int)$_GET['idx']) : null;
        $size = isset($_GET['size']) ? strtolower(trim((string)$_GET['size'])) : null;
        
        $publicBase = realpath(ROOT_PATH . '/public');
        $rel = 'assets/img/placeholder.svg';
        
        // 1) Tenta obter imagens da galeria (product_images)
        $productImageModel = new \App\Models\ProductImage();
        $galleryImages = $productImageModel->getByProduct($id);
        
        if (!empty($galleryImages)) {
            $images = array_column($galleryImages, 'url');
            $chosen = $images[0];
            if ($idx !== null && isset($images[$idx])) {
                $chosen = $images[$idx];
            }
            $pi = (string)$chosen;
            $pi = str_replace('\\', '/', $pi);
            if (strpos($pi, 'public/') === 0) {
                $pi = substr($pi, 7);
            }
            if (!filter_var($pi, FILTER_VALIDATE_URL)) {
                // Suporta variantes geradas para uploads: --thumb / --medium
                if (in_array($size, ['thumb','medium','large'], true) && strpos($pi, 'assets/img/uploads/') === 0) {
                    $dot = strrpos($pi, '.');
                    if ($dot !== false) {
                        $candidate = substr($pi, 0, $dot) . '--' . $size . substr($pi, $dot);
                        $rel = $candidate;
                    } else {
                        $rel = $pi;
                    }
                } else if (strpos($pi, 'assets/') === 0 || strpos($pi, 'images/') === 0) {
                    $rel = $pi;
                } else if (strpos($pi, '/') === false) {
                    $rel = 'assets/img/' . $pi;
                } else {
                    $rel = 'assets/img/' . basename($pi);
                }
            } else {
                // URL externa
                header('Location: ' . $pi);
                exit;
            }
        } else {
            // Fallback: imagem única do produto
            $product = $this->productModel->find($id);
            if ($product && !empty($product['image'])) {
                $pi = (string)$product['image'];
                $pi = str_replace('\\', '/', $pi);
                if (strpos($pi, 'public/') === 0) {
                    $pi = substr($pi, 7);
                }
                if (!filter_var($pi, FILTER_VALIDATE_URL)) {
                    if (in_array($size, ['thumb','medium','large'], true) && strpos($pi, 'assets/img/uploads/') === 0) {
                        $dot = strrpos($pi, '.');
                        if ($dot !== false) {
                            $candidate = substr($pi, 0, $dot) . '--' . $size . substr($pi, $dot);
                            $rel = $candidate;
                        } else {
                            $rel = $pi;
                        }
                    } else if (strpos($pi, 'assets/') === 0 || strpos($pi, 'images/') === 0) {
                        $rel = $pi;
                    } else if (strpos($pi, '/') === false) {
                        $rel = 'assets/img/' . $pi;
                    } else {
                        $rel = 'assets/img/' . basename($pi);
                    }
                } else {
                    header('Location: ' . $pi);
                    exit;
                }
            }
        }
        
        $abs = realpath($publicBase . DIRECTORY_SEPARATOR . $rel);
        if (!$abs || strpos($abs, $publicBase) !== 0 || !is_file($abs)) {
            $abs = realpath($publicBase . DIRECTORY_SEPARATOR . 'assets/img/placeholder.svg');
        }
        
        if (!$abs || !is_file($abs)) {
            http_response_code(404);
            exit('Not Found');
        }
        
        // Detect content type
        $mime = 'application/octet-stream';
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp'
        ];
        if (isset($map[$ext])) {
            $mime = $map[$ext];
        }
        
        // Caching headers
        $expires = 60 * 60 * 24 * 7; // 7 dias
        if (!headers_sent()) {
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=' . $expires);
        }
        
        // Output file
        $fp = fopen($abs, 'rb');
        if ($fp) {
            fpassthru($fp);
            fclose($fp);
        } else {
            http_response_code(404);
            echo 'Not Found';
        }
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
