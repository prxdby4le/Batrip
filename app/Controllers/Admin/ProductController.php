<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Garante que constantes estão definidas
     */
    private function ensureConstants(): void
    {
        if (!defined('UPLOAD_PATH')) {
            if (defined('UPLOAD_DIR')) {
                define('UPLOAD_PATH', UPLOAD_DIR);
            } else {
                $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(dirname(__DIR__)));
                define('UPLOAD_PATH', $rootPath . '/public/uploads/');
            }
        }
    }
    /**
     * Listar todos produtos e conjuntos
     */
    public function index()
    {
        $this->requireAdmin();
        
        $productModel = new Product();
        $products = $productModel->all([], 'created_at DESC');
        
        // Buscar conjuntos da tabela sets
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $setsStmt = $pdo->query('SELECT * FROM sets ORDER BY created_at DESC');
        $sets = $setsStmt->fetchAll() ?: [];
        
        // Combinar produtos e conjuntos, marcando conjuntos com tipo 'set'
        $allItems = [];
        foreach ($products as $product) {
            $product['item_type'] = 'product';
            $allItems[] = $product;
        }
        foreach ($sets as $set) {
            $set['item_type'] = 'set';
            $set['id'] = $set['id']; // Garantir que ID está presente
            $allItems[] = $set;
        }
        
        // Ordenar por data de criação (mais recentes primeiro)
        usort($allItems, function($a, $b) {
            $dateA = $a['created_at'] ?? '1970-01-01 00:00:00';
            $dateB = $b['created_at'] ?? '1970-01-01 00:00:00';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        $this->view('admin/products/index', [
            'pageTitle' => 'Gerenciar Produtos - Admin',
            'products' => $allItems,
            'items' => $allItems // Alias para compatibilidade
        ], 'admin');
    }
    
    /**
     * Formulário de criar produto
     */
    public function create()
    {
        $this->requireAdmin();
        
        // Buscar produtos ativos para usar em conjuntos
        $productModel = new Product();
        $availableProducts = $productModel->all(['active' => 1], 'title ASC');
        
        $this->view('admin/products/create', [
            'pageTitle' => 'Novo Produto - Admin',
            'availableProducts' => $availableProducts
        ], 'admin');
    }
    
    /**
     * Salvar novo produto
     */
    public function store()
    {
        $this->requireAdmin();
        
        // Garantir que constantes estão definidas
        $this->ensureConstants();
        
        // Validação básica
        $errors = [];
        
        if (empty($this->request->post('title'))) {
            $errors[] = 'Título é obrigatório';
        }
        
        if (empty($this->request->post('price')) || $this->request->post('price') <= 0) {
            $errors[] = 'Preço deve ser maior que zero';
        }
        
        // Verificar tipo
        $type = $this->request->post('type') ?? 'product';
        
        // Se for conjunto, criar APENAS na tabela sets (não em products)
        if ($type === 'set') {
            // Validação para conjuntos
            $setItems = $this->request->post('set_items') ?? [];
            $hasSelectedProducts = false;
            
            if (is_array($setItems)) {
                foreach ($setItems as $itemData) {
                    if (isset($itemData['checked']) && (string)$itemData['checked'] === '1') {
                        $hasSelectedProducts = true;
                        break;
                    }
                }
            }
            
            if (!$hasSelectedProducts) {
                $errors[] = 'Para criar um conjunto, é necessário selecionar pelo menos um produto.';
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = $this->request->all();
                return $this->redirect(BASE_URL . 'adm/produtos/novo');
            }
            
            return $this->createSet();
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $this->request->all();
            return $this->redirect(BASE_URL . 'adm/produtos/novo');
        }
        
        // Preparar dados para produto normal
        $data = [
            'title' => $this->request->post('title'),
            'description' => $this->request->post('description'),
            'price' => $this->request->post('price'),
            'image' => '', // Campo obrigatório - será atualizado depois com a primeira imagem
            'category' => $this->request->post('category') ?? 'geral',
            'type' => $type,
            'active' => $this->request->post('active') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Placeholder de imagem caso nenhum upload seja enviado
        $primaryImageUrl = null;
        
        // Criar produto
        $productModel = new Product();
        $id = $productModel->insert($data);
        
        if (!$id) {
            $_SESSION['error'] = 'Erro ao criar produto';
            return $this->redirect(BASE_URL . 'adm/produtos/novo');
        }

        // Processar imagens múltiplas (nome do campo: images[])
        $savedGallery = [];
        if (isset($_FILES['images']) && isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
            $maxPerProduct = defined('IMAGES_PER_PRODUCT_MAX') ? (int)IMAGES_PER_PRODUCT_MAX : 12;
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;

            // Garantir diretório
            $targetDir = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $fileCount = count($_FILES['images']['name']);
            $position = 0;
            for ($i = 0; $i < $fileCount && count($savedGallery) < $maxPerProduct; $i++) {
                $name = $_FILES['images']['name'][$i];
                $type = $_FILES['images']['type'][$i] ?? '';
                $tmp  = $_FILES['images']['tmp_name'][$i] ?? '';
                $err  = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                $size = $_FILES['images']['size'][$i] ?? 0;

                if ($err !== UPLOAD_ERR_OK) continue;
                if (!in_array($type, $allowedTypes)) continue;
                if ($size <= 0 || $size > $maxSizeBytes) continue;
                if (!is_uploaded_file($tmp)) continue;

                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
                $filename = sprintf('p%d-%s-%s.%s', $id, $safeBase ?: 'img', bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;

                if (move_uploaded_file($tmp, $destPath)) {
                    $url = BASE_URL . 'uploads/products/' . $filename;
                    $savedGallery[] = [
                        'url' => $url,
                        'position' => $position++,
                        'is_primary' => 0,
                    ];
                }
            }
        }

        // Se nada veio em images[], tentar campo legacy 'image'
        if (empty($savedGallery) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $type = $_FILES['image']['type'] ?? '';
            $size = $_FILES['image']['size'] ?? 0;
            $tmp  = $_FILES['image']['tmp_name'] ?? '';
            $name = $_FILES['image']['name'] ?? 'image.jpg';

            $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;
            if (in_array($type, $allowedTypes) && $size > 0 && $size <= $maxSizeBytes && is_uploaded_file($tmp)) {
                $targetDir = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0775, true);
                }
                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
                $filename = sprintf('p%d-%s-%s.%s', $id, $safeBase ?: 'img', bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;
                if (move_uploaded_file($tmp, $destPath)) {
                    $url = BASE_URL . 'uploads/products/' . $filename;
                    $savedGallery[] = [
                        'url' => $url,
                        'position' => 0,
                        'is_primary' => 1,
                    ];
                }
            }
        }

        // Se houver imagens salvas, definir a principal e atualizar produto.image
        if (!empty($savedGallery)) {
            // Primeira como principal
            $savedGallery[0]['is_primary'] = 1;
            $primaryImageUrl = $savedGallery[0]['url'];

            // Salvar na tabela product_images
            $imgModel = new \App\Models\ProductImage();
            $imgModel->insertMany($id, $savedGallery);

            // Atualiza campo image do produto para a principal (compatibilidade)
            $productModel->update($id, ['image' => $primaryImageUrl]);
        } else {
            // Se não houver imagens na galeria, verificar se há imagem no campo image do produto
            $product = $productModel->find($id);
            if ($product && !empty($product['image'])) {
                $primaryImageUrl = $product['image'];
            }
        }

        $_SESSION['success'] = 'Produto criado com sucesso!';
        return $this->redirect(BASE_URL . 'adm/produtos');
    }
    
    /**
     * Criar conjunto (salva apenas na tabela sets, não em products)
     */
    private function createSet()
    {
        $this->ensureConstants();
        $pdo = \App\Core\Database::getInstance()->getConnection();
        
        // Preparar dados do conjunto
        $setData = [
            'title' => $this->request->post('title'),
            'description' => $this->request->post('description') ?? '',
            'price' => $this->request->post('price'),
            'image' => '',
            'active' => $this->request->post('active') ? 1 : 0
        ];
        
        // Processar upload de imagem
        $primaryImageUrl = null;
        $savedImage = null;
        
        // Processar imagens múltiplas (nome do campo: images[])
        if (isset($_FILES['images']) && isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
            $maxPerProduct = defined('IMAGES_PER_PRODUCT_MAX') ? (int)IMAGES_PER_PRODUCT_MAX : 12;
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;

            // Garantir diretório (usar pasta de sets ou products)
            $targetDir = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < $fileCount && $i < 1; $i++) { // Apenas primeira imagem para conjunto
                $name = $_FILES['images']['name'][$i];
                $type = $_FILES['images']['type'][$i] ?? '';
                $tmp  = $_FILES['images']['tmp_name'][$i] ?? '';
                $err  = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                $size = $_FILES['images']['size'][$i] ?? 0;

                if ($err !== UPLOAD_ERR_OK) continue;
                if (!in_array($type, $allowedTypes)) continue;
                if ($size <= 0 || $size > $maxSizeBytes) continue;
                if (!is_uploaded_file($tmp)) continue;

                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
                // Usar prefixo 's' para sets ao invés de 'p' para products
                $filename = sprintf('s-%s-%s.%s', $safeBase ?: 'img', bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;

                if (move_uploaded_file($tmp, $destPath)) {
                    $savedImage = BASE_URL . 'uploads/products/' . $filename;
                    $primaryImageUrl = $savedImage;
                    break; // Apenas primeira imagem
                }
            }
        }

        // Se nada veio em images[], tentar campo legacy 'image'
        if (empty($savedImage) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $type = $_FILES['image']['type'] ?? '';
            $size = $_FILES['image']['size'] ?? 0;
            $tmp  = $_FILES['image']['tmp_name'] ?? '';
            $name = $_FILES['image']['name'] ?? 'image.jpg';

            $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;
            if (in_array($type, $allowedTypes) && $size > 0 && $size <= $maxSizeBytes && is_uploaded_file($tmp)) {
                $targetDir = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0775, true);
                }
                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
                $filename = sprintf('s-%s-%s.%s', $safeBase ?: 'img', bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;
                if (move_uploaded_file($tmp, $destPath)) {
                    $savedImage = BASE_URL . 'uploads/products/' . $filename;
                    $primaryImageUrl = $savedImage;
                }
            }
        }
        
        // Atualizar imagem no setData
        if (!empty($primaryImageUrl)) {
            $setData['image'] = $primaryImageUrl;
        }
        
        // Criar registro na tabela sets
        try {
            $setStmt = $pdo->prepare('INSERT INTO sets (title, description, price, image, active) VALUES (?, ?, ?, ?, ?)');
            $setStmt->execute([
                $setData['title'],
                $setData['description'],
                $setData['price'],
                $setData['image'],
                $setData['active']
            ]);
            $setId = (int)$pdo->lastInsertId();
            
            if (!$setId) {
                $_SESSION['error'] = 'Erro ao criar conjunto';
                return $this->redirect(BASE_URL . 'adm/produtos/novo');
            }
            
            // Processar produtos selecionados para o conjunto
            $setItems = $this->request->post('set_items') ?? [];
            if (is_array($setItems) && !empty($setItems)) {
                $insertStmt = $pdo->prepare('INSERT INTO set_items (set_id, product_id, quantity) VALUES (?, ?, ?)');
                
                foreach ($setItems as $productId => $itemData) {
                    $checked = isset($itemData['checked']) && (string)$itemData['checked'] === '1';
                    $qty = isset($itemData['qty']) ? max(1, (int)$itemData['qty']) : 1;
                    $productId = (int)$productId;
                    
                    if ($checked && $productId > 0) {
                        $insertStmt->execute([$setId, $productId, $qty]);
                    }
                }
            }
            
            $_SESSION['success'] = 'Conjunto criado com sucesso!';
            return $this->redirect(BASE_URL . 'adm/produtos');
        } catch (\PDOException $e) {
            error_log('Erro ao criar conjunto: ' . $e->getMessage());
            $_SESSION['error'] = 'Erro ao criar conjunto: ' . $e->getMessage();
            return $this->redirect(BASE_URL . 'adm/produtos/novo');
        }
    }
    
    /**
     * Formulário de editar produto ou conjunto
     */
    public function edit()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $productModel = new Product();
        $product = $productModel->find($id);
        
        // Se não encontrou na tabela products, verifica se é um conjunto
        if (!$product) {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('SELECT * FROM sets WHERE id = ?');
            $stmt->execute([$id]);
            $set = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($set) {
                // É um conjunto - redireciona para página de edição de conjunto (ou mostra erro)
                $_SESSION['error'] = 'Para editar conjuntos, use a funcionalidade específica de conjuntos.';
                return $this->redirect(BASE_URL . 'adm/produtos');
            }
            
            $_SESSION['error'] = 'Produto não encontrado';
            return $this->redirect(BASE_URL . 'adm/produtos');
        }
        
        // Carrega galeria
        $imgModel = new \App\Models\ProductImage();
        $images = $imgModel->getByProduct((int)$id);
        
        $this->view('admin/products/edit', [
            'pageTitle' => 'Editar Produto - Admin',
            'product' => $product,
            'images' => $images
        ], 'admin');
    }
    
    /**
     * Atualizar produto ou conjunto
     */
    public function update()
    {
        $this->requireAdmin();
        $this->ensureConstants();
        
        $id = $this->param('id');
        $productModel = new Product();
        $product = $productModel->find($id);
        
        // Se não encontrou na tabela products, verifica se é um conjunto
        if (!$product) {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('SELECT * FROM sets WHERE id = ?');
            $stmt->execute([$id]);
            $set = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($set) {
                // É um conjunto - atualiza na tabela sets
                // Validação
                $errors = [];
                
                if (empty($this->request->post('title'))) {
                    $errors[] = 'Título é obrigatório';
                }
                
                if (empty($this->request->post('price')) || $this->request->post('price') <= 0) {
                    $errors[] = 'Preço deve ser maior que zero';
                }
                
                if (!empty($errors)) {
                    $_SESSION['errors'] = $errors;
                    $_SESSION['error'] = 'Para editar conjuntos, use a funcionalidade específica de conjuntos.';
                    return $this->redirect(BASE_URL . 'adm/produtos');
                }
                
                // Preparar dados do conjunto
                $setData = [
                    'title' => $this->request->post('title'),
                    'description' => $this->request->post('description') ?? '',
                    'price' => $this->request->post('price'),
                    'active' => $this->request->post('active') ? 1 : 0
                ];
                
                // Upload de nova imagem (se houver)
                if ($this->request->hasFile('image')) {
                    $file = $this->request->file('image');
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                    $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;
                    
                    if (in_array($file['type'], $allowedTypes) && $file['size'] > 0 && $file['size'] <= $maxSizeBytes && is_uploaded_file($file['tmp_name'])) {
                        $targetDir = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0775, true);
                        }
                        
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
                        $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($file['name'], PATHINFO_FILENAME));
                        $filename = sprintf('s-%s-%s.%s', $safeBase ?: 'img', bin2hex(random_bytes(4)), strtolower($ext));
                        $destPath = $targetDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $destPath)) {
                            $setData['image'] = BASE_URL . 'uploads/products/' . $filename;
                        }
                    }
                }
                
                // Atualizar conjunto
                try {
                    $updateStmt = $pdo->prepare('UPDATE sets SET title = ?, description = ?, price = ?, active = ?' . (!empty($setData['image']) ? ', image = ?' : '') . ' WHERE id = ?');
                    $params = [$setData['title'], $setData['description'], $setData['price'], $setData['active']];
                    if (!empty($setData['image'])) {
                        $params[] = $setData['image'];
                    }
                    $params[] = $id;
                    $updateStmt->execute($params);
                    
                    $_SESSION['success'] = 'Conjunto atualizado com sucesso';
                    return $this->redirect(BASE_URL . 'adm/produtos');
                } catch (\PDOException $e) {
                    error_log('Erro ao atualizar conjunto: ' . $e->getMessage());
                    $_SESSION['error'] = 'Erro ao atualizar conjunto: ' . $e->getMessage();
                    return $this->redirect(BASE_URL . 'adm/produtos');
                }
            }
            
            $_SESSION['error'] = 'Produto não encontrado';
            return $this->redirect(BASE_URL . 'adm/produtos');
        }
        
        // É um produto normal - continua com a lógica original
        // Validação
        $errors = [];
        
        if (empty($this->request->post('title'))) {
            $errors[] = 'Título é obrigatório';
        }
        
        if (empty($this->request->post('price')) || $this->request->post('price') <= 0) {
            $errors[] = 'Preço deve ser maior que zero';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $this->redirect("adm/produtos/{$id}/editar");
        }
        
        // Preparar dados
        $data = [
            'title' => $this->request->post('title'),
            'description' => $this->request->post('description'),
            'price' => $this->request->post('price'),
            'category' => $this->request->post('category') ?? 'geral',
            'active' => $this->request->post('active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Upload de nova imagem (se houver)
        if ($this->request->hasFile('image')) {
            $file = $this->request->file('image');
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5000000) {
                $imageData = file_get_contents($file['tmp_name']);
                $data['image'] = base64_encode($imageData);
            }
        }
        
        // Atualizar produto
        $updated = $productModel->update($id, $data);
        
        if ($updated) {
            $_SESSION['success'] = 'Produto atualizado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar produto';
        }
        
        return $this->redirect(BASE_URL . 'adm/produtos');
    }

    /**
     * Upload de múltiplas imagens (AJAX)
     */
    public function uploadImages()
    {
        $this->requireAdmin();
        $this->ensureConstants();
        $productId = (int)$this->param('id');

        $productModel = new Product();
        $product = $productModel->find($productId);
        if (!$product) return $this->jsonError('Produto não encontrado', 404);

        if (!isset($_FILES['images'])) {
            return $this->jsonError('Nenhum arquivo enviado');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;
        $maxPerProduct = defined('IMAGES_PER_PRODUCT_MAX') ? (int)IMAGES_PER_PRODUCT_MAX : 12;

        $targetDir = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $imgModel = new \App\Models\ProductImage();
        $existing = $imgModel->getByProduct($productId);
        $position = count($existing);

        $saved = [];
        $fileCount = count($_FILES['images']['name']);
        for ($i = 0; $i < $fileCount && ($position + count($saved)) < $maxPerProduct; $i++) {
            $name = $_FILES['images']['name'][$i];
            $type = $_FILES['images']['type'][$i] ?? '';
            $tmp  = $_FILES['images']['tmp_name'][$i] ?? '';
            $err  = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size = $_FILES['images']['size'][$i] ?? 0;

            if ($err !== UPLOAD_ERR_OK) continue;
            if (!in_array($type, $allowedTypes)) continue;
            if ($size <= 0 || $size > $maxSizeBytes) continue;
            if (!is_uploaded_file($tmp)) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
            $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
            $filename = sprintf('p%d-%s-%s.%s', $productId, $safeBase ?: 'img', bin2hex(random_bytes(4)), strtolower($ext));
            $destPath = $targetDir . $filename;

            if (move_uploaded_file($tmp, $destPath)) {
                $url = BASE_URL . 'uploads/products/' . $filename;
                $saved[] = [
                    'url' => $url,
                    'position' => $position++,
                    'is_primary' => 0,
                ];
            }
        }

        if (empty($saved)) {
            return $this->jsonError('Nenhuma imagem válida foi enviada');
        }

        // Inserir e recuperar linhas criadas (id)
        $imgModel->insertMany($productId, $saved);
        $all = $imgModel->getByProduct($productId);

        // Se o produto não tem principal ainda, define a primeira
        $hasPrimary = false;
        foreach ($all as $im) { if ((int)$im['is_primary'] === 1) { $hasPrimary = true; break; } }
        if (!$hasPrimary && !empty($all)) {
            $firstId = $all[0]['id'];
            $imgModel->setPrimary($productId, (int)$firstId);
            $productModel->update($productId, ['image' => $all[0]['url']]);
            // refetch
            $all = $imgModel->getByProduct($productId);
        }

        return $this->jsonSuccess(['images' => $all]);
    }

    /**
     * Reordenar imagens (AJAX)
     */
    public function reorderImages()
    {
        $this->requireAdmin();
        $productId = (int)$this->param('id');
        $order = $_POST['order'] ?? [];
        if (!is_array($order) || empty($order)) return $this->jsonError('Ordem inválida');

        $imgModel = new \App\Models\ProductImage();
        $imgModel->updatePositions($productId, array_map('intval', $order));
        return $this->jsonSuccess('Ordem atualizada');
    }

    /**
     * Remover imagem (AJAX)
     */
    public function deleteImage()
    {
        $this->requireAdmin();
        $this->ensureConstants();
        $productId = (int)$this->param('id');
        $imageId = (int)$this->param('imageId');

        $imgModel = new \App\Models\ProductImage();
        $productModel = new Product();

        $image = $imgModel->findById($imageId, $productId);
        if (!$image) return $this->jsonError('Imagem não encontrada', 404);

        // Remove registro
        $deleted = $imgModel->delete($imageId);

        // Se for arquivo local (uploads/products), tente excluir fisicamente
        $url = $image['url'] ?? '';
        $uploadsPrefix = BASE_URL . 'uploads/products/';
        if ($deleted && strpos($url, $uploadsPrefix) === 0) {
            $filename = substr($url, strlen($uploadsPrefix));
            $filePath = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $filename;
            if (is_file($filePath)) { @unlink($filePath); }
        }

        // Se era principal, definir outra
        if ($deleted && (int)$image['is_primary'] === 1) {
            $first = $imgModel->getFirstForProduct($productId);
            if ($first) {
                $imgModel->setPrimary($productId, (int)$first['id']);
                $productModel->update($productId, ['image' => $first['url']]);
            } else {
                // sem imagens, limpa o campo image
                $productModel->update($productId, ['image' => null]);
            }
        }

        $remaining = $imgModel->getByProduct($productId);
        return $this->jsonSuccess(['images' => $remaining]);
    }

    /**
     * Definir imagem principal (AJAX)
     */
    public function setPrimaryImage()
    {
        $this->requireAdmin();
        $productId = (int)$this->param('id');
        $imageId = (int)$this->param('imageId');
        $imgModel = new \App\Models\ProductImage();
        $productModel = new Product();

        $image = $imgModel->findById($imageId, $productId);
        if (!$image) return $this->jsonError('Imagem não encontrada', 404);

        $imgModel->setPrimary($productId, $imageId);
        $productModel->update($productId, ['image' => $image['url']]);

        $all = $imgModel->getByProduct($productId);
        return $this->jsonSuccess(['images' => $all]);
    }
    
    /**
     * Deletar produto ou conjunto
     */
    public function destroy()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $productModel = new Product();
        $product = $productModel->find($id);
        
        // Se não encontrou na tabela products, verifica se é um conjunto
        if (!$product) {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            
            // Verifica se é um conjunto
            $stmt = $pdo->prepare('SELECT id FROM sets WHERE id = ?');
            $stmt->execute([$id]);
            $set = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($set) {
                // É um conjunto - deleta da tabela sets
                try {
                    // Deleta itens do conjunto primeiro
                    $pdo->prepare('DELETE FROM set_items WHERE set_id = ?')->execute([$id]);
                    // Deleta o conjunto
                    $stmt = $pdo->prepare('DELETE FROM sets WHERE id = ?');
                    $stmt->execute([$id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = 'Conjunto deletado com sucesso';
                    } else {
                        $_SESSION['error'] = 'Erro ao deletar conjunto';
                    }
                } catch (\PDOException $e) {
                    error_log('Erro ao deletar conjunto: ' . $e->getMessage());
                    $_SESSION['error'] = 'Erro ao deletar conjunto: ' . $e->getMessage();
                }
                
                return $this->redirect(BASE_URL . 'adm/produtos');
            }
            
            $_SESSION['error'] = 'Produto não encontrado';
            return $this->redirect(BASE_URL . 'adm/produtos');
        }
        
        // É um produto normal
        $deleted = $productModel->delete($id);
        
        if ($deleted) {
            $_SESSION['success'] = 'Produto deletado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao deletar produto';
        }
        
        return $this->redirect(BASE_URL . 'adm/produtos');
    }
    
    /**
     * Alternar status ativo/inativo
     */
    public function toggleActive()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if ($product) {
            $newStatus = $product['active'] == 1 ? 0 : 1;
            $productModel->update($id, ['active' => $newStatus]);
            
            return $this->jsonSuccess([
                'message' => 'Status atualizado',
                'active' => $newStatus
            ]);
        }
        
        return $this->jsonError('Produto não encontrado');
    }
}
