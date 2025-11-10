<?php
$pageTitle = 'Admin • Produto';
require_once __DIR__ . '/../../../includes/head.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/icon-helper.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = [
  'title' => '', 'description' => '', 'price' => '0.00', 'image' => '', 'image_type' => '', 'sizes' => 'P,M,G,GG', 'active' => 1
];
$productImages = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $product = $row;
        
        // Buscar todas as imagens do produto
        $stmtImages = $pdo->prepare('SELECT id, display_order, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC');
        $stmtImages->execute([$id]);
        $productImages = $stmtImages->fetchAll();
    }
}
?>
<body>
<?php include '../../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0"><?= $id ? 'Editar' : 'Novo' ?> Produto</h1>
    <a href="../index-adm.php" class="btn btn-sm btn-outline-light">Voltar</a>
  </div>
  
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php
        $errorMsg = 'Erro desconhecido.';
        switch($_GET['error']) {
          case 'dados_invalidos': $errorMsg = 'Preencha todos os campos obrigatórios.'; break;
          case 'tipo_invalido': $errorMsg = 'Tipo de arquivo inválido. Use JPG, PNG, WEBP ou GIF.'; break;
          case 'arquivo_grande': $errorMsg = 'Arquivo muito grande. Máximo por imagem: 10MB.'; break;
          case 'arquivo_muito_grande': $errorMsg = 'O tamanho total dos arquivos excede o limite (50MB). Envie menos imagens ou reduza o tamanho delas.'; break;
          case 'erro_leitura': $errorMsg = 'Erro ao ler o arquivo de imagem.'; break;
          case 'erro_upload': $errorMsg = 'Erro no upload da imagem.'; break;
          case 'imagem_obrigatoria': $errorMsg = 'Ao menos uma imagem é obrigatória para novos produtos.'; break;
          case 'erro_banco': $errorMsg = 'Erro ao salvar no banco de dados.'; break;
          case 'campo_obrigatorio': $errorMsg = 'Um campo obrigatório não foi preenchido.'; break;
          case 'duplicado': $errorMsg = 'Já existe um produto com estes dados.'; break;
        }
        echo htmlspecialchars($errorMsg);
        
        // Mostrar detalhes técnicos em desenvolvimento
        if (isset($_GET['msg']) && !empty($_GET['msg'])) {
          echo '<br><small class="text-muted">Detalhes: ' . htmlspecialchars($_GET['msg']) . '</small>';
        }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Produto salvo com sucesso!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <form method="post" action="adm/products/save.php" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="col-md-6">
      <label class="form-label">Título</label>
      <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Preço</label>
      <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Ativo</label>
      <select name="active" class="form-select">
        <option value="1" <?= $product['active'] ? 'selected' : '' ?>>Ativo</option>
        <option value="0" <?= !$product['active'] ? 'selected' : '' ?>>Inativo</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Imagens do Produto</label>
      
      <?php if (!empty($productImages)): ?>
        <div class="row mb-3" id="currentImages">
          <?php foreach ($productImages as $img): ?>
            <div class="col-md-3 mb-2 position-relative" data-image-id="<?= (int)$img['id'] ?>">
              <img src="image.php?img_id=<?= (int)$img['id'] ?>" alt="Imagem" class="img-thumbnail w-100" style="height: 150px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-1">
                <?php if ($img['is_primary']): ?>
                  <span class="badge bg-success">Principal</span>
                <?php else: ?>
                  <button type="button" class="btn btn-sm btn-success set-primary-btn" data-image-id="<?= (int)$img['id'] ?>" title="Definir como principal">
                    <?= icon('star', 'icon') ?>
                  </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-danger delete-image-btn" data-image-id="<?= (int)$img['id'] ?>" title="Remover">
                  <?= icon('trash', 'icon') ?>
                </button>
              </div>
              <small class="text-muted d-block text-center">Ordem: <?= (int)$img['display_order'] ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" multiple <?= ($id && !empty($productImages)) ? '' : 'required' ?>>
      <small class="form-text text-muted">
        Selecione uma ou mais imagens. Formatos aceitos: JPG, PNG, WEBP, GIF.<br>
        <strong>Limites:</strong> 10MB por imagem | 50MB no total | Até 20 imagens<br>
        <?php if (!empty($productImages)): ?>
          <strong>Dica:</strong> A primeira imagem marcada como "Principal" será exibida como capa do produto.
        <?php else: ?>
          <strong>Dica:</strong> A primeira imagem será definida automaticamente como principal.
        <?php endif; ?>
      </small>
    </div>
    <div class="col-12">
      <label class="form-label">Tamanhos (separados por vírgula)</label>
      <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars($product['sizes']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Descrição</label>
      <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>
    <div class="col-12">
      <button class="btn btn-custom">Salvar</button>
    </div>
  </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productId = <?= (int)$id ?>;
    
    // Definir imagem como principal
    document.querySelectorAll('.set-primary-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            if (!confirm('Definir esta imagem como principal?')) return;
            
            fetch('image-set-primary.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}&image_id=${imageId}&csrf_token=<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao definir imagem principal');
                }
            })
            .catch(err => alert('Erro: ' + err));
        });
    });
    
    // Deletar imagem
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            if (!confirm('Remover esta imagem?')) return;
            
            fetch('image-delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}&image_id=${imageId}&csrf_token=<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao remover imagem');
                }
            })
            .catch(err => alert('Erro: ' + err));
        });
    });
});
</script>

<?php include '../../../includes/footer.php'; ?>
<?php include '../../../includes/scripts.php'; ?>
</body>
</html>
