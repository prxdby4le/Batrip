<?php
/**
 * View: Sets/Index
 * Lista de conjuntos
 */

$sets = $sets ?? [];
?>

<div class="navbar-space"></div>
<section class="sets-page" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <h1 class="section-title mb-4">Conjuntos</h1>
        
        <?php if (!empty($sets)): ?>
            <div class="row">
                <?php foreach ($sets as $set): ?>
                    <div class="col-md-6 mb-4">
                        <div class="product-card h-100 d-flex flex-column">
                            <a href="<?= BASE_URL ?>conjunto/<?= (int)$set['id'] ?>" class="product-image d-block" style="height:260px;">
                                <img src="<?= BASE_URL ?>set-image.php?id=<?= (int)$set['id'] ?>&size=medium" 
                                     alt="<?= htmlspecialchars($set['title']) ?>" 
                                     class="img-fluid rounded" 
                                     style="object-fit:cover; width:100%; height:100%;">
                            </a>
                            <div class="p-3 flex-fill d-flex flex-column">
                                <h3 class="product-title mb-1"><?= htmlspecialchars($set['title']) ?></h3>
                                <?php if (!empty($set['description'])): ?>
                                    <p class="text-muted mb-2">
                                        <?= htmlspecialchars(substr($set['description'], 0, 90)) ?><?= strlen($set['description']) > 90 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                <p class="product-price mt-auto">R$ <?= number_format((float)($set['price'] ?? 0), 2, ',', '.') ?></p>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>conjunto/<?= (int)$set['id'] ?>" class="btn btn-custom flex-fill">Ver Conjunto</a>
                                    <button type="button" class="btn btn-outline-light" onclick="addSetToCart(<?= (int)$set['id'] ?>)">
                                        <?= icon('cart-plus', 'icon') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="mb-0">Nenhum conjunto disponível no momento.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
const baseHref = (window.BATRIP_CONFIG && window.BATRIP_CONFIG.baseUrl) || '<?= addslashes(BASE_URL) ?>';

function addSetToCart(setId) {
    if (!setId || setId <= 0) {
        if (typeof showAlert !== 'undefined') {
            showAlert('ID do conjunto inválido', 'danger');
        } else {
            alert('ID do conjunto inválido');
        }
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
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
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
            btn.innerHTML = originalHTML;
            
            if (result.success) {
                if (typeof updateCartCount === 'function') {
                    updateCartCount(result.cart_count || 0);
                }
                if (typeof showAlert !== 'undefined') {
                    showAlert(result.message || 'Conjunto adicionado ao carrinho!', 'success');
                } else {
                    alert(result.message || 'Conjunto adicionado ao carrinho!');
                }
                
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
                                document.getElementById('cartSidebar').innerHTML = novoSidebar.innerHTML;
                            }
                        })
                        .catch(err => console.error('Erro ao atualizar sidebar:', err));
                }
            } else {
                if (result.requires_login) {
                    const msg = result.message || 'Você precisa estar logado para adicionar conjuntos ao carrinho.';
                    if (typeof showAlert !== 'undefined') {
                        showAlert(msg, 'warning');
                    } else {
                        alert(msg);
                    }
                    setTimeout(() => {
                        window.location.href = result.login_url || baseHref + 'login';
                    }, 1500);
                } else {
                    if (typeof showAlert !== 'undefined') {
                        showAlert(result.message || 'Erro ao adicionar conjunto ao carrinho.', 'danger');
                    } else {
                        alert(result.message || 'Erro ao adicionar conjunto ao carrinho.');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Erro ao adicionar conjunto:', error);
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            const errorMsg = error.message || 'Erro ao adicionar conjunto ao carrinho. Tente novamente.';
            if (typeof showAlert !== 'undefined') {
                showAlert(errorMsg, 'danger');
            } else {
                alert(errorMsg);
            }
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

