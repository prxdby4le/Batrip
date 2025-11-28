<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use PDO;

class SetController extends Controller
{
    /**
     * Listar todos os conjuntos
     */
    public function index()
    {
        $this->requireAdmin();
        
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query('SELECT * FROM sets ORDER BY created_at DESC');
        $sets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $this->view('admin/sets/index', [
            'pageTitle' => 'Gerenciar Conjuntos - Admin',
            'sets' => $sets
        ], 'admin');
    }
    
    /**
     * Formulário de criar conjunto
     */
    public function create()
    {
        $this->requireAdmin();
        
        $pdo = Database::getInstance()->getConnection();
        
        // Buscar produtos ativos para usar em conjuntos
        $productModel = new Product();
        $products = $productModel->getActive(0);
        
        $this->view('admin/sets/create', [
            'pageTitle' => 'Novo Conjunto - Admin',
            'products' => $products
        ], 'admin');
    }
    
    /**
     * Salvar novo conjunto
     */
    public function store()
    {
        $this->requireAdmin();
        $this->ensureConstants();
        
        $pdo = Database::getInstance()->getConnection();
        
        // Preparar dados do conjunto
        $setData = [
            'title' => $this->request->post('title'),
            'description' => $this->request->post('description') ?? '',
            'price' => $this->request->post('price'),
            'image' => '',
            'active' => $this->request->post('active') ? 1 : 0
        ];
        
        // Validação
        $errors = [];
        if (empty($setData['title'])) {
            $errors[] = 'Título é obrigatório';
        }
        if (empty($setData['price']) || $setData['price'] <= 0) {
            $errors[] = 'Preço deve ser maior que zero';
        }
        
        $setItems = $this->request->post('set_items') ?? [];
        $hasItems = false;
        if (is_array($setItems)) {
            foreach ($setItems as $itemData) {
                if (isset($itemData['checked']) && (string)$itemData['checked'] === '1') {
                    $hasItems = true;
                    break;
                }
            }
        }
        if (!$hasItems) {
            $errors[] = 'Para criar um conjunto, é necessário selecionar pelo menos um produto.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $productModel = new Product();
            $products = $productModel->getActive(0);
            $this->view('admin/sets/create', [
                'pageTitle' => 'Novo Conjunto - Admin',
                'products' => $products,
                'errors' => $errors
            ], 'admin');
            return;
        }
        
        // Processar upload de imagem
        $primaryImageUrl = null;
        $savedImage = null;
        
        // Processar imagens múltiplas (nome do campo: images[])
        if (isset($_FILES['images']) && isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
            $maxPerProduct = defined('IMAGES_PER_PRODUCT_MAX') ? (int)IMAGES_PER_PRODUCT_MAX : 12;
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $maxSizeBytes = (defined('IMAGE_MAX_UPLOAD_MB') ? (int)IMAGE_MAX_UPLOAD_MB : 5) * 1024 * 1024;

            // Garantir diretório assets/img/sets/
            $targetDir = ROOT_PATH . '/public/assets/img/sets/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }
            // Garantir permissões de escrita
            @chmod($targetDir, 0777);

            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < $fileCount && $i < 1; $i++) { // Apenas primeira imagem para conjunto
                $name = $_FILES['images']['name'][$i];
                $type = $_FILES['images']['type'][$i] ?? '';
                $tmp  = $_FILES['images']['tmp_name'][$i] ?? '';
                $err  = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                $size = $_FILES['images']['size'][$i] ?? 0;

                if ($err !== UPLOAD_ERR_OK) {
                    error_log("SetController::store - Erro no upload: {$err}");
                    continue;
                }
                if (!in_array($type, $allowedTypes)) {
                    error_log("SetController::store - Tipo não permitido: {$type}");
                    continue;
                }
                if ($size <= 0 || $size > $maxSizeBytes) {
                    error_log("SetController::store - Tamanho inválido: {$size}");
                    continue;
                }
                if (!is_uploaded_file($tmp)) {
                    error_log("SetController::store - Arquivo não é upload válido");
                    continue;
                }

                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
                $filename = sprintf('set_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;

                error_log("SetController::store - Tentando salvar: {$destPath}");
                if (move_uploaded_file($tmp, $destPath)) {
                    @chmod($destPath, 0644);
                    // Salva caminho relativo no banco
                    $savedImage = '/assets/img/sets/' . $filename;
                    $primaryImageUrl = $savedImage;
                    error_log("SetController::store - Imagem salva com sucesso: {$savedImage}");
                    break;
                } else {
                    error_log("SetController::store - ERRO ao mover arquivo de {$tmp} para {$destPath}");
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
                // Garantir diretório assets/img/sets/
                $targetDir = ROOT_PATH . '/public/assets/img/sets/';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                @chmod($targetDir, 0777);
                $ext = pathinfo($name, PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($name, PATHINFO_FILENAME));
                $filename = sprintf('set_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;
                error_log("SetController::store - Tentando salvar (legacy): {$destPath}");
                if (move_uploaded_file($tmp, $destPath)) {
                    @chmod($destPath, 0644);
                    // Salva caminho relativo no banco
                    $savedImage = '/assets/img/sets/' . $filename;
                    $primaryImageUrl = $savedImage;
                    error_log("SetController::store - Imagem salva (legacy): {$savedImage}");
                } else {
                    error_log("SetController::store - ERRO ao mover arquivo (legacy) de {$tmp} para {$destPath}");
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
                return $this->redirect(BASE_URL . 'adm/conjuntos/novo');
            }
            
            // Processar produtos selecionados para o conjunto
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
            return $this->redirect(BASE_URL . 'adm/conjuntos');
        } catch (\PDOException $e) {
            error_log('Erro ao criar conjunto: ' . $e->getMessage());
            $_SESSION['error'] = 'Erro ao criar conjunto: ' . $e->getMessage();
            return $this->redirect(BASE_URL . 'adm/conjuntos/novo');
        }
    }
    
    /**
     * Formulário de editar conjunto
     */
    public function edit()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare('SELECT * FROM sets WHERE id = ?');
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set) {
            $_SESSION['error'] = 'Conjunto não encontrado';
            return $this->redirect(BASE_URL . 'adm/conjuntos');
        }
        
        // Buscar produtos ativos
        $productModel = new Product();
        $products = $productModel->getActive(0);
        
        // Buscar itens do conjunto
        $stmt = $pdo->prepare('SELECT product_id, quantity FROM set_items WHERE set_id = ?');
        $stmt->execute([$id]);
        $setItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selectedProducts = [];
        foreach ($setItems as $item) {
            $selectedProducts[$item['product_id']] = $item['quantity'];
        }
        
        $this->view('admin/sets/edit', [
            'pageTitle' => 'Editar Conjunto - Admin',
            'set' => $set,
            'products' => $products,
            'selectedProducts' => $selectedProducts
        ], 'admin');
    }
    
    /**
     * Atualizar conjunto
     */
    public function update()
    {
        $this->requireAdmin();
        $this->ensureConstants();
        
        $id = $this->param('id');
        $pdo = Database::getInstance()->getConnection();
        
        // Verificar se conjunto existe
        $stmt = $pdo->prepare('SELECT id FROM sets WHERE id = ?');
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set) {
            $_SESSION['error'] = 'Conjunto não encontrado';
            return $this->redirect(BASE_URL . 'adm/conjuntos');
        }
        
        // Validação
        $errors = [];
        if (empty($this->request->post('title'))) {
            $errors[] = 'Título é obrigatório';
        }
        if (empty($this->request->post('price')) || $this->request->post('price') <= 0) {
            $errors[] = 'Preço deve ser maior que zero';
        }
        
        $setItems = $this->request->post('set_items') ?? [];
        $hasItems = false;
        if (is_array($setItems)) {
            foreach ($setItems as $itemData) {
                if (isset($itemData['checked']) && (string)$itemData['checked'] === '1') {
                    $hasItems = true;
                    break;
                }
            }
        }
        if (!$hasItems) {
            $errors[] = 'Para atualizar um conjunto, é necessário selecionar pelo menos um produto.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $this->redirect(BASE_URL . "adm/conjuntos/{$id}/editar");
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
                // Garantir diretório assets/img/sets/
                $targetDir = ROOT_PATH . '/public/assets/img/sets/';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                @chmod($targetDir, 0777);
                
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
                $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($file['name'], PATHINFO_FILENAME));
                $filename = sprintf('set_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), strtolower($ext));
                $destPath = $targetDir . $filename;
                
                error_log("SetController::update - Tentando salvar: {$destPath}");
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    @chmod($destPath, 0644);
                    // Salva caminho relativo no banco
                    $setData['image'] = '/assets/img/sets/' . $filename;
                    error_log("SetController::update - Imagem salva: {$setData['image']}");
                } else {
                    error_log("SetController::update - ERRO ao mover arquivo de {$file['tmp_name']} para {$destPath}");
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
            
            // Remover itens antigos
            $pdo->prepare('DELETE FROM set_items WHERE set_id = ?')->execute([$id]);
            
            // Adicionar novos itens
            if (is_array($setItems) && !empty($setItems)) {
                $insertStmt = $pdo->prepare('INSERT INTO set_items (set_id, product_id, quantity) VALUES (?, ?, ?)');
                
                foreach ($setItems as $productId => $itemData) {
                    $checked = isset($itemData['checked']) && (string)$itemData['checked'] === '1';
                    $qty = isset($itemData['qty']) ? max(1, (int)$itemData['qty']) : 1;
                    $productId = (int)$productId;
                    
                    if ($checked && $productId > 0) {
                        $insertStmt->execute([$id, $productId, $qty]);
                    }
                }
            }
            
            $_SESSION['success'] = 'Conjunto atualizado com sucesso';
            return $this->redirect(BASE_URL . 'adm/conjuntos');
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar conjunto: ' . $e->getMessage());
            $_SESSION['error'] = 'Erro ao atualizar conjunto: ' . $e->getMessage();
            return $this->redirect(BASE_URL . "adm/conjuntos/{$id}/editar");
        }
    }
    
    /**
     * Deletar conjunto
     */
    public function destroy()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $pdo = Database::getInstance()->getConnection();
        
        try {
            // Deleta itens do conjunto primeiro
            $pdo->prepare('DELETE FROM set_items WHERE set_id = ?')->execute([$id]);
            // Deleta o conjunto
            $stmt = $pdo->prepare('DELETE FROM sets WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = 'Conjunto deletado com sucesso';
            } else {
                $_SESSION['error'] = 'Conjunto não encontrado';
            }
        } catch (\PDOException $e) {
            error_log('Erro ao deletar conjunto: ' . $e->getMessage());
            $_SESSION['error'] = 'Erro ao deletar conjunto: ' . $e->getMessage();
        }
        
        return $this->redirect(BASE_URL . 'adm/conjuntos');
    }
    
    /**
     * Garante que constantes necessárias estão definidas
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
}

