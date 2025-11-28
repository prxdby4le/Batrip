<?php
// Template para página de produto individual
// Variáveis necessárias: $productTitle, $productPrice, $productImage, $productDescription (opcional)
// $productImage pode ser: ID numérico (busca no banco), URL completa, ou path relativo
require_once __DIR__ . '/auth.php';

// Determinar ação do formulário baseado na localização
$baseForm = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'public') ? '' : '../';
$formAction = $baseForm . 'cart.php';

// Converter preço para float
$priceNumeric = 0.0;
if (isset($productPrice)) {
    $priceSan = str_replace(['R$', ' ', '.'], '', (string)$productPrice);
    $priceSan = str_replace(',', '.', $priceSan);
    $priceNumeric = is_numeric($priceSan) ? (float)$priceSan : 0.0;
}

// Determinar source da imagem de forma mais clara
$imgSrc = 'assets/img/placeholder.svg'; // Fallback padrão
if (isset($productImage) && $productImage !== '') {
    if (is_numeric($productImage)) {
        // ID do produto - buscar do banco via API
        $imgSrc = 'product-image.php?id=' . (int)$productImage;
    } elseif (filter_var($productImage, FILTER_VALIDATE_URL)) {
        // URL completa externa
        $imgSrc = $productImage;
    } else {
        $pi = (string)$productImage;
        // Normalizar barras
        $pi = str_replace('\\', '/', $pi);
        // Se vier com prefixo public/, remover
        if (strpos($pi, 'public/') === 0) {
            $pi = substr($pi, 7);
        }
        // Se já começar com assets/ ou images/ usar direto
        if (strpos($pi, 'assets/') === 0 || strpos($pi, 'images/') === 0) {
            $imgSrc = $pi;
        } else {
            // Se for apenas um arquivo (sem /), presumir assets/img/<arquivo>
            if (strpos($pi, '/') === false) {
                $imgSrc = 'assets/img/' . $pi;
            }
        }
    }
}
?>
<div class="navbar-space"></div>
<section class="section produto-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <!-- Bootstrap Carousel for product images -->
                <div id="productCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" role="region" aria-roledescription="carousel" aria-label="Imagens do produto">
                    <?php if (!empty($productImages) && count($productImages) > 1): ?>
                    <div class="carousel-indicators">
                        <?php foreach ($productImages as $i => $url): ?>
                            <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="carousel-inner">
                        <?php if (!empty($productImages)):
                            foreach ($productImages as $i => $url): 
                                $medUrl = $url . '&size=medium'; ?>
                            <div class="carousel-item<?php echo $i === 0 ? ' active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($medUrl); ?>" class="d-block w-100 img-fluid rounded product-img-store" alt="Imagem <?php echo $i+1; ?> do produto" onerror="this.src='assets/img/placeholder.svg'">
                            </div>
                        <?php endforeach; else: ?>
                            <div class="carousel-item active">
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="d-block w-100 img-fluid rounded product-img-store" alt="Imagem do produto" onerror="this.src='assets/img/placeholder.svg'">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($productImages) && count($productImages) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Próxima</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="product-title mb-2"><?php echo $productTitle; ?></h2>
                <p class="product-price mb-2"><?php echo $productPrice; ?></p>
                <p class="product-desc">
                    <?php echo $productDescription ?? 'Do Opium ao Streetwear, a Batrip explora textura e aspectos musicais em forma de moda. A fusão do mundo punk e rock com as ruas criando uma passarela alternativa e agressiva.'; ?>
                </p>
                <form method="post" action="<?php echo htmlspecialchars($formAction); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo isset($p) ? (int)$p['id'] : 0; ?>">
                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($productTitle); ?>">
                    <input type="hidden" name="price" value="<?php echo htmlspecialchars(number_format($priceNumeric, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="redirect" value="cart">
                    <div class="mb-3">
                        <label for="tamanho" class="form-label">Tamanho</label>
                        <select class="form-select" id="tamanho" name="size" required>
                            <?php
                            $sizes = isset($productSizes) && is_array($productSizes) && count($productSizes) > 0
                                ? $productSizes
                                : ['P','M','G','GG'];
                            foreach ($sizes as $sz):
                                $sz = trim((string)$sz);
                                if ($sz === '') continue;
                            ?>
                                <option value="<?php echo htmlspecialchars($sz); ?>"><?php echo htmlspecialchars($sz); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php
                    // Exibir Tabela/Guia de Tamanhos vinda do Admin, se existir
                    $sizeTableHtml = '';
                    $sizeTableImage = '';
                    $possibleKeys = [
                        'size_table', 'size_table_html', 'size_chart', 'size_chart_html',
                        'tabela_tamanhos', 'guia_tamanhos', 'size_guide', 'sizeGuide',
                        'tabela_medidas', 'tabelaMedidas'
                    ];
                    $possibleImageKeys = ['size_table_image','size_chart_image','tabela_tamanhos_imagem'];
                    // Tentar extrair do array de produto, se existir
                    if (isset($p) && is_array($p)) {
                        foreach ($possibleKeys as $k) { if (!empty($p[$k])) { $sizeTableHtml = (string)$p[$k]; break; } }
                        foreach ($possibleImageKeys as $k) { if (!empty($p[$k])) { $sizeTableImage = (string)$p[$k]; break; } }
                    }
                    // Tentar variáveis soltas definidas pelo chamador
                    foreach (['productSizeTable','sizeTableHtml','sizeChartHtml','tabela_tamanhos'] as $v) {
                        if (!empty($$v)) { $sizeTableHtml = (string)$$v; break; }
                    }
                    foreach (['sizeTableImage','sizeChartImage'] as $v) {
                        if (!empty($$v)) { $sizeTableImage = (string)$$v; break; }
                    }
                    if ($sizeTableHtml || $sizeTableImage):
                        // Sanitização básica para evitar scripts/eventos
                        $clean = $sizeTableHtml;
                        if ($clean) {
                            $clean = preg_replace('/<(script|style)[^>]*>.*?<\\/\1>/is', '', $clean);
                            $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
                            $clean = preg_replace('/javascript\s*:/i', '', $clean);
                        }
                    ?>
                    <div class="mb-3">
                        <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#sizeTableCollapse" aria-expanded="false" aria-controls="sizeTableCollapse">
                            Tabela de tamanhos
                        </button>
                        <div class="collapse mt-2" id="sizeTableCollapse">
                            <div class="card card-body bg-dark border-secondary">
                                <?php if (!empty($productSizeChart)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-dark table-striped align-middle w-auto mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Tamanho</th>
                                                    <th>Peito</th>
                                                    <th>Comprimento</th>
                                                    <th>Ombro</th>
                                                    <th>Manga</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($productSizeChart as $row): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string)($row['size'] ?? '')) ?></td>
                                                        <td><?= htmlspecialchars((string)($row['bust_cm'] ?? '')) ?></td>
                                                        <td><?= htmlspecialchars((string)($row['length_cm'] ?? '')) ?></td>
                                                        <td><?= htmlspecialchars((string)($row['shoulder_cm'] ?? '')) ?></td>
                                                        <td><?= htmlspecialchars((string)($row['sleeve_cm'] ?? '')) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif ($clean): ?>
                                    <div class="size-table-content">
                                        <?php echo $clean; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($sizeTableImage): ?>
                                    <div class="text-center">
                                        <img src="<?php echo htmlspecialchars($sizeTableImage); ?>" alt="Tabela de tamanhos" class="img-fluid">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <div class="input-group product-qty-group" style="max-width: 200px;">
                            <button class="btn btn-outline-secondary product-qty-dec" type="button" aria-label="Diminuir">-</button>
                            <input type="number" class="form-control text-center product-qty-input" id="quantidade" name="qty" value="<?php echo (int)(defined('MIN_CART_QTY') ? MIN_CART_QTY : 1); ?>" min="<?php echo (int)(defined('MIN_CART_QTY') ? MIN_CART_QTY : 1); ?>" max="<?php echo (int)(defined('MAX_CART_QTY') ? MAX_CART_QTY : 10); ?>" required>
                            <button class="btn btn-outline-secondary product-qty-inc" type="button" aria-label="Aumentar">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Comprar</button>
                    <button type="button" id="addToCartOnlyBtn" class="btn btn-outline-light w-100 mt-2">Adicionar ao Carrinho</button>
                </form>
            </div>
        </div>
    </div>

</section>
<script>
window.csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
</script>

<script>
// Adicionar produto ao carrinho via AJAX na página de produto
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.getElementById('addToCartOnlyBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (!form) return;
            const formData = new FormData(form);
            formData.set('action', 'add');
            // AJAX para adicionar ao carrinho sem redirecionar
            fetch('cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || ''
                },
                body: JSON.stringify({
                    action: 'add',
                    id: formData.get('id'),
                    title: formData.get('title'),
                    price: formData.get('price'),
                    size: formData.get('size'),
                    qty: formData.get('qty'),
                    image: '',
                    csrf_token: formData.get('csrf_token')
                })
            })
            .then(async res => {
                if (!res.ok) {
                    const text = await res.text();
                    alert('Erro ao conectar com o servidor. Tente novamente.');
                    console.error('Erro HTTP:', res.status, text);
                    return;
                }
                return res.json();
            })
            .then(data => {
                if (!data) return;
                if (!data.success) {
                    alert(data.message || 'Erro ao adicionar ao carrinho.');
                } else {
                    // Atualizar badge do carrinho se existir
                    const badge = document.querySelector('.cart-badge, .cart-count, #sidebar-cart-count');
                    if (badge && data.cart_count !== undefined) {
                        badge.textContent = data.cart_count;
                    }
                    // Atualizar carrinho lateral via AJAX (usando includes/cart-sidebar.php)
                    fetch('includes/cart-sidebar.php')
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newSidebar = doc.querySelector('.offcanvas-body');
                            const sidebar = document.querySelector('.offcanvas-body');
                            if (sidebar && newSidebar) {
                                sidebar.innerHTML = newSidebar.innerHTML;
                                rebindRemoveCartItemEvents();
                            }
                        });
                    // Abrir o sidebar se não estiver aberto
                    const cartSidebar = document.getElementById('cartSidebar');
                    if (cartSidebar && window.bootstrap && window.bootstrap.Offcanvas) {
                        const oc = window.bootstrap.Offcanvas.getOrCreateInstance(cartSidebar);
                        oc.show();
                    }
                    // Alerta visual
                    if (typeof showAlert === 'function') {
                        showAlert('Produto adicionado ao carrinho!', 'success');
                    } else {
                        alert('Produto adicionado ao carrinho!');
                    }
                // Reanexa eventos de remoção após atualizar o sidebar
                function rebindRemoveCartItemEvents() {
                    document.querySelectorAll('.btn-remove-sidebar').forEach(function(btn) {
                        btn.onclick = null;
                        btn.addEventListener('click', function() {
                            if (!confirm('Remover este item do carrinho?')) return;
                            const productId = parseInt(this.dataset.productId);
                            const productSize = this.dataset.productSize;
                            fetch('cart-handler.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': window.csrfToken || ''
                                },
                                body: JSON.stringify({
                                    action: 'remove',
                                    id: productId,
                                    size: productSize,
                                    csrf_token: window.csrfToken || ''
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    fetch('includes/cart-sidebar.php')
                                        .then(r => r.text())
                                        .then(html => {
                                            const parser = new DOMParser();
                                            const doc = parser.parseFromString(html, 'text/html');
                                            const newSidebar = doc.querySelector('.offcanvas-body');
                                            const sidebar = document.querySelector('.offcanvas-body');
                                            if (sidebar && newSidebar) {
                                                sidebar.innerHTML = newSidebar.innerHTML;
                                                rebindRemoveCartItemEvents();
                                            }
                                        });
                                } else {
                                    alert('Erro ao remover item: ' + (data.message || 'Erro desconhecido'));
                                }
                            })
                            .catch(error => {
                                alert('Erro ao remover item do carrinho');
                            });
                        });
                    });
                }
                }
            })
            .catch(err => {
                alert('Erro ao conectar com o servidor. Tente novamente.');
                console.error('Erro na requisição:', err);
            });
        });
    }
    const productForm = document.querySelector('form[action*="cart.php"]');
    const qtyInput = document.getElementById('quantidade');
    const maxQty = qtyInput ? parseInt(qtyInput.getAttribute('max')) || 10 : 10;
    const minQty = qtyInput ? parseInt(qtyInput.getAttribute('min')) || 1 : 1;
    
    if (productForm) {
        // SEMPRE usar AJAX para melhor UX
        productForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Sempre prevenir submissão padrão
            
            const formData = new FormData(this);
            const csrfToken = formData.get('csrf_token');
            const redirectMode = formData.get('redirect');
            
            const productData = {
                action: 'add',
                id: parseInt(formData.get('id')),
                title: formData.get('title'),
                price: parseFloat(formData.get('price')),
                size: formData.get('size'),
                qty: parseInt(formData.get('qty')),
                csrf_token: csrfToken // Incluir no body
            };
            
            // Validações básicas antes de enviar
            if (!productData.size || productData.size === '') {
                showProductAlert('Por favor, selecione um tamanho', 'warning');
                return;
            }
            if (productData.qty < minQty || productData.qty > maxQty) {
                showProductAlert('Quantidade deve estar entre ' + minQty + ' e ' + maxQty, 'warning');
                return;
            }
            
            // Usar fetch para adicionar ao carrinho
            fetch('cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken // CSRF no header também
                },
                body: JSON.stringify(productData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro HTTP ' + response.status);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    // Atualizar contador do carrinho
                    updateCartCount(result.cart_count);
                    
                    // Se modo redirect é "cart", redirecionar
                    if (redirectMode === 'cart') {
                        setTimeout(function() {
                            window.location.href = 'checkout/carrinho.php';
                        }, 400); // 400ms para garantir persistência da sessão
                        return;
                    }
                    
                    // Caso contrário, mostrar alerta e abrir sidebar
                    showProductAlert('Produto adicionado ao carrinho!', 'success');
                    
                    // Abrir sidebar do carrinho
                    setTimeout(() => {
                        const cartSidebar = document.getElementById('cartSidebar');
                        if (cartSidebar && window.bootstrap && window.bootstrap.Offcanvas) {
                            const oc = window.bootstrap.Offcanvas.getOrCreateInstance(cartSidebar);
                            oc.show();
                        }
                    }, 300);
                } else {
                    showProductAlert(result.message || 'Erro ao adicionar produto', 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showProductAlert('Erro ao conectar com o servidor. Tente novamente.', 'danger');
            });
        });
    }

    // Troca de imagem principal ao clicar em miniaturas
    const mainImg = document.getElementById('mainProductImage');
    // Thumbs à esquerda
    const thumbBtns = Array.from(document.querySelectorAll('.pp-thumb-btn'));
    if (thumbBtns.length) {
        thumbBtns.forEach((btn, idx)=>{
            btn.addEventListener('click', ()=>{
                const src = btn.getAttribute('data-img');
                if (src && mainImg) mainImg.src = src;
                thumbBtns.forEach(b=>b.classList.remove('active'));
                btn.classList.add('active');
                currentIndex = idx;
            });
            if (idx === 0) btn.classList.add('active');
        });
    }

    // Navegação prev/next
    let currentIndex = 0;
    function setIndex(i){
        if (!thumbBtns.length) return;
        currentIndex = (i + thumbBtns.length) % thumbBtns.length;
        const btn = thumbBtns[currentIndex];
        thumbBtns.forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const src = btn.getAttribute('data-img');
        if (src && mainImg) mainImg.src = src;
    }
    document.querySelector('.pp-prev')?.addEventListener('click', ()=> setIndex(currentIndex - 1));
    document.querySelector('.pp-next')?.addEventListener('click', ()=> setIndex(currentIndex + 1));
    
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('#cart-count, #sidebar-cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }
    
    function showProductAlert(message, type = 'info') {
        // Remover alertas existentes
        const existingAlert = document.querySelector('.temp-product-alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} temp-product-alert position-fixed`;
        alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
        
        // Ícones SVG simples
        const icons = {
            success: '<svg class="icon me-2" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" opacity="0.2"/><path d="M9 12l2 2 4-4"/></svg>',
            danger: '<svg class="icon me-2" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" opacity="0.2"/><path d="M15 9l-6 6m0-6l6 6"/></svg>',
            info: '<svg class="icon me-2" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" opacity="0.2"/><path d="M12 16v-4m0-4h.01"/></svg>'
        };
        
        alertDiv.innerHTML = `
            ${icons[type] || icons.info}
            <span>${message}</span>
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remover após 4 segundos
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 4000);
    }
});
</script>

<style>
/* miniaturas (página produto) */
.pp-thumbs { display:flex; flex-direction:column; gap:.5rem; max-height:400px; overflow:auto; }
.pp-thumb-btn {
    padding: 0;
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 6px;
    background: rgba(255,255,255,.03);
    cursor: pointer;
    width: 90px;
    height: 90px;
    aspect-ratio: 1/1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.pp-thumb-btn.active { border-color: rgba(255,215,0,.6); box-shadow: inset 0 0 0 1px rgba(255,215,0,.25); }
.pp-thumb-img {
    width: 100%;
    height: 100%;
    aspect-ratio: 1/1;
    object-fit: cover;
    display: block;
    border-radius: 4px;
    background: #222;
}
.pp-nav { position:absolute; top:50%; transform: translateY(-50%); opacity:.85; }
.pp-prev { left:.5rem; }
.pp-next { right:.5rem; }
.thumb-btn:focus, .pp-thumb-btn:focus { outline: 2px solid #6cf; outline-offset: 2px; }
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
.temp-product-alert {
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
}
.temp-product-alert .icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}
</style>
