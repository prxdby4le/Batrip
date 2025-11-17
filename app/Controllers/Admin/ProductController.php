<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Listar todos produtos
     */
    public function index()
    {
        $this->requireAdmin();
        
        $productModel = new Product();
        $products = $productModel->all([], 'created_at DESC');
        
        $this->view('admin/products/index', [
            'pageTitle' => 'Gerenciar Produtos - Admin',
            'products' => $products
        ], 'admin');
    }
    
    /**
     * Formulário de criar produto
     */
    public function create()
    {
        $this->requireAdmin();
        
        $this->view('admin/products/create', [
            'pageTitle' => 'Novo Produto - Admin'
        ], 'admin');
    }
    
    /**
     * Salvar novo produto
     */
    public function store()
    {
        $this->requireAdmin();
        
        // Validação básica
        $errors = [];
        
        if (empty($this->request->post('title'))) {
            $errors[] = 'Título é obrigatório';
        }
        
        if (empty($this->request->post('price')) || $this->request->post('price') <= 0) {
            $errors[] = 'Preço deve ser maior que zero';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $this->request->all();
            return $this->redirect(BASE_URL . 'adm/produtos/novo');
        }
        
        // Preparar dados
        $type = $this->request->post('type') ?? 'product';
        $data = [
            'title' => $this->request->post('title'),
            'description' => $this->request->post('description'),
            'price' => $this->request->post('price'),
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
        }

        $_SESSION['success'] = 'Produto criado com sucesso!';
        return $this->redirect(BASE_URL . 'adm/produtos');
    }
    
    /**
     * Formulário de editar produto
     */
    public function edit()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product) {
            $_SESSION['error'] = 'Produto não encontrado';
            return $this->redirect('adm/produtos');
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
     * Atualizar produto
     */
    public function update()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
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
        $productModel = new Product();
        $updated = $productModel->update($id, $data);
        
        if ($updated) {
            $_SESSION['success'] = 'Produto atualizado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar produto';
        }
        
        return $this->redirect('adm/produtos');
    }

    /**
     * Upload de múltiplas imagens (AJAX)
     */
    public function uploadImages()
    {
        $this->requireAdmin();
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
     * Deletar produto
     */
    public function destroy()
    {
        $this->requireAdmin();
        
        $id = $this->param('id');
        
        $productModel = new Product();
        $deleted = $productModel->delete($id);
        
        if ($deleted) {
            $_SESSION['success'] = 'Produto deletado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao deletar produto';
        }
        
        return $this->redirect('adm/produtos');
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
