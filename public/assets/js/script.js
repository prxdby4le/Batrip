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


// Interações do preview do carrinho (offcanvas)
document.addEventListener('DOMContentLoaded', function() {
    // Submete ao trocar tamanho
    document.querySelectorAll('.cart-size-select').forEach(function(sel){
        sel.addEventListener('change', function(){
            const form = sel.closest('form');
            if (form) form.submit();
        });
    });

    // Botões +/- quantidade
    document.querySelectorAll('.cart-qty-form').forEach(function(form){
        const dec = form.querySelector('.cart-qty-dec');
        const inc = form.querySelector('.cart-qty-inc');
        const input = form.querySelector('.cart-qty-input');
        if (dec && input) {
            dec.addEventListener('click', function(){
                const v = Math.max(1, parseInt(input.value || '1', 10) - 1);
                input.value = v;
                form.submit();
            });
        }
        if (inc && input) {
            inc.addEventListener('click', function(){
                const v = Math.max(1, parseInt(input.value || '1', 10) + 1);
                input.value = v;
                form.submit();
            });
        }
        if (input) {
            input.addEventListener('change', function(){
                if (parseInt(input.value, 10) < 1 || isNaN(parseInt(input.value, 10))) input.value = 1;
                form.submit();
            });
        }
    });
});

// Abre o offcanvas do carrinho automaticamente quando indicado por query/hash
document.addEventListener('DOMContentLoaded', function() {
    try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('openCart') === '1' || window.location.hash.indexOf('openCart') !== -1) {
            var offcanvasEl = document.getElementById('cartSidebar');
            if (offcanvasEl && window.bootstrap && window.bootstrap.Offcanvas) {
                var oc = window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                oc.show();
            } else {
                // Fallback: clica no botão do carrinho se existir
                var btn = document.querySelector('[data-bs-target="#cartSidebar"]');
                if (btn) btn.click();
            }
        }
    } catch (e) { /* no-op */ }
});

// Controles +/- nos cards de produto (não envia automaticamente)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.card-qty-group').forEach(function(group){
        var dec = group.querySelector('.card-qty-dec');
        var inc = group.querySelector('.card-qty-inc');
        var input = group.querySelector('.card-qty-input');
        if (!input) return;
        var clamp = function(v){ v = parseInt(v || '1', 10); return isNaN(v) || v < 1 ? 1 : v; };
        if (dec) dec.addEventListener('click', function(){ input.value = clamp((parseInt(input.value||'1',10) - 1)); });
        if (inc) inc.addEventListener('click', function(){ input.value = clamp((parseInt(input.value||'1',10) + 1)); });
        input.addEventListener('change', function(){ input.value = clamp(input.value); });
    });
});

// Controles +/- na página de produto (não envia automaticamente)
document.addEventListener('DOMContentLoaded', function() {
    var group = document.querySelector('.product-qty-group');
    if (!group) return;
    var dec = group.querySelector('.product-qty-dec');
    var inc = group.querySelector('.product-qty-inc');
    var input = group.querySelector('.product-qty-input');
    if (!input) return;
    var clamp = function(v){ v = parseInt(v || '1', 10); return isNaN(v) || v < 1 ? 1 : v; };
    if (dec) dec.addEventListener('click', function(){ input.value = clamp((parseInt(input.value||'1',10) - 1)); });
    if (inc) inc.addEventListener('click', function(){ input.value = clamp((parseInt(input.value||'1',10) + 1)); });
    input.addEventListener('change', function(){ input.value = clamp(input.value); });
});

// Remover item do carrinho no sidebar
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-remove-sidebar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Remover este item do carrinho?')) return;
            
            const productId = parseInt(this.dataset.productId);
            const productSize = this.dataset.productSize;
            
            // Usa caminho relativo à tag <base> do HTML
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
                    location.reload();
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
});

