<?php
// Buffer cedo para evitar 'headers already sent' durante validações/redirecionamentos
if (function_exists('ob_get_level') && ob_get_level() === 0) { ob_start(); }

$pageTitle = 'Admin • Produto';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/icon-helper.php';
require_admin();

require_once __DIR__ . '/../../../includes/head.php';
$baseHref = $baseHref ?? '/';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = [
  'title' => '', 'description' => '', 'price' => '0.00', 'image' => '', 'sizes' => 'P,M,G,GG', 'active' => 1
];
$sizeChart = [];
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
  if ($row) { 
    $product = $row; 
    if (!empty($row['size_chart'])) {
      $decoded = json_decode((string)$row['size_chart'], true);
      if (is_array($decoded)) { $sizeChart = $decoded; }
    }
  }
}

// Carregar imagens extras (se houver)
$imagesExtra = [];
if ($id > 0) {
    try {
        $s = $pdo->prepare('SELECT url FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, position ASC, id ASC');
        $s->execute([$id]);
        $imagesExtra = $s->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $imagesExtra = [];
    }
}
if (empty($imagesExtra) && !empty($product['image'])) {
    $imagesExtra = [ (string)$product['image'] ];
}
?>
<body>
<?php include '../../../includes/nav.php'; ?>
<div class="navbar-space"></div>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0"><?= $id ? 'Editar' : 'Novo' ?> Produto</h1>
  <a href="<?= $baseHref ?>adm/products/index.php" class="btn btn-sm btn-outline-light">Voltar</a>
  </div>
  <form method="post" action="<?= $baseHref ?>adm/products/save.php" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
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
      <label class="form-label">Imagens extras</label>
      <div class="mb-2 small text-muted">Arraste para reordenar. A primeira é a principal. Use URLs relativas (ex.: <code>assets/img/produto-1.jpg</code>) ou use a janela para enviar arquivos.</div>
      <div class="mb-2">
        <button type="button" class="btn btn-outline-light btn-sm" id="btnOpenImagesModalLegacy">
          <i class="bi bi-images me-1"></i> Adicionar imagens (janela)
        </button>
      </div>
      <div id="imagesList" class="d-flex flex-wrap gap-2 mb-2">
        <?php foreach ($imagesExtra as $iUrl): 
          $raw = (string)$iUrl;
          $u = htmlspecialchars($raw);
          $srcResolved = '';
          if (preg_match('~^https?://~i', $raw)) {
            $srcResolved = $raw; // absolute URL
          } else {
            $trim = ltrim($raw, '/');
            if (strpos($trim, 'assets/') === 0 || strpos($trim, 'images/') === 0) {
              $srcResolved = ($baseHref ?? '/') . $trim; // relative from base
            } else {
              $srcResolved = ($baseHref ?? '/') . 'assets/img/uploads/' . basename($trim);
            }
          }
        ?>
          <div class="img-chip" draggable="true" data-url="<?= $u ?>">
            <img src="<?= htmlspecialchars($srcResolved) ?>" alt="thumb" />
            <span class="chip-text" title="<?= $u ?>"><?= $u ?></span>
            <div class="form-check form-switch mb-0 ms-1 me-auto">
              <input class="form-check-input chip-primary-switch" type="checkbox" title="Principal">
            </div>
            <button type="button" class="btn-close btn-close-white btn-remove" aria-label="Remover"></button>
          </div>
        <?php endforeach; ?>
      </div>
      <textarea name="images_extra" id="imagesExtraTextarea" class="form-control" rows="4" placeholder="Uma URL por linha" style="white-space: pre;"><?= htmlspecialchars(implode("\n", $imagesExtra)) ?></textarea>

      <!-- Pré-visualização: Carrossel com miniaturas à esquerda -->
      <div class="card bg-transparent border-secondary mt-3" id="galleryCard" style="display:none;">
        <div class="card-header border-secondary py-2">
          <strong>Pré-visualização</strong> <span class="text-muted small">(todas as imagens do produto)</span>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-start">
            <div class="col-12 col-md-3">
              <div id="galleryThumbs" class="d-flex flex-md-column flex-row flex-wrap gap-2 gallery-thumbs"></div>
            </div>
            <div class="col-12 col-md-9">
              <div class="gallery-main position-relative">
                <img id="galleryMainImg" src="" alt="preview" class="img-fluid rounded border border-secondary" style="max-height:480px; object-fit:contain; background:#111;">
                <button type="button" id="galleryPrev" class="btn btn-sm btn-outline-light gallery-nav prev" title="Anterior">&#8249;</button>
                <button type="button" id="galleryNext" class="btn btn-sm btn-outline-light gallery-nav next" title="Próxima">&#8250;</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <label class="form-label">Tamanhos (separados por vírgula)</label>
      <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars($product['sizes']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Tabela de Medidas (cm) — opcional</label>
      <div class="form-text">Defina medidas por tamanho. Colunas sugeridas: Peito, Comprimento, Ombro, Manga (em centímetros).</div>
      <div class="table-responsive">
        <table class="table table-dark table-striped align-middle" id="sizeChartTable">
          <thead>
            <tr>
              <th style="width:120px;">Tamanho</th>
              <th>Peito (cm)</th>
              <th>Comprimento (cm)</th>
              <th>Ombro (cm)</th>
              <th>Manga (cm)</th>
              <th style="width:50px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $presetSizes = array_filter(array_map('trim', explode(',', (string)($product['sizes'] ?? 'P,M,G,GG'))));
              $rows = [];
              if (!empty($sizeChart)) {
                foreach ($sizeChart as $row) { if (!empty($row['size'])) { $rows[] = $row; } }
              } else {
                foreach ($presetSizes as $sz) { $rows[] = ['size'=>$sz, 'bust_cm'=>'', 'length_cm'=>'', 'shoulder_cm'=>'', 'sleeve_cm'=>'']; }
              }
              foreach ($rows as $r):
                $sz = htmlspecialchars((string)($r['size'] ?? ''));
                $b = htmlspecialchars((string)($r['bust_cm'] ?? ''));
                $l = htmlspecialchars((string)($r['length_cm'] ?? ''));
                $sh = htmlspecialchars((string)($r['shoulder_cm'] ?? ''));
                $sl = htmlspecialchars((string)($r['sleeve_cm'] ?? ''));
            ?>
            <tr>
              <td><input type="text" class="form-control form-control-sm sc-size" value="<?= $sz ?>" placeholder="P"></td>
              <td><input type="number" step="0.1" class="form-control form-control-sm sc-bust" value="<?= $b ?>" placeholder="peito"></td>
              <td><input type="number" step="0.1" class="form-control form-control-sm sc-length" value="<?= $l ?>" placeholder="compr."></td>
              <td><input type="number" step="0.1" class="form-control form-control-sm sc-shoulder" value="<?= $sh ?>" placeholder="ombro"></td>
              <td><input type="number" step="0.1" class="form-control form-control-sm sc-sleeve" value="<?= $sl ?>" placeholder="manga"></td>
              <td><button type="button" class="btn btn-sm btn-outline-danger sc-remove" title="Remover">&times;</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex gap-2 mb-2">
        <button type="button" id="scAddRow" class="btn btn-outline-light btn-sm">Adicionar linha</button>
        <button type="button" id="scFromSizes" class="btn btn-outline-light btn-sm" title="Popular pelos tamanhos acima">Popular pelos tamanhos</button>
      </div>
      <textarea name="size_chart" id="sizeChartField" class="form-control" rows="3" placeholder='[{"size":"M","bust_cm":56,"length_cm":72,"shoulder_cm":48,"sleeve_cm":22}]' style="white-space: pre;"></textarea>
      <div class="form-text">O campo acima é preenchido automaticamente ao salvar, mas você pode editar manualmente em JSON válido.</div>
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
<!-- Modal de upload de imagens (janela flutuante) -->
<div class="modal fade" id="legacyImagesModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="bi bi-images me-2"></i>Adicionar imagens</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="dzLegacy" class="p-4 border border-secondary rounded text-center" style="border-style:dashed !important;">
          <p class="mb-2">Solte as imagens aqui</p>
          <p class="text-muted small">ou</p>
          <button type="button" class="btn btn-outline-light" id="pickLegacy"><i class="bi bi-upload me-1"></i> Selecionar arquivos</button>
          <input type="file" id="fileLegacy" accept="image/*" multiple class="d-none">
        </div>
        <div id="previewLegacy" class="mt-3 row g-3"></div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-light" id="uploadLegacy">Enviar imagens</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
<script>
// Admin: sortable chips + upload para imagens extras
(function(){
  const list = document.getElementById('imagesList');
  const textarea = document.getElementById('imagesExtraTextarea');
  if(!list || !textarea) return;
  // Gallery preview elements
  const galleryCard = document.getElementById('galleryCard');
  const galleryThumbs = document.getElementById('galleryThumbs');
  const galleryMainImg = document.getElementById('galleryMainImg');
  const galleryPrev = document.getElementById('galleryPrev');
  const galleryNext = document.getElementById('galleryNext');
  let galleryIndex = 0;

  function resolveSrc(original){
    const o = (original||'').trim();
    if (/^https?:\/\//i.test(o)) return o;
    // Se já começa com assets/img/uploads, retorna caminho absoluto
    if (o.startsWith('assets/img/uploads/')) return '/' + o.replace(/^\/+/, '');
    let rel = o.replace(/^\//,'');
    const bf = (window.BATRIP_CONFIG?.baseHref || '');
    if (rel.startsWith('assets/') || rel.startsWith('images/')) return '/' + rel;
    return '/assets/img/uploads/' + rel.split('/').pop();
  }

  function serialize(){
    const urls = Array.from(list.querySelectorAll('.img-chip')).map(el => el.getAttribute('data-url')).filter(Boolean);
    textarea.value = urls.join('\n');
    markPrimary();
    renderGallery(urls);
  }
  function markPrimary(){
    list.querySelectorAll('.img-chip').forEach(ch => {
      ch.classList.remove('principal');
      const sw = ch.querySelector('.chip-primary-switch');
      if (sw) sw.checked = false;
      const pb = ch.querySelector('.primary-badge');
      if (pb) pb.remove();
    });
    const first = list.querySelector('.img-chip');
    if (first) {
      first.classList.add('principal');
      const sw = first.querySelector('.chip-primary-switch');
      if (sw) sw.checked = true;
      const badge = document.createElement('span');
      badge.className = 'primary-badge';
      badge.textContent = 'Principal';
      first.appendChild(badge);
    }
  }
  function makeChip(url){
    // Validate basic image URL or filename with extension
    const validExt = /(\.png|\.jpe?g|\.gif|\.webp|\.svg)$/i;
    if (!validExt.test(url.trim())) {
      alert('Informe uma URL de imagem válida (terminando em .png, .jpg, .jpeg, .gif, .webp ou .svg). Dica: use o botão Enviar para fazer upload.');
      return null;
    }
    const div = document.createElement('div');
    div.className = 'img-chip';
    div.setAttribute('draggable','true');
    div.setAttribute('data-url', url);
    const thumb = document.createElement('img');
    const original = url.trim();
    const isAbs = /^https?:\/\//i.test(original);
    if (isAbs) {
      thumb.src = original;
    } else {
      let rel = original.replace(/^\//,'');
      const bf = (window.BATRIP_CONFIG?.baseHref || '<?= addslashes($baseHref) ?>');
      if (rel.startsWith('assets/') || rel.startsWith('images/')) {
        thumb.src = bf + rel;
      } else {
        thumb.src = bf + 'assets/img/uploads/' + rel.split('/').pop();
      }
    }
    // fallback: if first src fails, try uploads/basename
    thumb.onerror = function(){
      const b = original.split('/').pop();
  const candidate = (window.BATRIP_CONFIG?.baseHref || '<?= addslashes($baseHref) ?>') + 'assets/img/uploads/' + b;
      if (thumb.src !== window.location.origin + candidate && thumb.src !== candidate) {
        thumb.src = candidate;
      }
    };
    thumb.alt = 'thumb';
    const span = document.createElement('span');
    span.className = 'chip-text';
    span.title = url;
    span.textContent = url;
    const switchWrap = document.createElement('div');
    switchWrap.className = 'form-check form-switch mb-0 ms-1 me-auto';
    const sw = document.createElement('input');
    sw.type = 'checkbox';
    sw.className = 'form-check-input chip-primary-switch';
    sw.title = 'Principal';
    sw.addEventListener('change', ()=>{
      if (!sw.checked) { sw.checked = true; return; }
      const parent = div.parentElement;
      if (parent) parent.insertBefore(div, parent.firstChild);
      serialize();
    });
    switchWrap.appendChild(sw);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close btn-close-white btn-remove';
    btn.setAttribute('aria-label','Remover');
    btn.addEventListener('click', ()=>{ div.remove(); serialize(); });
    div.append(thumb, span, switchWrap, btn);
    attachDnD(div);
    return div;
  }
  function attachDnD(el){
    el.addEventListener('dragstart', ev=>{ ev.dataTransfer.setData('text/plain', 'drag'); el.classList.add('dragging'); });
    el.addEventListener('dragend', ()=>{ el.classList.remove('dragging'); serialize(); });
  }
  list.addEventListener('dragover', ev=>{
    ev.preventDefault();
    const dragging = list.querySelector('.dragging');
    if(!dragging) return;
    const after = Array.from(list.querySelectorAll('.img-chip:not(.dragging)')).find(child => {
      const box = child.getBoundingClientRect();
      return ev.clientY < box.top + box.height/2;
    });
    if(after){ list.insertBefore(dragging, after); }
    else { list.appendChild(dragging); }
  });
  list.querySelectorAll('.img-chip .btn-remove').forEach(btn=>{
    btn.addEventListener('click', (e)=>{ e.currentTarget.closest('.img-chip').remove(); serialize(); });
  });
  list.querySelectorAll('.img-chip .chip-primary-switch').forEach(sw=>{
    sw.addEventListener('change', (e)=>{
      const chip = e.currentTarget.closest('.img-chip');
      if (!chip) return;
      if (!sw.checked) { sw.checked = true; return; }
      const parent = chip.parentElement;
      if (parent) parent.insertBefore(chip, parent.firstChild);
      serialize();
    });
  });
  list.querySelectorAll('.img-chip').forEach(attachDnD);
  markPrimary();
  // expõe helpers para o modal reutilizar
  window.makeLegacyChip = makeChip;
  window.serializeLegacyChips = serialize;

  function renderGallery(urls){
    if (!galleryCard || !galleryThumbs || !galleryMainImg) return;
    const clean = (Array.isArray(urls) ? urls : [] ).map(u => u && u.trim()).filter(Boolean);
    if (clean.length === 0) {
      galleryCard.style.display = 'none';
      galleryThumbs.innerHTML = '';
      galleryMainImg.src = '';
      galleryIndex = 0;
      return;
    }
    galleryCard.style.display = '';
    galleryThumbs.innerHTML = '';
    clean.forEach((u, idx)=>{
      const img = document.createElement('img');
      img.src = resolveSrc(u);
      img.alt = 'thumb ' + (idx+1);
      img.className = 'gallery-thumb-img';
      img.addEventListener('click', ()=> setActive(idx));
      const wrap = document.createElement('div');
      wrap.className = 'gallery-thumb-wrap';
      wrap.appendChild(img);
      galleryThumbs.appendChild(wrap);
    });
    function setActive(i){
      galleryIndex = (i+clean.length) % clean.length;
      galleryMainImg.src = resolveSrc(clean[galleryIndex]);
      // highlight
      Array.from(galleryThumbs.children).forEach((w, j)=>{
        if (j === galleryIndex) w.classList.add('active');
        else w.classList.remove('active');
      });
    }
    // Expose for navigation
    window.__gallerySetActive = setActive;
    setActive(Math.min(galleryIndex, clean.length-1));
    // Navigation
    if (galleryPrev) {
      galleryPrev.onclick = ()=> setActive(galleryIndex - 1);
    }
    if (galleryNext) {
      galleryNext.onclick = ()=> setActive(galleryIndex + 1);
    }
  }
  // initial
  renderGallery(Array.from(list.querySelectorAll('.img-chip')).map(el => el.getAttribute('data-url')).filter(Boolean));
})();

// Modal de upload (drag&drop) integrando com upload-image.php
(function(){
  const openBtn = document.getElementById('btnOpenImagesModalLegacy');
  const modalEl = document.getElementById('legacyImagesModal');
  if (!openBtn || !modalEl) return;
  const modal = new bootstrap.Modal(modalEl);
  const dz = document.getElementById('dzLegacy');
  const pick = document.getElementById('pickLegacy');
  const file = document.getElementById('fileLegacy');
  const preview = document.getElementById('previewLegacy');
  const upload = document.getElementById('uploadLegacy');
  const list = document.getElementById('imagesList');
  const textarea = document.getElementById('imagesExtraTextarea');

  const maxFiles = 12;
  let buffer = [];

  openBtn.addEventListener('click', ()=> modal.show());
  pick.addEventListener('click', ()=> file.click());
  file.addEventListener('change', e => addFiles(e.target.files));
  ;['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, e=>{ e.preventDefault(); dz.classList.add('bg-dark'); }));
  ;['dragleave','drop'].forEach(ev => dz.addEventListener(ev, e=>{ e.preventDefault(); dz.classList.remove('bg-dark'); }));
  dz.addEventListener('drop', e => addFiles(e.dataTransfer.files));

  function addFiles(listFiles){
    let arr = Array.from(listFiles);
    let remaining = maxFiles - buffer.length;
    if (remaining <= 0) return;
    arr.slice(0, remaining).forEach(f => { if (f.type.startsWith('image/')) buffer.push(f); });
    render();
  }
  function render(){
    preview.innerHTML = '';
    buffer.forEach((f, idx) => {
      const col = document.createElement('div'); col.className='col-6 col-md-4 col-lg-3';
      const card = document.createElement('div'); card.className='card bg-transparent border-secondary';
      const img = document.createElement('img'); img.className='card-img-top'; img.style.height='140px'; img.style.objectFit='cover'; img.src = URL.createObjectURL(f);
      const body = document.createElement('div'); body.className='card-body p-2 d-flex justify-content-between align-items-center';
      const small = document.createElement('small'); small.textContent = f.name; small.className='text-truncate';
      const del = document.createElement('button'); del.type='button'; del.className='btn btn-sm btn-outline-danger'; del.innerHTML='<i class="bi bi-trash"></i>';
      del.addEventListener('click', ()=>{ buffer.splice(idx,1); render(); });
      body.appendChild(small); body.appendChild(del);
      card.appendChild(img); card.appendChild(body); col.appendChild(card); preview.appendChild(col);
    });
  }

  upload.addEventListener('click', async ()=>{
    if (buffer.length === 0) { modal.hide(); return; }
    upload.disabled = true; upload.textContent = 'Enviando...';
    try {
      const fd = new FormData();
      buffer.forEach(f => fd.append('files[]', f));
      fd.append('csrf_token', '<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>');
      <?php if ($id > 0): ?>
      fd.append('product_id', '<?= (int)$id ?>');
      <?php endif; ?>
      const resp = await fetch('/adm/products/upload-image.php', { method:'POST', body: fd });
      const data = await resp.json();
      if (!data.success) { alert('Falha no upload: ' + (data.message || (data.errors||[]).join('\n'))); return; }
      if (Array.isArray(data.files)) {
        data.files.forEach(url => { const chip = (window.makeLegacyChip? window.makeLegacyChip(url) : null); if (chip) list.appendChild(chip); });
      }
      if (textarea) {
        const urls = Array.from(list.querySelectorAll('.img-chip')).map(el => el.getAttribute('data-url')).filter(Boolean);
        textarea.value = urls.join('\n');
      }
      buffer = []; render(); modal.hide();
    } catch (e) {
      console.error(e); alert('Erro ao enviar imagens.');
    } finally {
      upload.disabled = false; upload.textContent = 'Enviar imagens';
    }
  });
})();

// Size chart editor
(function(){
  const table = document.getElementById('sizeChartTable');
  const field = document.getElementById('sizeChartField');
  const addBtn = document.getElementById('scAddRow');
  const fromSizesBtn = document.getElementById('scFromSizes');
  if (!table || !field) return;

  function serialize(){
    const rows = [];
    table.querySelectorAll('tbody tr').forEach(tr => {
      const size = tr.querySelector('.sc-size')?.value.trim() || '';
      const bust = parseFloat(tr.querySelector('.sc-bust')?.value.replace(',', '.') || '') || '';
      const length = parseFloat(tr.querySelector('.sc-length')?.value.replace(',', '.') || '') || '';
      const shoulder = parseFloat(tr.querySelector('.sc-shoulder')?.value.replace(',', '.') || '') || '';
      const sleeve = parseFloat(tr.querySelector('.sc-sleeve')?.value.replace(',', '.') || '') || '';
      if (size){ rows.push({ size, bust_cm: bust, length_cm: length, shoulder_cm: shoulder, sleeve_cm: sleeve }); }
    });
    field.value = JSON.stringify(rows, null, 2);
  }
  function bindRow(tr){
    tr.querySelectorAll('input').forEach(inp => inp.addEventListener('input', serialize));
    tr.querySelector('.sc-remove')?.addEventListener('click', ()=>{ tr.remove(); serialize(); });
  }
  // bind existing
  table.querySelectorAll('tbody tr').forEach(bindRow);
  serialize();

  addBtn?.addEventListener('click', ()=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text" class="form-control form-control-sm sc-size" placeholder="P"></td>
      <td><input type="number" step="0.1" class="form-control form-control-sm sc-bust" placeholder="peito"></td>
      <td><input type="number" step="0.1" class="form-control form-control-sm sc-length" placeholder="compr."></td>
      <td><input type="number" step="0.1" class="form-control form-control-sm sc-shoulder" placeholder="ombro"></td>
      <td><input type="number" step="0.1" class="form-control form-control-sm sc-sleeve" placeholder="manga"></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger sc-remove" title="Remover">&times;</button></td>`;
    table.querySelector('tbody').appendChild(tr);
    bindRow(tr);
    serialize();
  });

  fromSizesBtn?.addEventListener('click', ()=>{
    const sizesInput = document.querySelector('input[name="sizes"]');
    const preset = (sizesInput?.value || 'P,M,G,GG').split(',').map(s=>s.trim()).filter(Boolean);
    const tbody = table.querySelector('tbody');
    tbody.innerHTML='';
    preset.forEach(sz => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="text" class="form-control form-control-sm sc-size" value="${sz}"></td>
        <td><input type="number" step="0.1" class="form-control form-control-sm sc-bust"></td>
        <td><input type="number" step="0.1" class="form-control form-control-sm sc-length"></td>
        <td><input type="number" step="0.1" class="form-control form-control-sm sc-shoulder"></td>
        <td><input type="number" step="0.1" class="form-control form-control-sm sc-sleeve"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger sc-remove" title="Remover">&times;</button></td>`;
      tbody.appendChild(tr);
      bindRow(tr);
    });
    serialize();
  });
})();
</script>
<style>
.img-chip{ display:inline-flex; align-items:center; gap:.5rem; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.15); padding:.25rem .5rem; border-radius:8px; cursor:grab; }
.img-chip.dragging{ opacity:.7; }
.img-chip img{ width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid rgba(255,255,255,.2); }
.img-chip .chip-text{ max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.85rem; }
.img-chip .primary-badge{ margin-left:.25rem; padding:.125rem .375rem; font-size:.7rem; border:1px solid rgba(255,255,255,.25); border-radius:6px; background:rgba(255,255,255,.08); }
.img-chip.principal{ border-color: rgba(255,215,0,.6); box-shadow: 0 0 0 1px rgba(255,215,0,.25) inset; }
.img-chip .btn-remove{ margin-left:.25rem; }
/* size chart */
#sizeChartTable input{ min-width: 90px; }
/* Gallery preview */
.gallery-main{ min-height: 320px; }
.gallery-nav{ position:absolute; top:50%; transform: translateY(-50%); opacity:.8; }
.gallery-nav.prev{ left: .5rem; }
.gallery-nav.next{ right: .5rem; }
.gallery-thumbs{ max-height: 480px; overflow:auto; }
.gallery-thumb-wrap{ border:1px solid rgba(255,255,255,.2); border-radius:6px; padding:2px; background:rgba(255,255,255,.03); cursor:pointer; }
.gallery-thumb-wrap.active{ border-color: rgba(255,215,0,.6); box-shadow: 0 0 0 1px rgba(255,215,0,.25) inset; }
.gallery-thumb-img{ width: 72px; height:72px; object-fit: cover; display:block; border-radius:4px; }
</style>
</body>
</html>
