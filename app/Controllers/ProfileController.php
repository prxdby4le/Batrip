<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Order;

/**
 * ProfileController - Gerenciamento de perfil do usuário
 */
class ProfileController extends Controller
{
    private User $userModel;
    private Order $orderModel;
    
    public function __construct($request = null, $params = [])
    {
        parent::__construct($request, $params);
        $this->userModel = new User();
        $this->orderModel = new Order();
    }
    
    /**
     * Exibe perfil do usuário
     */
    public function index(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/perfil';
            $_SESSION['error'] = 'Você precisa estar logado para acessar seu perfil';
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/');
            return;
        }
        
        // Buscar pedidos do usuário
        $orders = $this->orderModel->getByUserId($userId);
        
        $data = [
            'pageTitle' => 'Meu Perfil - Batrip',
            'user' => $user,
            'orders' => $orders,
            'layout' => 'main'
        ];
        
        $this->view('profile.index', $data);
    }
    
    /**
     * Exibe formulário de edição de perfil
     */
    public function edit(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/perfil/editar';
            $_SESSION['error'] = 'Você precisa estar logado para editar seu perfil';
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/');
            return;
        }
        
        $data = [
            'pageTitle' => 'Editar Perfil - Batrip',
            'user' => $user,
            'layout' => 'main'
        ];
        
        $this->view('profile.edit', $data);
    }
    
    /**
     * Atualiza perfil do usuário
     */
    public function update(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Você precisa estar logado para editar seu perfil';
            $this->redirect('/login');
            return;
        }
        
        if (!$this->request->isPost()) {
            $this->redirect('/perfil/editar');
            return;
        }
        
        // CSRF
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        if (!$this->validateCsrf($token)) {
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect('/perfil/editar');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        $name = trim($this->request->post('name') ?? '');
        $email = trim($this->request->post('email') ?? '');
        $phone = trim($this->request->post('phone') ?? '');
        $endereco = trim($this->request->post('endereco') ?? '');
        $cidade = trim($this->request->post('cidade') ?? '');
        $estado = strtoupper(trim($this->request->post('estado') ?? ''));
        $cep = preg_replace('/\D/', '', $this->request->post('cep') ?? '');
        
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        // Verifica se email já existe em outro usuário
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $errors[] = 'Este email já está em uso';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/perfil/editar');
            return;
        }
        
        // Upload do background do perfil
        $profileBgPath = null;
        if ($this->request->hasFile('profile_bg')) {
            $file = $this->request->file('profile_bg');
            if ($file && $file['tmp_name'] && $file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (in_array($ext, $allowed)) {
                    // Salvar em public/uploads/profile_bg/ para ser acessível via web
                    // Usa caminho absoluto baseado em ROOT_PATH
                    $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__));
                    $destDir = $rootPath . '/public/uploads/profile_bg/';
                    // Normaliza o caminho (remove barras duplas e barras invertidas)
                    $destDir = str_replace(['//', '\\'], '/', $destDir);
                    $destDir = rtrim($destDir, '/') . '/';
                    
                    // Garante que o diretório existe
                    if (!is_dir($destDir)) {
                        if (!mkdir($destDir, 0777, true)) {
                            error_log("ProfileController::update - ERRO ao criar diretório: $destDir");
                            $_SESSION['error'] = 'Erro ao criar diretório para upload.';
                            return;
                        }
                    }
                    
                    // Verifica se o diretório é gravável
                    if (!is_writable($destDir)) {
                        // Tenta ajustar permissões (suprime erro se não for possível em volumes Docker)
                        @chmod($destDir, 0777);
                        if (!is_writable($destDir)) {
                            error_log("ProfileController::update - ERRO: Diretório não é gravável: $destDir");
                            $_SESSION['error'] = 'Diretório de upload não tem permissão de escrita. Verifique as permissões do diretório.';
                            return;
                        }
                    }
                    
                    $filename = 'bg_' . $userId . '_' . time() . '.' . $ext;
                    $destPath = $destDir . $filename;
                    
                    
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $profileBgPath = '/uploads/profile_bg/' . $filename;
                        
                        // Verifica se o arquivo realmente existe após o upload
                        if (file_exists($destPath)) {
                            $fileSize = filesize($destPath);
                        } else {
                            error_log("ProfileController::update - AVISO CRÍTICO: Arquivo não encontrado após upload!");
                        }
                    } else {
                        $lastError = error_get_last();
                        error_log("ProfileController::update - ERRO ao salvar background em: $destPath");
                        $_SESSION['error'] = 'Erro ao fazer upload da imagem de background.';
                    }
                }
            }
        }

        // Upload da foto de perfil
        $profileImgFileName = null;
        if ($this->request->hasFile('profile_img')) {
            $file = $this->request->file('profile_img');
            if ($file) {
            }
            if ($file && $file['tmp_name'] && $file['error'] === UPLOAD_ERR_OK) {
                // Validar tipo
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                    // Salvar em public/assets/img/perfil/ para ser acessível via web
                    $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__));
                    $uploadDir = $rootPath . '/public/assets/img/perfil/';
                    // Normaliza o caminho (remove barras duplas e barras invertidas)
                    $uploadDir = str_replace(['//', '\\'], '/', $uploadDir);
                    $uploadDir = rtrim($uploadDir, '/') . '/';
                    
                    // Garante que o diretório existe
                    if (!is_dir($uploadDir)) {
                        if (!mkdir($uploadDir, 0755, true)) {
                            error_log("ProfileController::update - ERRO ao criar diretório: $uploadDir");
                            $_SESSION['error'] = 'Erro ao criar diretório para upload da foto de perfil.';
                        }
                    }
                    
                    // Verifica se o diretório é gravável
                    if (is_dir($uploadDir) && !is_writable($uploadDir)) {
                        @chmod($uploadDir, 0777); // Tenta 777 para garantir escrita
                    }
                    
                    // Nome do arquivo: usuario_{userId}.jpg
                    $fileName = 'usuario_' . $userId . '.jpg';
                    $filePath = $uploadDir . $fileName;
                    
                    
                    // Remover arquivo anterior se existir (tanto no novo quanto no antigo local)
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                    // Também remove do diretório antigo se existir
                    $oldPath = $rootPath . '/assets/img/perfil/' . $fileName;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                    
                    // Mover arquivo
                    if (!is_writable($uploadDir)) {
                        error_log("ProfileController::update - ERRO: Diretório não é gravável: $uploadDir");
                        $_SESSION['error'] = 'Diretório de upload não tem permissão de escrita.';
                    } elseif (!file_exists($file['tmp_name'])) {
                        $_SESSION['error'] = 'Arquivo temporário não encontrado.';
                    } elseif (move_uploaded_file($file['tmp_name'], $filePath)) {
                        $profileImgFileName = $fileName;
                        // Garante permissões de leitura
                        @chmod($filePath, 0644);
                    } else {
                        $lastError = error_get_last();
                        error_log("ProfileController::update - ERRO ao salvar foto de perfil em: $filePath");
                        $_SESSION['error'] = 'Erro ao fazer upload da foto de perfil. Verifique as permissões do diretório.';
                    }
                }
            }
        }

        // Preparar dados para atualização usando os nomes corretos da tabela
        // IMPORTANTE: Sempre incluir todos os campos, mesmo vazios, para garantir atualização completa
        $updateData = [
            'name' => $name,
            'email' => strtolower($email)
        ];
        
        // Campos opcionais - sempre incluir (pode ser null ou vazio)
        $updateData['phone'] = !empty($phone) ? $phone : null;
        $updateData['endereco'] = !empty($endereco) ? $endereco : null;
        $updateData['cidade'] = !empty($cidade) ? $cidade : null;
        $updateData['estado'] = (!empty($estado) && strlen($estado) === 2) ? strtoupper($estado) : null;
        $updateData['cep'] = (!empty($cep) && strlen($cep) === 8) ? $cep : null;
        
        // Campos de upload - só adicionar se houver arquivo
        if ($profileBgPath) {
            $updateData['profile_bg'] = $profileBgPath;
        }
        if ($profileImgFileName) {
            $updateData['profile_img'] = $profileImgFileName;
        } else {
        }

        // Garantir que os campos necessários existam no banco antes de atualizar
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $stmt = $pdo->query("SHOW COLUMNS FROM users");
        $existingColumns = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Field');
        
        // Adicionar phone se não existir
        if (!in_array('phone', $existingColumns)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
                $this->userModel->clearColumnsCache();
            } catch (\Exception $e) {
            }
        }
        
        // Adicionar profile_bg se não existir
        if (!in_array('profile_bg', $existingColumns)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN profile_bg VARCHAR(255) NULL AFTER profile_img");
                $this->userModel->clearColumnsCache();
            } catch (\Exception $e) {
            }
        }
        
        // Limpar cache ANTES da atualização para garantir que temos as colunas mais recentes
        $this->userModel->clearColumnsCache();
        
        // Log dos dados que serão atualizados
        
        try {
            $success = $this->userModel->update($userId, $updateData);
            
            
            if (!$success) {
                // Verificar se há campos sendo filtrados incorretamente
                $pdo = \App\Core\Database::getInstance()->getConnection();
                $stmt = $pdo->query("SHOW COLUMNS FROM users");
                $availableColumns = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Field');
                $missingColumns = array_diff(array_keys($updateData), $availableColumns);
                
                if (!empty($missingColumns)) {
                    
                    // Tentar adicionar os campos faltantes
                    foreach ($missingColumns as $col) {
                        if ($col === 'phone') {
                            try {
                                $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
                            } catch (\Exception $e) {
                            }
                        } elseif ($col === 'profile_bg') {
                            try {
                                $pdo->exec("ALTER TABLE users ADD COLUMN profile_bg VARCHAR(255) NULL AFTER profile_img");
                            } catch (\Exception $e) {
                            }
                        }
                    }
                    
                    // Limpar cache e tentar novamente
                    $this->userModel->clearColumnsCache();
                    $success = $this->userModel->update($userId, $updateData);
                    
                    if (!$success) {
                        throw new \Exception('Falha ao atualizar registro no banco de dados');
                    }
                } else {
                    throw new \Exception('Falha ao atualizar registro no banco de dados');
                }
            }
            
            // Limpar cache novamente após atualização bem-sucedida
            $this->userModel->clearColumnsCache();
            
            // Recarregar dados do usuário após atualização para garantir que está atualizado
            $updatedUser = $this->userModel->findById($userId);
            
            if ($updatedUser) {
                $_SESSION['user_name'] = $updatedUser['name'];
                $_SESSION['user_email'] = $updatedUser['email'];
            } else {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
            }
            
            $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            
            // Redirecionar com cache bust para garantir que a página seja recarregada
            $this->redirect('/perfil?updated=' . time());
            
        } catch (\Exception $e) {
            \App\Helpers\Logger::error('Erro ao atualizar perfil', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'update_data' => $updateData,
                'trace' => $e->getTraceAsString()
            ]);
            
            
            $_SESSION['error'] = 'Erro ao atualizar perfil: ' . htmlspecialchars($e->getMessage());
            $this->redirect('/perfil/editar');
            return;
        }
    }
    
    /**
     * Exibe pedidos do usuário
     */
    public function orders(): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/pedidos';
            $_SESSION['error'] = 'Você precisa estar logado para ver seus pedidos';
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $orders = $this->orderModel->getByUserId($userId);
        
        $data = [
            'pageTitle' => 'Meus Pedidos - Batrip',
            'orders' => $orders,
            'layout' => 'main'
        ];
        
        $this->view('profile.orders', $data);
    }
    
    /**
     * Serve arquivos de upload (substitui serve-upload.php)
     */
    public function serveUpload(): void
    {
        // Limpa output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Pega o caminho do arquivo da query string
        $filePath = $this->request->get('file', '');
        
        if (empty($filePath)) {
            http_response_code(400);
            die('File parameter required');
        }
        
        // Remove qualquer tentativa de path traversal
        $filePath = str_replace(['..', '\\'], '', $filePath);
        $filePath = ltrim($filePath, '/');
        
        // Define ROOT_PATH se necessário
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(dirname(__DIR__)));
        }
        
        // Constrói o caminho completo (tenta múltiplos caminhos possíveis)
        $possiblePaths = [
            ROOT_PATH . '/public/' . $filePath,  // Caminho padrão
            ROOT_PATH . '/' . $filePath,         // Sem public/ (legado)
        ];
        
        $realPath = null;
        $allowedDir = realpath(ROOT_PATH . '/public');
        
        // Tenta encontrar o arquivo em um dos caminhos possíveis
        foreach ($possiblePaths as $fullPath) {
            $testPath = realpath($fullPath);
            if ($testPath && $allowedDir && strpos($testPath, $allowedDir) === 0) {
                $realPath = $testPath;
                break;
            }
        }
        
        // Se não encontrou no diretório public, tenta no diretório raiz (para compatibilidade)
        if (!$realPath) {
            $rootAllowedDir = realpath(ROOT_PATH);
            foreach ($possiblePaths as $fullPath) {
                $testPath = realpath($fullPath);
                if ($testPath && $rootAllowedDir && strpos($testPath, $rootAllowedDir) === 0) {
                    // Verifica se está em um subdiretório permitido (uploads, assets)
                    if (strpos($testPath, $rootAllowedDir . '/uploads') === 0 || 
                        strpos($testPath, $rootAllowedDir . '/assets') === 0) {
                        $realPath = $testPath;
                        break;
                    }
                }
            }
        }
        
        if (!$realPath) {
            http_response_code(403);
            die('Access denied');
        }
        
        if (!file_exists($realPath) || !is_file($realPath)) {
            http_response_code(404);
            die('File not found');
        }
        
        // Determina o tipo MIME
        $mimeType = mime_content_type($realPath);
        if (!$mimeType) {
            $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
            ];
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        }
        
        // Envia o arquivo
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: public, max-age=31536000');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($realPath)) . ' GMT');
        readfile($realPath);
        exit;
    }
    
    /**
     * Exibe detalhes de um pedido específico
     */
    public function showOrder($params = []): void
    {
        // Bloqueia usuários anônimos
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/pedidos';
            $_SESSION['error'] = 'Você precisa estar logado para ver seus pedidos';
            $this->redirect('/login');
            return;
        }
        
        $orderId = (int)($params['id'] ?? $_GET['id'] ?? 0);
        
        if ($orderId <= 0) {
            $_SESSION['error'] = 'Pedido inválido';
            $this->redirect('/pedidos');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Buscar pedido garantindo que pertence ao usuário
        $order = $this->orderModel->findById($orderId);
        
        if (!$order || (int)$order['user_id'] !== $userId) {
            $_SESSION['error'] = 'Pedido não encontrado';
            $this->redirect('/pedidos');
            return;
        }
        
        // Decodificar dados JSON
        $items = [];
        if (!empty($order['items'])) {
            $items = json_decode($order['items'], true) ?: [];
        }
        
        $address = [];
        if (!empty($order['endereco'])) {
            $address = json_decode($order['endereco'], true) ?: [];
        }
        
        $frete = [];
        if (!empty($order['frete'])) {
            $frete = json_decode($order['frete'], true) ?: [];
        }
        
        $data = [
            'pageTitle' => 'Detalhe do Pedido #' . $orderId . ' - Batrip',
            'order' => $order,
            'items' => $items,
            'address' => $address,
            'frete' => $frete,
            'layout' => 'main'
        ];
        
        $this->view('profile.order-detail', $data);
    }
}

