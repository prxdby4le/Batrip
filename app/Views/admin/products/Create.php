
<?php
/**
 * View: Admin Products Create
 */

// Garante que config.php foi carregado (define constantes necessárias)
if (!defined('IMAGES_PER_PRODUCT_MAX')) {
    require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
}

$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<!-- Navbar Admin -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-shield-lock me-2"></i>Administração</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm/produtos">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>adm/pedidos">Pedidos</a>
                </li>
                <!-- Adicione mais links conforme necessário -->
            </ul>
            <div class="d-flex">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> Retornar para visão de cliente
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>adm/produtos" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Erros de Validação</h5>
        <ul class="mb-0">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="bi bi-plus-circle me-2"></i>Novo Produto
                </h4>
                
                <form method="POST" action="<?php echo BASE_URL; ?>adm/produtos/salvar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($old_input['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($old_input['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" 
                                       value="<?php echo htmlspecialchars($old_input['price'] ?? '0.00'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="type" class="form-label">Tipo de item *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="product" <?php echo ($old_input['type'] ?? 'product') === 'product' ? 'selected' : ''; ?>>Produto normal</option>
                                <option value="set" <?php echo ($old_input['type'] ?? '') === 'set' ? 'selected' : ''; ?>>Conjunto</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Categoria</label>
                            <select class="form-select" id="category" name="category">
                                <option value="geral" <?php echo ($old_input['category'] ?? '') === 'geral' ? 'selected' : ''; ?>>Geral</option>
                                <option value="camiseta" <?php echo ($old_input['category'] ?? '') === 'camiseta' ? 'selected' : ''; ?>>Camiseta</option>
                                <option value="moletom" <?php echo ($old_input['category'] ?? '') === 'moletom' ? 'selected' : ''; ?>>Moletom</option>
                                <option value="calca" <?php echo ($old_input['category'] ?? '') === 'calca' ? 'selected' : ''; ?>>Calça</option>
                                <option value="conjunto" <?php echo ($old_input['category'] ?? '') === 'conjunto' ? 'selected' : ''; ?>>Conjunto</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Seção de produtos do conjunto (aparece apenas quando type = "set") -->
                    <div id="setProductsSection" class="mb-4" style="display: none;">
                        <hr class="my-4">
                        <h5 class="mb-3">
                            <i class="bi bi-box-seam me-2"></i>Produtos do Conjunto
                        </h5>
                        <p class="text-muted mb-3">Selecione os produtos que compõem este conjunto e defina as quantidades.</p>
                        <?php 
                        $availableProducts = $availableProducts ?? [];
                        if (!empty($availableProducts)): 
                        ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">Incluir</th>
                                        <th>Produto</th>
                                        <th style="width:140px;">Preço</th>
                                        <th style="width:140px;">Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableProducts as $p): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       class="form-check-input set-product-checkbox" 
                                                       name="set_items[<?= (int)$p['id'] ?>][checked]" 
                                                       value="1" 
                                                       data-product-id="<?= (int)$p['id'] ?>">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($p['title']) ?></strong>
                                            </td>
                                            <td>R$ <?= number_format((float)$p['price'], 2, ',', '.') ?></td>
                                            <td>
                                                <input type="number" 
                                                       min="1" 
                                                       step="1" 
                                                       class="form-control form-control-sm set-product-qty" 
                                                       name="set_items[<?= (int)$p['id'] ?>][qty]" 
                                                       value="1"
                                                       data-product-id="<?= (int)$p['id'] ?>"
                                                       disabled>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Nenhum produto ativo disponível. Crie produtos antes de criar um conjunto.
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagens do produto</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-light" id="btnOpenImagesModal">
                                <i class="bi bi-images me-1"></i> Adicionar imagens
                            </button>
                            <small class="text-white align-self-center">Arraste e solte ou clique para selecionar (até <?php echo IMAGES_PER_PRODUCT_MAX; ?> imagens)</small>
                        </div>
                        <input type="hidden" name="legacy_no_image" value="1">
                        <div id="imagesMiniPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                            <label class="form-check-label" for="active">
                                Produto ativo (visível no site)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Criar Produto
                        </button>
                        <a href="<?php echo BASE_URL; ?>adm/produtos" class="btn btn-outline-light">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- Modal de Upload de Imagens -->
<div class="modal fade" id="imagesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-images me-2"></i>Adicionar imagens</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dropzone" class="p-4 border border-secondary rounded text-center" style="border-style:dashed !important;">
                        <p class="mb-2">Solte as imagens aqui</p>
                        <p class="text-white small">ou</p>
                        <button type="button" class="btn btn-outline-light" id="btnPickFiles"><i class="bi bi-upload me-1"></i> Selecionar arquivos</button>
                        <input type="file" id="fileInputHidden" accept="image/*" multiple class="d-none">
                </div>
                <div id="previewList" class="mt-3 row g-3"></div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Concluir</button>
            </div>
        </div>
    </div>
    </div>

<?php ob_start(); ?>
<script>
(function(){
    const maxFiles = <?php echo (int)IMAGES_PER_PRODUCT_MAX; ?>;
    const dropzone = document.getElementById('dropzone');
    const btnPick = document.getElementById('btnPickFiles');
    const fileInput = document.getElementById('fileInputHidden');
    const previewList = document.getElementById('previewList');
    const miniPreview = document.getElementById('imagesMiniPreview');
    const btnOpen = document.getElementById('btnOpenImagesModal');
    const imagesModal = new bootstrap.Modal(document.getElementById('imagesModal'));
    
    // Controle de produtos do conjunto
    const typeSelect = document.getElementById('type');
    const setProductsSection = document.getElementById('setProductsSection');
    const setProductCheckboxes = document.querySelectorAll('.set-product-checkbox');
    const setProductQtyInputs = document.querySelectorAll('.set-product-qty');
    
    // Função para mostrar/ocultar seção de produtos do conjunto
    function toggleSetProductsSection() {
        const isSet = typeSelect.value === 'set';
        setProductsSection.style.display = isSet ? 'block' : 'none';
        
        // Habilitar/desabilitar inputs de quantidade baseado no checkbox
        setProductCheckboxes.forEach(checkbox => {
            const productId = checkbox.dataset.productId;
            const qtyInput = document.querySelector(`.set-product-qty[data-product-id="${productId}"]`);
            if (qtyInput) {
                qtyInput.disabled = !isSet || !checkbox.checked;
            }
        });
    }
    
    // Event listener para mudança no tipo
    typeSelect.addEventListener('change', toggleSetProductsSection);
    
    // Event listeners para checkboxes de produtos
    setProductCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const qtyInput = document.querySelector(`.set-product-qty[data-product-id="${productId}"]`);
            if (qtyInput) {
                qtyInput.disabled = !this.checked;
                if (!this.checked) {
                    qtyInput.value = '1';
                }
            }
        });
    });
    
    // Inicializar estado ao carregar
    toggleSetProductsSection();

    const form = document.querySelector('form[action$="adm/produtos/salvar"]');
    // Criar input real multiple para envio com name="images[]"
    let realInput = document.createElement('input');
    realInput.type = 'file';
    realInput.name = 'images[]';
    realInput.accept = 'image/*';
    realInput.multiple = true;
    realInput.className = 'd-none';
    form.appendChild(realInput);

    let filesBuffer = [];
    let primaryIndex = 0; // índice do arquivo principal dentro de filesBuffer

    function refreshMiniPreview(){
        miniPreview.innerHTML = '';
        filesBuffer.forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            const wrap = document.createElement('div');
            wrap.className = 'position-relative';
            const img = document.createElement('img');
            img.src = url;
            img.width = 64; img.height = 64; img.style.objectFit='cover';
            img.className = 'rounded border ' + (idx === 0 ? 'border-primary' : 'border-secondary');
            wrap.appendChild(img);
            if (idx === 0) {
                const badge = document.createElement('span');
                badge.textContent = 'P';
                badge.className = 'position-absolute top-0 start-0 translate-middle badge rounded-pill bg-primary';
                badge.style.left = '10px'; badge.style.top = '10px';
                wrap.appendChild(badge);
            }
            miniPreview.appendChild(wrap);
        });
    }

    function renderPreview(){
        previewList.innerHTML = '';
        filesBuffer.forEach((file, idx) => {
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';
            const card = document.createElement('div');
            card.className = 'card bg-transparent border-secondary';
            const img = document.createElement('img');
            img.className = 'card-img-top';
            img.style.height = '140px';
            img.style.objectFit = 'cover';
            img.alt = file.name;
            img.src = URL.createObjectURL(file);
            const body = document.createElement('div');
            body.className = 'card-body p-2 d-flex justify-content-between align-items-center gap-2';
            const formSwitch = document.createElement('div');
            formSwitch.className = 'form-check form-switch mb-0';
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'form-check-input';
            cb.checked = (idx === 0); // primeiro é principal
            cb.addEventListener('change', () => {
                if (!cb.checked) { cb.checked = true; return; }
                setPrimaryByIndex(idx);
            });
            const label = document.createElement('label');
            label.className = 'form-check-label small';
            label.textContent = 'Principal';
            formSwitch.appendChild(cb);
            formSwitch.appendChild(label);
            const small = document.createElement('small');
            small.textContent = file.name;
            small.className = 'text-truncate';
            const btnDel = document.createElement('button');
            btnDel.type = 'button';
            btnDel.className = 'btn btn-sm btn-outline-danger';
            btnDel.innerHTML = '<i class="bi bi-trash"></i>';
            btnDel.addEventListener('click', () => {
                filesBuffer.splice(idx,1);
                if (primaryIndex === idx) primaryIndex = 0;
                syncRealInput();
                renderPreview();
                refreshMiniPreview();
            });
            const leftSide = document.createElement('div');
            leftSide.className = 'd-flex flex-column';
            leftSide.appendChild(formSwitch);
            leftSide.appendChild(small);
            body.appendChild(leftSide);
            body.appendChild(btnDel);
            card.appendChild(img);
            card.appendChild(body);
            col.appendChild(card);
            previewList.appendChild(col);
        });
    }

    function addFiles(list){
        let arr = Array.from(list);
        let remaining = maxFiles - filesBuffer.length;
        if (remaining <= 0) return;
        arr.slice(0, remaining).forEach(f => {
            if (f.type.startsWith('image/')) filesBuffer.push(f);
        });
        syncRealInput();
        renderPreview();
        refreshMiniPreview();
    }

    function syncRealInput(){
        // Cria um DataTransfer para preencher o input real com os arquivos do buffer
        const dt = new DataTransfer();
        filesBuffer.forEach(f => dt.items.add(f));
        realInput.files = dt.files;
    }

    function setPrimaryByIndex(idx){
        if (idx <= 0 || idx >= filesBuffer.length) { refreshMiniPreview(); renderPreview(); return; }
        // move o arquivo selecionado para a primeira posição
        const [file] = filesBuffer.splice(idx,1);
        filesBuffer.unshift(file);
        primaryIndex = 0;
        syncRealInput();
        renderPreview();
        refreshMiniPreview();
    }

    btnOpen.addEventListener('click', () => imagesModal.show());
    btnPick.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => addFiles(e.target.files));

    ;['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, e => {
        e.preventDefault(); e.stopPropagation();
        dropzone.classList.add('bg-dark');
    }));
    ;['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, e => {
        e.preventDefault(); e.stopPropagation();
        dropzone.classList.remove('bg-dark');
    }));
    dropzone.addEventListener('drop', (e) => {
        addFiles(e.dataTransfer.files);
    });
})();
</script>
<?php $scripts = (isset($scripts) ? $scripts : '') . ob_get_clean(); ?>

</div>
