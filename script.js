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

document.getElementById('custom-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Pedido enviado com sucesso! Entraremos em contato em breve.');
    this.reset();
});

