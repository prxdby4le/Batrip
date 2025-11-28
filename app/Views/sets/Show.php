<?php
/**
 * View: Sets/Show
 * Detalhes do conjunto
 */

$set = $set ?? [];
$setItems = $setItems ?? [];
?>

<div class="navbar-space"></div>
<section class="set-detail" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <?php if (!empty($set)): ?>
            <div class="row g-4 align-items-start">
                <div class="col-md-6">
                    <img src="<?= BASE_URL ?>set-image.php?id=<?= (int)$set['id'] ?>&size=large" 
                         alt="<?= htmlspecialchars($set['title']) ?>" 
                         class="img-fluid rounded shadow" 
                         style="object-fit:cover; width:100%; max-height:520px;">
                </div>
                
                <div class="col-md-6">
                    <h1 class="section-title mb-3"><?= htmlspecialchars($set['title']) ?></h1>
                    
                    <?php if (!empty($set['description'])): ?>
                        <div class="mb-4">
                            <p style="color:var(--text-gray); font-size:1.1rem;">
                                <?= nl2br(htmlspecialchars($set['description'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <h2 class="text-primary">R$ <?= number_format((float)($set['price'] ?? 0), 2, ',', '.') ?></h2>
                    </div>
                    
                    <?php if (!empty($setItems)): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">Itens do Conjunto:</h5>
                            <ul class="list-group">
                                <?php foreach ($setItems as $item): ?>
                                    <li class="list-group-item bg-dark text-white border-secondary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                <br>
                                                <small class="text-white">
                                                    Quantidade: <?= (int)$item['quantity'] ?>x | 
                                                    Preço: R$ <?= number_format((float)$item['price'], 2, ',', '.') ?>
                                                </small>
                                                <?php if (!empty($item['sizes'])): ?>
                                                    <br>
                                                    <small class="text-white">Tamanhos: <?= implode(', ', $item['sizes']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-custom btn-lg" onclick="addSetToCart(<?= (int)$set['id'] ?>)">
                            <i class="bi bi-cart-plus me-2"></i>
                            Adicionar Conjunto ao Carrinho
                        </button>
                        <a href="<?= BASE_URL ?>conjuntos" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left me-2"></i>
                            Ver Outros Conjuntos
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <h3>Conjunto não encontrado</h3>
                <p class="text-white">O conjunto que você procura não existe ou foi removido.</p>
                <a href="<?= BASE_URL ?>" class="btn btn-custom mt-3">Voltar para a Home</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
const baseHref = (window.BATRIP_CONFIG && window.BATRIP_CONFIG.baseUrl) || '<?= addslashes(BASE_URL) ?>';

function addSetToCart(setId) {
    if (!setId || setId <= 0) {
        showAlert('ID do conjunto inválido', 'danger');
        return;
    }

    const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    
    const data = {
        id: parseInt(setId),
        qty: 1,
        csrf_token: csrfToken
    };

    // Mostrar feedback visual
    const btn = event?.target?.closest('button') || event?.target;
    if (btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adicionando...';
        
        fetch(baseHref + 'cart/add-set', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Resposta não é JSON:', text.substring(0, 200));
                throw new Error('O servidor retornou uma resposta inválida. Tente novamente.');
            }
            return response.json();
        })
        .then(result => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            if (result.success) {
                updateCartCount(result.cart_count || 0);
                showAlert(result.message || 'Conjunto adicionado ao carrinho!', 'success');
                
                // Abre sidebar do carrinho
                const cartSidebar = document.getElementById('cartSidebar');
                if (cartSidebar && window.bootstrap) {
                    const sidebarInstance = window.bootstrap.Offcanvas.getOrCreateInstance(cartSidebar);
                    sidebarInstance.show();
                    
                    // Atualiza sidebar
                    fetch(baseHref + 'cart/sidebar')
                        .then(r => r.text())
                        .then(html => {
                            const temp = document.createElement('div');
                            temp.innerHTML = html;
                            const novoSidebar = temp.querySelector('#cartSidebar');
                            if (novoSidebar) {
                                const sidebarEl = document.getElementById('cartSidebar');
                                if (sidebarEl) {
                                    sidebarEl.innerHTML = novoSidebar.innerHTML;
                                    // Rebind eventos de remoção
                                    if (typeof rebindRemoveCartItemEvents === 'function') {
                                        rebindRemoveCartItemEvents();
                                    }
                                }
                            }
                        })
                        .catch(err => console.error('Erro ao atualizar sidebar:', err));
                }
            } else {
                if (result.requires_login) {
                    showAlert(result.message || 'Você precisa estar logado para adicionar conjuntos ao carrinho.', 'warning');
                    setTimeout(() => {
                        window.location.href = result.login_url || baseHref + 'login';
                    }, 1500);
                } else {
                    showAlert(result.message || 'Erro ao adicionar conjunto ao carrinho.', 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Erro ao adicionar conjunto:', error);
            btn.disabled = false;
            btn.innerHTML = originalText;
            const errorMsg = error.message || 'Erro ao adicionar conjunto ao carrinho. Tente novamente.';
            showAlert(errorMsg, 'danger');
        });
    }
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('#cart-count, #sidebar-cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

// Função showAlert global (compatível com utils.js ou cria se não existir)
if (typeof showAlert === 'undefined') {
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
}
</script>

