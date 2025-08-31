<?php
$pageTitle = 'Admin • Produto';
include '../../../includes/head.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = [
  'title' => '', 'description' => '', 'price' => '0.00', 'image' => '', 'sizes' => 'P,M,G,GG', 'active' => 1
];
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) $product = $row;
}
?>
<body>
<?php include '../../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0"><?= $id ? 'Editar' : 'Novo' ?> Produto</h1>
    <a href="index.php" class="btn btn-sm btn-outline-light">Voltar</a>
  </div>
  <form method="post" action="save.php" class="row g-3">
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
      <label class="form-label">Imagem (URL relativa em assets/img/...)</label>
      <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($product['image']) ?>" placeholder="assets/img/produto.jpg" required>
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
<?php include '../../../includes/footer.php'; ?>
<?php include '../../../includes/scripts.php'; ?>
</body>
</html>
