<?php
/**
 * View: Admin Products Edit
 */

// Garante que config.php foi carregado (define constantes necessárias)
if (!defined('IMAGES_PER_PRODUCT_MAX')) {
    require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
}

$product = $product ?? [];
$images = $images ?? [];
?>

<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>adm/produtos" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="bi bi-pencil me-2"></i>Editar Produto
                </h4>
                
                <form method="POST" action="<?php echo BASE_URL; ?>adm/produtos/<?php echo $product['id']; ?>/atualizar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($product['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" 
                                       value="<?php echo $product['price']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Categoria</label>
                            <select class="form-select" id="category" name="category">
                                <option value="geral" <?php echo ($product['category'] ?? 'geral') === 'geral' ? 'selected' : ''; ?>>Geral</option>
                                <option value="camiseta" <?php echo ($product['category'] ?? '') === 'camiseta' ? 'selected' : ''; ?>>Camiseta</option>
                                <option value="moletom" <?php echo ($product['category'] ?? '') === 'moletom' ? 'selected' : ''; ?>>Moletom</option>
                                <option value="calca" <?php echo ($product['category'] ?? '') === 'calca' ? 'selected' : ''; ?>>Calça</option>
                                <option value="conjunto" <?php echo ($product['category'] ?? '') === 'conjunto' ? 'selected' : ''; ?>>Conjunto</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Galeria de Imagens -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Galeria de imagens</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-light btn-sm" id="btnOpenImagesModal">
                                    <i class="bi bi-plus-circle me-1"></i> Adicionar imagens
                                </button>
                            </div>
                        </div>
                        <div class="row g-3" id="galleryGrid" data-product-id="<?php echo (int)$product['id']; ?>">
                            <?php foreach ($images as $img): ?>
                                <div class="col-6 col-md-4 col-lg-3" draggable="true" data-id="<?php echo (int)$img['id']; ?>">
                                    <div class="card bg-transparent border-secondary h-100">
                                        <div class="position-relative">
                                            <img src="<?php echo htmlspecialchars($img['url']); ?>" class="card-img-top" style="height: 160px; object-fit: cover;" alt="img">
                                            <?php if ((int)$img['is_primary'] === 1): ?>
                                                <span class="badge bg-primary position-absolute top-0 start-0 m-2">Principal</span>
                                            <?php endif; ?>
                                            <span class="position-absolute top-0 end-0 m-2 text-muted" style="cursor:move" title="Arrastar para reordenar">
                                                <i class="bi bi-arrows-move"></i>
                                            </span>
                                        </div>
                                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input set-primary-checkbox" type="checkbox" data-id="<?php echo (int)$img['id']; ?>" <?php echo (int)$img['is_primary'] === 1 ? 'checked' : ''; ?>>
                                                <label class="form-check-label small">Principal</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-image" data-id="<?php echo (int)$img['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Dica: arraste para reordenar. Marque a chave para definir a imagem principal, ou clique na lixeira para remover.</small>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active" name="active" 
                                   <?php echo $product['active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">
                                Produto ativo (visível no site)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                        <a href="<?php echo BASE_URL; ?>adm/produtos" class="btn btn-outline-light">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
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
                        <p class="text-muted small">ou</p>
                        <button type="button" class="btn btn-outline-light" id="btnPickFiles"><i class="bi bi-upload me-1"></i> Selecionar arquivos</button>
                        <input type="file" id="fileInputHidden" accept="image/*" multiple class="d-none">
                </div>
                <div id="previewList" class="mt-3 row g-3"></div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light" id="btnUploadImages">Enviar imagens</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
    </div>

<?php ob_start(); ?>
<script>
(function(){
    const productId = document.getElementById('galleryGrid').dataset.productId;
    const btnOpen = document.getElementById('btnOpenImagesModal');
    const imagesModal = new bootstrap.Modal(document.getElementById('imagesModal'));
    const dropzone = document.getElementById('dropzone');
    const btnPick = document.getElementById('btnPickFiles');
    const fileInput = document.getElementById('fileInputHidden');
    const previewList = document.getElementById('previewList');
    const btnUpload = document.getElementById('btnUploadImages');
    const grid = document.getElementById('galleryGrid');
    const maxFiles = <?php echo (int)IMAGES_PER_PRODUCT_MAX; ?>;

    let filesBuffer = [];

    btnOpen.addEventListener('click', () => imagesModal.show());
    btnPick.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => addFiles(e.target.files));
    ;['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); dropzone.classList.add('bg-dark'); }));
    ;['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('bg-dark'); }));
    dropzone.addEventListener('drop', (e) => addFiles(e.dataTransfer.files));

    function addFiles(list){
        let arr = Array.from(list);
        let current = grid.querySelectorAll('[data-id]').length;
        let remaining = maxFiles - current - filesBuffer.length;
        if (remaining <= 0) return;
        arr.slice(0, remaining).forEach(f => { if (f.type.startsWith('image/')) filesBuffer.push(f); });
        renderPreview();
    }
    function renderPreview(){
        previewList.innerHTML = '';
        filesBuffer.forEach((file, idx) => {
            const col = document.createElement('div'); col.className='col-6 col-md-4 col-lg-3';
            const card = document.createElement('div'); card.className='card bg-transparent border-secondary';
            const img = document.createElement('img'); img.className='card-img-top'; img.style.height='140px'; img.style.objectFit='cover'; img.src = URL.createObjectURL(file);
            const body = document.createElement('div'); body.className='card-body p-2 d-flex justify-content-between align-items-center';
            const small = document.createElement('small'); small.textContent=file.name; small.className='text-truncate';
            const del = document.createElement('button'); del.type='button'; del.className='btn btn-sm btn-outline-danger'; del.innerHTML='<i class="bi bi-trash"></i>';
            del.addEventListener('click', ()=>{ filesBuffer.splice(idx,1); renderPreview(); });
            body.appendChild(small); body.appendChild(del); card.appendChild(img); card.appendChild(body); col.appendChild(card); previewList.appendChild(col);
        });
    }

    btnUpload.addEventListener('click', async () => {
        if (filesBuffer.length === 0) { imagesModal.hide(); return; }
        const fd = new FormData();
        filesBuffer.forEach(f => fd.append('images[]', f));
        const res = await fetch('<?php echo BASE_URL; ?>adm/produtos/' + productId + '/imagens/upload', { method:'POST', body: fd });
        const data = await res.json();
        if (!data.success) { alert(data.error || 'Falha ao enviar imagens'); return; }
        filesBuffer = []; previewList.innerHTML=''; imagesModal.hide();
        renderGallery(data.images);
    });

            function renderGallery(images){
        grid.innerHTML = '';
        images.forEach(img => {
            const col = document.createElement('div'); col.className = 'col-6 col-md-4 col-lg-3'; col.draggable = true; col.dataset.id = img.id;
            const card = document.createElement('div'); card.className = 'card bg-transparent border-secondary h-100';
            const wrap = document.createElement('div'); wrap.className='position-relative';
            const image = document.createElement('img'); image.className='card-img-top'; image.style.height='160px'; image.style.objectFit='cover'; image.src = img.url;
            wrap.appendChild(image);
            if (parseInt(img.is_primary) === 1){
                const badge = document.createElement('span'); badge.className='badge bg-primary position-absolute top-0 start-0 m-2'; badge.textContent='Principal'; wrap.appendChild(badge);
            }
            const handle = document.createElement('span'); handle.className='position-absolute top-0 end-0 m-2 text-muted'; handle.style.cursor='move'; handle.title='Arrastar para reordenar'; handle.innerHTML='<i class="bi bi-arrows-move"></i>';
            wrap.appendChild(handle);
                    const body = document.createElement('div'); body.className='card-body p-2 d-flex justify-content-between align-items-center';
                    const formCheck = document.createElement('div'); formCheck.className='form-check form-switch mb-0';
                    const cb = document.createElement('input'); cb.type='checkbox'; cb.className='form-check-input set-primary-checkbox'; cb.dataset.id = img.id; if (parseInt(img.is_primary)===1) cb.checked = true;
                    cb.addEventListener('change', () => { if (cb.checked) setPrimary(img.id); else cb.checked = true; });
                    const label = document.createElement('label'); label.className='form-check-label small'; label.textContent='Principal';
                    formCheck.appendChild(cb); formCheck.appendChild(label);
                const btnDel = document.createElement('button'); btnDel.type='button'; btnDel.className='btn btn-sm btn-outline-danger'; btnDel.innerHTML='<i class="bi bi-trash"></i>';
            btnDel.addEventListener('click', ()=> deleteImage(img.id));
                    body.appendChild(formCheck); body.appendChild(btnDel);
            card.appendChild(wrap); card.appendChild(body); col.appendChild(card); grid.appendChild(col);
        });
        enableDrag();
                bindPrimaryCheckboxes();
    }

    async function setPrimary(imageId){
        const res = await fetch('<?php echo BASE_URL; ?>adm/produtos/' + productId + '/imagens/' + imageId + '/principal', { method:'POST' });
        const data = await res.json();
        if (!data.success) { alert(data.error || 'Falha ao definir principal'); return; }
        renderGallery(data.images);
    }

    async function deleteImage(imageId){
        if (!confirm('Remover esta imagem?')) return;
        const res = await fetch('<?php echo BASE_URL; ?>adm/produtos/' + productId + '/imagens/' + imageId + '/remover', { method:'POST' });
        const data = await res.json();
        if (!data.success) { alert(data.error || 'Falha ao remover imagem'); return; }
        renderGallery(data.images);
    }

    function enableDrag(){
        let dragSrc = null;
        grid.querySelectorAll('[data-id]').forEach(el => {
            el.addEventListener('dragstart', (e) => { dragSrc = el; e.dataTransfer.effectAllowed='move'; el.classList.add('opacity-50'); });
            el.addEventListener('dragend',   (e) => { el.classList.remove('opacity-50'); });
            el.addEventListener('dragover',  (e) => { e.preventDefault(); e.dataTransfer.dropEffect='move'; });
            el.addEventListener('drop',      async (e) => {
                e.preventDefault();
                if (!dragSrc || dragSrc === el) return;
                const children = Array.from(grid.children);
                const srcIndex = children.indexOf(dragSrc.parentElement ? dragSrc.parentElement : dragSrc);
                const tgtIndex = children.indexOf(el.parentElement ? el.parentElement : el);
                // rearranjo simples: inserir src antes do target
                grid.insertBefore(dragSrc, tgtIndex > srcIndex ? el.nextSibling : el);
                await persistOrder();
            });
        });
    }

            function bindPrimaryCheckboxes(){
                const boxes = grid.querySelectorAll('.set-primary-checkbox');
                boxes.forEach(box => {
                    box.addEventListener('change', () => {
                        if (box.checked) {
                            // desmarca outros e define principal
                            boxes.forEach(other => { if (other !== box) other.checked = false; });
                            setPrimary(box.dataset.id);
                        } else {
                            // não permitir ficar sem principal; reverte
                            box.checked = true;
                        }
                    });
                });
            }

            // Bind nos checkboxes renderizados pelo PHP inicialmente
            bindPrimaryCheckboxes();

    async function persistOrder(){
        const ids = Array.from(grid.querySelectorAll('[data-id]')).map(x => x.dataset.id);
        const fd = new FormData(); ids.forEach(id => fd.append('order[]', id));
        const res = await fetch('<?php echo BASE_URL; ?>adm/produtos/' + productId + '/imagens/reordenar', { method:'POST', body: fd });
        const data = await res.json();
        if (!data.success) { alert(data.error || 'Falha ao reordenar'); }
    }
})();
</script>
<?php $scripts = (isset($scripts) ? $scripts : '') . ob_get_clean(); ?>
