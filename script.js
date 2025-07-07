document.addEventListener('DOMContentLoaded', function () {
    var fotoInput = document.getElementById('fotoPerfil');
    var fotoPreview = document.getElementById('fotoPerfilPreview');
    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function (ev) {
                    fotoPreview.src = ev.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Validação para salvar segurança: exige senha
    var formSeguranca = document.getElementById('perfilSegurancaForm');
    if (formSeguranca) {
        formSeguranca.addEventListener('submit', function (e) {
            e.preventDefault();
            var senha = document.getElementById('senhaConfirmacao').value;
            if (!senha) {
                alert('Digite sua senha para salvar as alterações.');
                return;
            }
            // Aqui você pode adicionar a lógica de verificação da senha real
            alert('Alterações salvas com sucesso!');
            formSeguranca.reset();
        });
    }
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero-section');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.product-card, .artist-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(50px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});

// Corrige erro ao adicionar event listener em elemento inexistente
const customForm = document.getElementById('custom-form');
if (customForm) {
    customForm.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Pedido enviado com sucesso! Entraremos em contato em breve.');
        this.reset();
    });
}

// Modal para visualização de imagens da galeria
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const galleryImages = document.querySelectorAll('.gallery-batrip .gallery-img');
        if (galleryImages.length > 0) {
            // Cria modal se não existir
            let modal = document.getElementById('galleryModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'galleryModal';
                modal.innerHTML = `
                    <button class="close-modal" aria-label="Fechar">&times;</button>
                    <img src="" alt="Imagem ampliada" />
                `;
                document.body.appendChild(modal);
            }
            const modalImg = modal.querySelector('img');
            const closeBtn = modal.querySelector('.close-modal');
            galleryImages.forEach(img => {
                img.addEventListener('click', function() {
                    modalImg.src = this.src;
                    modal.classList.add('active');
                });
            });
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                modalImg.src = '';
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    modalImg.src = '';
                }
            });
            document.addEventListener('keydown', function(e) {
                if (modal.classList.contains('active') && (e.key === 'Escape' || e.key === 'Esc')) {
                    modal.classList.remove('active');
                    modalImg.src = '';
                }
            });
        }
    });
})();


// --- Carrinho de compras ---
// Utiliza localStorage para persistir os itens
function getCart() {
    return JSON.parse(localStorage.getItem('batrip_cart') || '[]');
}

function setCart(cart) {
    localStorage.setItem('batrip_cart', JSON.stringify(cart));
}

function addToCart(product) {
    let cart = getCart();
    // Se já existe (mesmo título e tamanho), soma quantidade
    const idx = cart.findIndex(item => item.title === product.title && item.size === product.size);
    if (idx !== -1) {
        cart[idx].qty += 1;
    } else {
        cart.push({ ...product, qty: 1 });
    }
    setCart(cart);
    updateCartPreview();
    updateCartCount();
}

function removeFromCart(title, size) {
    let cart = getCart();
    cart = cart.filter(item => !(item.title === title && item.size === size));
    setCart(cart);
    updateCartPreview();
    updateCartCount();
    // Notifica todas as páginas abertas
    try {
        localStorage.setItem('batrip_cart_event', Date.now());
    } catch(e) {}
    if (typeof renderCartPage === 'function') renderCartPage();
}

function updateCartItemQty(title, size, qty) {
    let cart = getCart();
    const idx = cart.findIndex(item => item.title === title && item.size === size);
    if (idx !== -1) {
        cart[idx].qty = qty;
        setCart(cart);
        updateCartPreview();
        updateCartCount();
        try {
            localStorage.setItem('batrip_cart_event', Date.now());
        } catch(e) {}
        if (typeof renderCartPage === 'function') renderCartPage();
    }
}

function updateCartItemSize(title, oldSize, newSize) {
    let cart = getCart();
    const idx = cart.findIndex(item => item.title === title && item.size === oldSize);
    if (idx !== -1) {
        // Se já existe um item com o novo tamanho, soma as quantidades
        const existingIdx = cart.findIndex(item => item.title === title && item.size === newSize);
        if (existingIdx !== -1 && existingIdx !== idx) {
            cart[existingIdx].qty += cart[idx].qty;
            cart.splice(idx, 1);
        } else {
            cart[idx].size = newSize;
        }
        setCart(cart);
        updateCartPreview();
        updateCartCount();
        try {
            localStorage.setItem('batrip_cart_event', Date.now());
        } catch(e) {}
        if (typeof renderCartPage === 'function') renderCartPage();
    }
}

function updateCartCount() {
    const cart = getCart();
    const count = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.getElementById('cart-count');
    if (badge) badge.textContent = count;
}

function updateCartPreview() {
    const cart = getCart();
    const preview = document.getElementById('cart-preview');
    if (!preview) return;
    if (cart.length === 0) {
        preview.innerHTML = '<div class="text-center text-muted">Seu carrinho está vazio.</div>';
        return;
    }
    let html = '';
    let subtotal = 0;
    cart.forEach(item => {
        subtotal += item.qty * item.price;
        html += `<div class="d-flex align-items-center mb-3">
            <img src="${item.img}" alt="${item.title}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
              <div class="fw-bold">${item.title}</div>
              <div class="text-muted small">Tamanho: 
                <select class="cart-size-input" data-title="${item.title}" data-size="${item.size}">
                  <option value="P"${item.size === 'P' ? ' selected' : ''}>P</option>
                  <option value="M"${item.size === 'M' ? ' selected' : ''}>M</option>
                  <option value="G"${item.size === 'G' ? ' selected' : ''}>G</option>
                  <option value="GG"${item.size === 'GG' ? ' selected' : ''}>GG</option>
                </select>
              </div>
              <div class="text-muted small">
                <input type="number" min="1" value="${item.qty}" class="cart-qty-input" data-title="${item.title}" data-size="${item.size}" style="width:50px; text-align:center;"> x R$ <span class="item-unit-price">${item.price.toFixed(2)}</span> = <span class="item-subtotal">${(item.price * item.qty).toFixed(2)}</span>
              </div>
            </div>
            <button class="btn btn-sm btn-link text-danger ms-auto remove-cart-item" data-title="${item.title}" data-size="${item.size}"><i class="fas fa-trash"></i></button>
          </div>`;
    });
    const cep = getUserCep();
    const frete = calcularFrete(cep);
    const total = subtotal + frete;
    html += `<hr><div class="d-flex justify-content-between"><span>Subtotal:</span><span id="cart-preview-subtotal">R$ ${subtotal.toFixed(2)}</span></div>`;
    html += `<div class="d-flex justify-content-between"><span>Frete:</span><span id="cart-preview-frete">R$ ${frete.toFixed(2)}</span></div>`;
    html += `<div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="cart-preview-total">R$ ${total.toFixed(2)}</span></div>`;
    html += `<div class="text-muted small">CEP: ${cep}</div>`;
    html += `<a href="carrinho.html" class="btn btn-custom w-100 mt-3">Finalizar Compra</a>`;
    preview.innerHTML = html;
    // Botões de remover
    // Remove todos os listeners antigos para garantir que todos os botões funcionem
    const removeBtns = Array.from(preview.querySelectorAll('.remove-cart-item'));
    removeBtns.forEach(btn => {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
    });
    preview.querySelectorAll('.remove-cart-item').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            removeFromCart(this.getAttribute('data-title'), this.getAttribute('data-size'));
            setTimeout(updateCartPreview, 10);
        });
    });
    // Inputs de quantidade
    preview.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('input', function() {
            let val = parseInt(this.value);
            if (isNaN(val) || val < 1) val = 1;
            this.value = val;
            updateCartItemQty(this.getAttribute('data-title'), this.getAttribute('data-size'), val);
            // Atualiza subtotal do item em tempo real
            const parent = this.closest('.d-flex.align-items-center');
            const price = parseFloat(parent.querySelector('.item-unit-price').textContent.replace(',', '.'));
            parent.querySelector('.item-subtotal').textContent = (price * val).toFixed(2);
            // Atualiza totais do preview
            let cart = getCart();
            let subtotal = cart.reduce((sum, item) => sum + item.qty * item.price, 0);
            let cep = getUserCep();
            let frete = calcularFrete(cep);
            let total = subtotal + frete;
            document.getElementById('cart-preview-subtotal').textContent = 'R$ ' + subtotal.toFixed(2);
            document.getElementById('cart-preview-frete').textContent = 'R$ ' + frete.toFixed(2);
            document.getElementById('cart-preview-total').textContent = 'R$ ' + total.toFixed(2);
        });
    });
    // Inputs de tamanho
    preview.querySelectorAll('.cart-size-input').forEach(select => {
        select.addEventListener('change', function() {
            updateCartItemSize(this.getAttribute('data-title'), this.getAttribute('data-size'), this.value);
        });
    });
}

// Adiciona evento aos botões "Carrinho" dos produtos
document.addEventListener('DOMContentLoaded', function () {
    // Botão "Carrinho" dos cards
    document.querySelectorAll('.product-card .btn.btn-custom').forEach(btn => {
        const label = btn.textContent.trim().toLowerCase();
        if (label === 'carrinho' || label === 'comprar') {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const card = btn.closest('.product-card');
                const title = card.querySelector('.product-title').textContent.trim();
                const priceText = card.querySelector('.product-price').textContent.replace(/[^\d,]/g, '').replace(',', '.');
                const price = parseFloat(priceText);
                const img = card.querySelector('img').getAttribute('src');
                // Tenta pegar tamanho selecionado se existir
                let size = 'M';
                const sizeSelect = card.querySelector('.product-size-select');
                if (sizeSelect) size = sizeSelect.value;
                addToCart({ title, price, img, size });
            });
        }
    });

    // Botão "Comprar" em páginas de produto
    const prodBtn = document.querySelector('form .btn.btn-custom');
    if (prodBtn && prodBtn.textContent.trim().toLowerCase() === 'comprar') {
        prodBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Busca dados do produto na página
            const container = prodBtn.closest('.col-md-6');
            if (!container) return;
            const title = container.querySelector('.product-title')?.textContent.trim() || document.title;
            const priceText = container.querySelector('.product-price')?.textContent.replace(/[^\d,]/g, '').replace(',', '.');
            const price = parseFloat(priceText);
            const img = document.querySelector('.product-img-store')?.getAttribute('src') || '';
            // Pega tamanho selecionado
            let size = 'M';
            const sizeSelect = document.getElementById('tamanho');
            if (sizeSelect && sizeSelect.value) size = sizeSelect.value;
            addToCart({ title, price, img, size });
        });
    }
    updateCartPreview();
    updateCartCount();
});

// Sincroniza carrinho entre abas/páginas
window.addEventListener('storage', function(e) {
    if (e.key === 'batrip_cart' || e.key === 'batrip_cart_event') {
        updateCartPreview();
        updateCartCount();
        if (typeof renderCartPage === 'function') renderCartPage();
    }
});

// Renderização dinâmica do carrinho na página carrinho.html
if (window.location.pathname.endsWith('carrinho.html')) {
    window.renderCartPage = function() {
        const cart = getCart();
        const container = document.getElementById('cart-items-container');
        const emptyMsg = document.getElementById('cart-empty-message');
        if (!container) return;
        let html = '';
        if (cart.length === 0) {
            container.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
        } else {
            if (emptyMsg) emptyMsg.classList.add('d-none');
            cart.forEach(item => {
                html += `<tr>
                    <td><img src="${item.img}" alt="${item.title}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;"></td>
                    <td><div class="fw-bold">${item.title}</div></td>
                    <td>
                        <select class="cart-size-input form-select form-select-sm" data-title="${item.title}" data-size="${item.size}" style="width:90px;">
                            <option value="P"${item.size === 'P' ? ' selected' : ''}>P</option>
                            <option value="M"${item.size === 'M' ? ' selected' : ''}>M</option>
                            <option value="G"${item.size === 'G' ? ' selected' : ''}>G</option>
                            <option value="GG"${item.size === 'GG' ? ' selected' : ''}>GG</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" min="1" value="${item.qty}" class="cart-qty-input form-control form-control-sm" data-title="${item.title}" data-size="${item.size}" style="width:60px; text-align:center;">
                    </td>
                    <td class="text-end subtotal-cell">R$ ${(item.price * item.qty).toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-link text-danger remove-cart-item" data-title="${item.title}" data-size="${item.size}"><i class="fas fa-trash"></i></button></td>
                </tr>`;
            });
            container.innerHTML = html;
            // Remover item (garante que todos os botões funcionem, inclusive o primeiro)
            const removeBtns = Array.from(container.querySelectorAll('.remove-cart-item'));
            removeBtns.forEach(btn => {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
            });
            container.querySelectorAll('.remove-cart-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    removeFromCart(this.getAttribute('data-title'), this.getAttribute('data-size'));
                    window.renderCartPage();
                    window.renderCartSummary();
                });
            });
            // Inputs de quantidade
            container.querySelectorAll('.cart-qty-input').forEach(input => {
                input.addEventListener('input', function() {
                    let val = parseInt(this.value);
                    if (isNaN(val) || val < 1) val = 1;
                    this.value = val;
                    updateCartItemQty(this.getAttribute('data-title'), this.getAttribute('data-size'), val);
                    // Atualiza subtotal da linha em tempo real
                    const row = this.closest('tr');
                    const price = cart.find(i => i.title === this.getAttribute('data-title') && i.size === this.getAttribute('data-size')).price;
                    row.querySelector('.subtotal-cell').textContent = 'R$ ' + (price * val).toFixed(2);
                    // Atualiza resumo do pedido em tempo real
                    let newCart = getCart();
                    let subtotal = newCart.reduce((sum, item) => sum + item.qty * item.price, 0);
                    const cep = getUserCep();
                    let frete = newCart.length > 0 ? calcularFrete(cep) : 0;
                    let total = subtotal + frete;
                    const summary = document.getElementById('cart-summary');
                    if (summary) {
                        summary.innerHTML = `<h5 class="fw-bold mb-3">Resumo do Pedido</h5>
                            <div class="d-flex justify-content-between"><span>Subtotal</span><span>R$ ${subtotal.toFixed(2)}</span></div>
                            <div class="d-flex justify-content-between"><span>Frete</span><span>R$ ${frete.toFixed(2)}</span></div>
                            <hr><div class="d-flex justify-content-between fw-bold"><span>Total</span><span>R$ ${total.toFixed(2)}</span></div>
                            <div class="text-muted small">CEP: ${cep}</div>
                            <a href="#" class="btn btn-custom w-100 mt-3">Finalizar Compra</a>`;
                    }
                });
            });
            // Inputs de tamanho
            container.querySelectorAll('.cart-size-input').forEach(select => {
                select.addEventListener('change', function() {
                    updateCartItemSize(this.getAttribute('data-title'), this.getAttribute('data-size'), this.value);
                    // Atualiza carrinho e resumo em tempo real
                    setTimeout(() => {
                        window.renderCartPage();
                        // Atualiza resumo do pedido em tempo real
                        let newCart = getCart();
                        let subtotal = newCart.reduce((sum, item) => sum + item.qty * item.price, 0);
                        const cep = getUserCep();
                        let frete = newCart.length > 0 ? calcularFrete(cep) : 0;
                        let total = subtotal + frete;
                        const summary = document.getElementById('cart-summary');
                        if (summary) {
                            summary.innerHTML = `<h5 class=\"fw-bold mb-3\">Resumo do Pedido</h5>
                                <div class=\"d-flex justify-content-between\"><span>Subtotal</span><span>R$ ${subtotal.toFixed(2)}</span></div>
                                <div class=\"d-flex justify-content-between\"><span>Frete</span><span>R$ ${frete.toFixed(2)}</span></div>
                                <hr><div class=\"d-flex justify-content-between fw-bold\"><span>Total</span><span>R$ ${total.toFixed(2)}</span></div>
                                <div class=\"text-muted small\">CEP: ${cep}</div>
                                <a href=\"#\" class=\"btn btn-custom w-100 mt-3\">Finalizar Compra</a>`;
                        }
                    }, 10);
                });
            });
        }
    }
    window.renderCartSummary = function() {
        const cart = getCart();
        let subtotal = cart.reduce((sum, item) => sum + item.qty * item.price, 0);
        const cep = getUserCep();
        let frete = cart.length > 0 ? calcularFrete(cep) : 0;
        let total = subtotal + frete;
        const summary = document.getElementById('cart-summary');
        if (summary) {
            summary.innerHTML = `<h5 class="fw-bold mb-3">Resumo do Pedido</h5>
                <div class="d-flex justify-content-between"><span>Subtotal</span><span>R$ ${subtotal.toFixed(2)}</span></div>
                <div class="d-flex justify-content-between"><span>Frete</span><span>R$ ${frete.toFixed(2)}</span></div>
                <hr><div class="d-flex justify-content-between fw-bold"><span>Total</span><span>R$ ${total.toFixed(2)}</span></div>
                <div class="text-muted small">CEP: ${cep}</div>
                <a href="#" class="btn btn-custom w-100 mt-3">Finalizar Compra</a>`;
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        window.renderCartPage();
        window.renderCartSummary();
        window.addEventListener('storage', function(e) {
            if (e.key === 'batrip_cart' || e.key === 'batrip_cart_event') {
                window.renderCartPage();
                window.renderCartSummary();
            }
        });
    });
}

// Função para obter o CEP do usuário salvo no perfil
function getUserCep() {
    // Tenta pegar do localStorage (salvo ao editar perfil)
    return localStorage.getItem('batrip_cep') || '00000-000';
}

// Função para calcular o frete baseado no CEP (exemplo simples)
function calcularFrete(cep) {
    // Exemplo: frete grátis para SP (inicia com 01), 20 para sudeste, 40 para outros
    if (/^0[1-9]/.test(cep)) return 0; // SP
    if (/^1[0-9]/.test(cep) || /^2[0-9]/.test(cep)) return 20; // Sudeste
    if (/^3[0-9]/.test(cep) || /^4[0-9]/.test(cep)) return 30; // Sul/Centro-Oeste
    if (/^5[0-9]/.test(cep) || /^6[0-9]/.test(cep) || /^7[0-9]/.test(cep)) return 40; // Nordeste/Norte
    return 50; // Outros
}

// Salva o CEP do perfil no localStorage ao editar perfil
document.addEventListener('DOMContentLoaded', function () {
    // ...existing code...
    // Salva o CEP do perfil no localStorage ao editar perfil
    var perfilForm = document.getElementById('perfilSegurancaForm');
    if (perfilForm) {
        perfilForm.addEventListener('submit', function(e) {
            var cepInput = document.getElementById('cep');
            if (cepInput && cepInput.value) {
                localStorage.setItem('batrip_cep', cepInput.value.replace(/\D/g, ''));
            }
        });
    }
});

