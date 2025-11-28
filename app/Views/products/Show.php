<?php
/**
 * View: Products/Show
 * Detalhes do produto
 */

$product = $product ?? [];
$relatedProducts = $relatedProducts ?? [];
$relatedImagesByProduct = $relatedImagesByProduct ?? [];

// Base href e helper de ícones
$baseHref = BASE_URL;
require_once ROOT_PATH . '/includes/icon-helper.php';
?>

<div class="navbar-space"></div>
<!-- Produto -->
<section class="product-detail" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <?php if (!empty($product)): ?>
            <div class="row">
                <!-- Imagem do Produto -->

                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="product-detail-image">
                        <?php
                        // Usar imagens da galeria se disponíveis
                        $images = $productImages ?? [];
                        if (empty($images)) {
                            // Fallback para imagem principal
                            if (!empty($product['image'])) {
                                $images[] = BASE_URL . 'product-image.php?id=' . $product['id'];
                            } else {
                                $images[] = BASE_URL . 'assets/img/placeholder.svg';
                            }
                        }
                        ?>
                        <div id="productCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover" role="region" aria-roledescription="carousel" aria-label="Imagens do produto">
                            <?php if (count($images) > 1): ?>
                            <div class="carousel-indicators">
                                <?php foreach ($images as $i => $url): ?>
                                    <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i+1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="carousel-inner">
                                <?php foreach ($images as $i => $url): ?>
                                    <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                                        <img src="<?= htmlspecialchars($url) ?>" class="d-block w-100 img-fluid rounded product-img-store" alt="Imagem <?= $i+1 ?> do produto" onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.svg'" style="max-height:600px; object-fit:cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($images) > 1): ?>
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
                </div>
                
                <!-- Informações do Produto -->
                <div class="col-md-6">
                    <h1 class="product-detail-title mb-3">
                        <?php echo htmlspecialchars($product['title']); ?>
                    </h1>
                    
                    <div class="product-detail-price mb-4">
                        <h2 class="text-primary">
                            R$ <?php echo number_format($product['price'], 2, ',', '.'); ?>
                        </h2>
                    </div>
                    
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-detail-description mb-4">
                            <h5>Descrição</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulário de Compra -->
                    <form id="addToCartForm" class="mb-4" method="POST" action="<?= BASE_URL ?>cart/add">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="product_title" value="<?= htmlspecialchars($product['title']) ?>">
                        <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                        
                        <?php if (!empty($sizes)): ?>
                        <div class="mb-3">
                            <label for="size" class="form-label">Tamanho</label>
                            <select class="form-select" id="size" name="size" required>
                                <option value="">Selecione um tamanho</option>
                                <?php foreach ($sizes as $size): ?>
                                    <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <?php
                        // Exibir tabela de medidas
                        if (!empty($sizeTableHtml) || !empty($sizeTableImage)):
                            $clean = $sizeTableHtml;
                            if ($clean) {
                                $clean = preg_replace('/<(script|style)[^>]*>.*?<\/\\1>/is', '', $clean);
                                $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^"]*\'|[^\s>]+)/i', '', $clean);
                                $clean = preg_replace('/javascript\s*:/i', '', $clean);
                            }
                        ?>
                        <div class="mb-3">
                            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#sizeTableCollapse" aria-expanded="false" aria-controls="sizeTableCollapse">
                                <i class="bi bi-ruler me-1"></i>Tabela de medidas
                            </button>
                            <div class="collapse mt-2" id="sizeTableCollapse">
                                <div class="card card-body bg-dark border-secondary">
                                    <?php if (!empty($clean)): ?>
                                        <div class="size-table-content">
                                            <?= $clean ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($sizeTableImage)): ?>
                                        <div class="text-center">
                                            <img src="<?= htmlspecialchars($sizeTableImage) ?>" alt="Tabela de medidas" class="img-fluid">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantidade</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   value="1" min="1" max="10" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-lg">
                                <i class="bi bi-cart-plus me-2"></i>
                                Adicionar ao Carrinho
                            </button>
                            <a href="<?= BASE_URL ?>produtos" class="btn btn-outline-light">
                                <i class="bi bi-arrow-left me-2"></i>
                                Voltar aos Produtos
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Produtos Relacionados -->
            <?php if (!empty($relatedProducts)): ?>
                <section class="related-products mt-5 pt-5 border-top">
                    <h3 class="mb-4">Produtos Relacionados</h3>
                    <div class="row">
                        <?php 
                        foreach ($relatedProducts as $related): 
                            $rpid = (int)($related['id'] ?? 0);
                            $rmediums = $relatedImagesByProduct[$rpid] ?? [];
                            $rimgCount = count($rmediums);
                            if ($rimgCount === 0) {
                                if (!empty($related['image'])) {
                                    $rmediums = [BASE_URL . 'product-image.php?id=' . $rpid];
                                } else {
                                    $rmediums = [BASE_URL . 'assets/img/placeholder.svg'];
                                }
                                $rimgCount = 1;
                            }
                        ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="product-card">
                                    <div class="product-card-gallery position-relative">
                                        <a href="<?= $baseHref ?>produto/<?= (int)$related['id'] ?>" class="product-image-store d-block">
                                            <div id="cardCarousel-related-<?= $rpid ?>" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover" aria-label="Imagens do produto">
                                                <?php if ($rimgCount > 1): ?>
                                                <div class="carousel-indicators">
                                                    <?php foreach ($rmediums as $rci => $rurl): ?>
                                                        <button type="button" data-bs-target="#cardCarousel-related-<?= $rpid ?>" data-bs-slide-to="<?= $rci ?>" class="<?= $rci === 0 ? 'active' : '' ?>" aria-current="<?= $rci === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $rci+1 ?>"></button>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                                <div class="carousel-inner">
                                                    <?php foreach ($rmediums as $rci => $rurl): ?>
                                                        <div class="carousel-item<?= $rci === 0 ? ' active' : '' ?>">
                                                            <img src="<?= htmlspecialchars($rurl) ?>" class="d-block w-100 index-carousel-img" alt="Imagem <?= $rci+1 ?> do produto" onerror="this.src='<?= $baseHref ?>assets/img/placeholder.svg'">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php if ($rimgCount > 1): ?>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#cardCarousel-related-<?= $rpid ?>" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Anterior</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#cardCarousel-related-<?= $rpid ?>" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Próxima</span>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="product-title"><?= htmlspecialchars($related['title']) ?></h3>
                                        <?php if (!empty($related['description'])): ?>
                                            <p class="text-muted mb-2"><?= htmlspecialchars(substr($related['description'], 0, 80)) ?>...</p>
                                        <?php endif; ?>
                                        <p class="product-price">R$ <?= number_format((float)$related['price'], 2, ',', '.') ?></p>
                                        <div class="d-flex gap-2">
                                            <a href="<?= $baseHref ?>produto/<?= (int)$related['id'] ?>" class="btn btn-custom flex-fill">
                                                <?= icon('eye', 'icon me-1') ?>Ver
                                            </a>
                                            <button type="button" class="btn btn-outline-light" 
                                                    onclick="addToCart(<?= (int)$related['id'] ?>, '<?= htmlspecialchars($related['title']) ?>', <?= (float)$related['price'] ?>)">
                                                <?= icon('cart-plus', 'icon') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <h3>Produto não encontrado</h3>
                <p class="text-white">O produto que você procura não existe ou foi removido.</p>
                <a href="<?php echo BASE_URL; ?>produtos" class="btn btn-custom mt-3">
                    Ver Todos os Produtos
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
const baseHref = (window.BATRIP_CONFIG && window.BATRIP_CONFIG.baseUrl) || '<?= addslashes(BASE_URL) ?>';

// Funcionalidade do carrinho para produtos relacionados
function addToCart(productId, title, price, size = 'M') {
    const data = {
        action: 'add',
        id: productId,
        title: title,
        price: price,
        size: size,
        qty: 1
    };
    const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || (window.CSRF_TOKEN || '');
    fetch(baseHref + 'cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            updateCartCount(result.cart_count);
            showAlert('Produto adicionado ao carrinho!', 'success');
            const cartSidebar = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('cartSidebar'));
            cartSidebar.show();
            fetch(baseHref + 'cart/sidebar')
                .then(r => r.text())
                .then(html => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    const novoSidebar = temp.querySelector('#cartSidebar');
                    if (novoSidebar) {
                        document.getElementById('cartSidebar').innerHTML = novoSidebar.innerHTML;
                        rebindRemoveCartItemEvents();
                    }
                });
        } else {
            showAlert(result.message || 'Erro ao adicionar produto', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Erro ao adicionar produto', 'danger');
    });
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('#cart-count, #sidebar-cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

function showAlert(message, type = 'info') {
    const existingAlert = document.querySelector('.temp-alert');
    if (existingAlert) existingAlert.remove();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} temp-alert position-fixed`;
    alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
    alertDiv.innerHTML = `
        <span class="ms-2">${message}</span>
        <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 4000);
}

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
                    'X-CSRF-Token': window.CSRF_TOKEN || ''
                },
                body: JSON.stringify({
                    action: 'remove',
                    id: productId,
                    size: productSize,
                    csrf_token: window.CSRF_TOKEN || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetch(baseHref + 'cart/sidebar')
                        .then(r => r.text())
                        .then(html => {
                            const temp = document.createElement('div');
                            temp.innerHTML = html;
                            const novoSidebar = temp.querySelector('#cartSidebar');
                            if (novoSidebar) {
                                document.getElementById('cartSidebar').innerHTML = novoSidebar.innerHTML;
                                updateCartCount(data.cart_count || 0);
                                rebindRemoveCartItemEvents();
                            }
                        });
                }
            });
        });
    });
}

// Formulário de adicionar ao carrinho na página do produto
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const sizeEl = document.getElementById('size');
    const size = sizeEl ? sizeEl.value : 'M';
    const qty = parseInt(document.getElementById('quantity').value || '1', 10);
    const id = parseInt(this.querySelector('input[name="product_id"]').value, 10);
    const title = this.querySelector('input[name="product_title"]').value;
    const price = parseFloat(this.querySelector('input[name="product_price"]').value);

    if (!size && sizeEl) {
        alert('Por favor, selecione um tamanho');
        return;
    }

    fetch(baseHref + 'cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ id, size, qty, title, price })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showAlert('Produto adicionado ao carrinho!', 'success');
            const cartSidebarEl = document.getElementById('cartSidebar');
            if (cartSidebarEl && window.bootstrap && bootstrap.Offcanvas) {
                const cartSidebar = bootstrap.Offcanvas.getOrCreateInstance(cartSidebarEl);
                cartSidebar.show();
                fetch(baseHref + 'cart/sidebar')
                    .then(r => r.text())
                    .then(html => {
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const novoSidebar = temp.querySelector('#cartSidebar');
                        if (novoSidebar) {
                            document.getElementById('cartSidebar').innerHTML = novoSidebar.innerHTML;
                            rebindRemoveCartItemEvents();
                        }
                    });
            } else {
                alert('Produto adicionado ao carrinho!');
            }
        } else {
            showAlert(data.message || 'Erro ao adicionar produto', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showAlert('Erro ao adicionar produto ao carrinho', 'danger');
    });
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
.temp-alert {
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
</style>
