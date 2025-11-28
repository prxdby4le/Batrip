<?php
/**
 * View: Products/Index
 * Listagem de produtos
 */

$products = $products ?? [];
$imagesByProduct = $imagesByProduct ?? [];

// Base href para compatibilidade
$baseHref = BASE_URL;
require_once ROOT_PATH . '/includes/icon-helper.php';
?>

<div class="navbar-space"></div>
<!-- Header da Página -->
<section class="page-header" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <h1 class="text-center">Nossos Produtos</h1>
        <p class="text-center text-white">Explore nossa coleção completa</p>
    </div>
</section>

<!-- Produtos -->
<section class="section py-5">
    <div class="container">
        <?php if (!empty($products)): ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <?php
                        $pid = (int)($product['id'] ?? 0);
                        $mediums = $imagesByProduct[$pid] ?? [];
                        $imgCount = count($mediums);
                        if ($imgCount === 0) {
                            // Fallback para imagem principal ou placeholder
                            if (!empty($product['image'])) {
                                $mediums = [BASE_URL . 'product-image.php?id=' . $pid];
                            } else {
                                $mediums = [BASE_URL . 'assets/img/placeholder.svg'];
                            }
                            $imgCount = 1;
                        }
                        ?>
                        <div class="product-card">
                            <div class="product-card-gallery position-relative">
                                <a href="<?= $baseHref ?>produto/<?= (int)$product['id'] ?>" class="product-image-store d-block">
                                    <div id="cardCarousel-<?= $pid ?>" class="carousel slide carousel-fade" data-bs-ride="carousel" aria-label="Imagens do produto">
                                        <div class="carousel-inner">
                                            <?php foreach ($mediums as $ci => $url): ?>
                                                <div class="carousel-item<?= $ci === 0 ? ' active' : '' ?>">
                                                    <img src="<?= htmlspecialchars($url) ?>" class="d-block w-100 index-carousel-img" alt="Imagem <?= $ci+1 ?> do produto" onerror="this.src='<?= $baseHref ?>assets/img/placeholder.svg'">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php if ($imgCount > 1): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($mediums as $ci => $url): ?>
                                        <button type="button" data-bs-target="#cardCarousel-<?= $pid ?>" data-bs-slide-to="<?= $ci ?>" class="<?= $ci === 0 ? 'active' : '' ?>" aria-current="<?= $ci === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $ci+1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#cardCarousel-<?= $pid ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#cardCarousel-<?= $pid ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Próxima</span>
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="p-3">
                                <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                                <?php if (!empty($product['description'])): ?>
                                    <p class="text-muted mb-2"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                                <?php endif; ?>
                                <p class="product-price">R$ <?= number_format((float)$product['price'], 2, ',', '.') ?></p>
                                <div class="d-flex gap-2">
                                    <a href="<?= $baseHref ?>produto/<?= (int)$product['id'] ?>" class="btn btn-custom flex-fill">
                                        <?= icon('eye', 'icon me-1') ?>Ver
                                    </a>
                                    <button type="button" class="btn btn-outline-light" 
                                            onclick="addToCart(<?= (int)$product['id'] ?>, '<?= htmlspecialchars($product['title']) ?>', <?= (float)$product['price'] ?>)">
                                        <?= icon('cart-plus', 'icon') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="empty-state">
                    <div class="empty-icon">
                        <?= icon('box-open', 'icon-5x') ?>
                    </div>
                    <h4>Nenhum produto encontrado</h4>
                    <p>Em breve novos lançamentos!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Config baseHref e funcionalidade do carrinho
const baseHref = (window.BATRIP_CONFIG && window.BATRIP_CONFIG.baseUrl) || '<?= addslashes($baseHref) ?>';

// Funcionalidade do carrinho
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
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            updateCartCount(result.cart_count);
            showAlert('Produto adicionado ao carrinho!', 'success');
            // Abrir sidebar do carrinho
            const cartSidebar = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('cartSidebar'));
            cartSidebar.show();
            // Atualizar conteúdo do sidebar de forma assíncrona
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

// Reanexa eventos de remoção após atualizar o sidebar
function rebindRemoveCartItemEvents() {
    document.querySelectorAll('.btn-remove-sidebar').forEach(function(btn) {
        btn.onclick = null;
        btn.addEventListener('click', function() {
            if (!confirm('Remover este item do carrinho?')) return;
            const productId = parseInt(this.dataset.productId);
            const productSize = this.dataset.productSize;
            fetch('cart/add', {
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
                } else {
                    alert('Erro ao remover item: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao remover item do carrinho');
            });
        });
    });
}
// Inicializa eventos ao carregar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', rebindRemoveCartItemEvents);
} else {
    rebindRemoveCartItemEvents();
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('#cart-count, #sidebar-cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

function showAlert(message, type = 'info') {
    // Remover alertas existentes
    const existingAlert = document.querySelector('.temp-alert');
    if (existingAlert) existingAlert.remove();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} temp-alert position-fixed`;
    alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
    
    const iconSvgs = {
        success: <?= json_encode(icon("check-circle", "icon")) ?>,
        warning: <?= json_encode(icon("exclamation-triangle", "icon")) ?>,
        danger: <?= json_encode(icon("times-circle", "icon")) ?>,
        info: <?= json_encode(icon("info-circle", "icon")) ?>
    };
    
    alertDiv.innerHTML = `
        ${iconSvgs[type] || iconSvgs.info}
        <span class="ms-2">${message}</span>
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
