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
if (isset($productImage)) {
    if (is_numeric($productImage)) {
        // ID do produto - buscar do banco via API
        $imgSrc = 'product-image.php?id=' . (int)$productImage;
    } elseif (filter_var($productImage, FILTER_VALIDATE_URL)) {
        // URL completa externa
        $imgSrc = $productImage;
    } elseif (strpos($productImage, 'assets/') === 0 || strpos($productImage, 'images/') === 0) {
        // Path relativo local
        $imgSrc = $productImage;
    }
}
?>
<div class="navbar-space"></div>
<section class="section produto-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <!-- Galeria de imagens -->
                <div class="product-image-store mb-2">
                    <img id="mainProductImage" 
                         src="<?php echo htmlspecialchars($imgSrc); ?>" 
                         alt="<?php echo htmlspecialchars($productTitle); ?>" 
                         class="img-fluid rounded product-img-store"
                         onerror="this.src='assets/img/placeholder.svg'">
                </div>
                
                <!-- Miniaturas -->
                <div id="productThumbnails" class="d-flex gap-2 overflow-auto pb-2">
                    <!-- Miniaturas serão carregadas via JavaScript -->
                </div>
                
                <script>
                // Carregar todas as imagens do produto
                <?php if (is_numeric($productImage)): ?>
                fetch('product-image.php?id=<?= (int)$productImage ?>&all=1')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.images && data.images.length > 1) {
                            const container = document.getElementById('productThumbnails');
                            const mainImg = document.getElementById('mainProductImage');
                            
                            data.images.forEach((img, index) => {
                                const thumb = document.createElement('img');
                                thumb.src = img.url;
                                thumb.className = 'img-thumbnail' + (index === 0 ? ' border-primary' : '');
                                thumb.style.width = '80px';
                                thumb.style.height = '80px';
                                thumb.style.objectFit = 'cover';
                                thumb.style.cursor = 'pointer';
                                thumb.onclick = function() {
                                    mainImg.src = img.url;
                                    container.querySelectorAll('img').forEach(t => t.classList.remove('border-primary'));
                                    thumb.classList.add('border-primary');
                                };
                                container.appendChild(thumb);
                            });
                        }
                    })
                    .catch(err => console.error('Erro ao carregar imagens:', err));
                <?php endif; ?>
                </script>
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
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <div class="input-group product-qty-group" style="max-width: 200px;">
                            <button class="btn btn-outline-secondary product-qty-dec" type="button" aria-label="Diminuir">-</button>
                            <input type="number" class="form-control text-center product-qty-input" id="quantidade" name="qty" value="1" min="1" required>
                            <button class="btn btn-outline-secondary product-qty-inc" type="button" aria-label="Aumentar">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Comprar</button>
                </form>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="section-title">Produtos Relacionados</h3>
                <div class="row">
                    <!-- Produtos relacionados podem ser inseridos aqui -->
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Adicionar produto ao carrinho via AJAX na página de produto
document.addEventListener('DOMContentLoaded', function() {
    const productForm = document.querySelector('form[action*="cart.php"]');
    
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
            if (productData.qty < 1 || productData.qty > 10) {
                showProductAlert('Quantidade deve estar entre 1 e 10', 'warning');
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
                        window.location.href = 'checkout/carrinho.php';
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
