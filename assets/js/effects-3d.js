/**
 * Batrip - Efeitos 3D e Animações
 * Background animado discreto + Cards 3D interativos
 */

// ============================================
// BACKGROUND ANIMADO COM PARTÍCULAS
// ============================================

class AnimatedBackground {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.particleCount = 50;
        
        this.init();
    }
    
    init() {
        // Configurar canvas
        this.canvas.style.position = 'fixed';
        this.canvas.style.top = '0';
        this.canvas.style.left = '0';
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100%';
        this.canvas.style.zIndex = '-1';
        this.canvas.style.opacity = '0.3';
        this.canvas.style.pointerEvents = 'none';
        
        document.body.insertBefore(this.canvas, document.body.firstChild);
        
        this.resize();
        this.createParticles();
        this.animate();
        
        window.addEventListener('resize', () => this.resize());
    }
    
    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }
    
    createParticles() {
        for (let i = 0; i < this.particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 2 + 1,
                opacity: Math.random() * 0.5 + 0.2
            });
        }
    }
    
    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Atualizar e desenhar partículas
        this.particles.forEach(particle => {
            // Mover partícula
            particle.x += particle.vx;
            particle.y += particle.vy;
            
            // Bounce nas bordas
            if (particle.x < 0 || particle.x > this.canvas.width) particle.vx *= -1;
            if (particle.y < 0 || particle.y > this.canvas.height) particle.vy *= -1;
            
            // Desenhar partícula
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = `rgba(128, 128, 128, ${particle.opacity})`;
            this.ctx.fill();
        });
        
        // Conectar partículas próximas
        this.connectParticles();
        
        requestAnimationFrame(() => this.animate());
    }
    
    connectParticles() {
        for (let i = 0; i < this.particles.length; i++) {
            for (let j = i + 1; j < this.particles.length; j++) {
                const dx = this.particles[i].x - this.particles[j].x;
                const dy = this.particles[i].y - this.particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 150) {
                    this.ctx.beginPath();
                    this.ctx.strokeStyle = `rgba(128, 128, 128, ${0.2 * (1 - distance / 150)})`;
                    this.ctx.lineWidth = 1;
                    this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                    this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                    this.ctx.stroke();
                }
            }
        }
    }
}

// ============================================
// EFEITO 3D NOS CARDS DE PRODUTO
// ============================================

class Card3DEffect {
    constructor(card) {
        this.card = card;
        this.isHovering = false;
        
        this.init();
    }
    
    init() {
        // Adicionar estilo 3D ao card
        this.card.style.transform = 'perspective(1000px)';
        this.card.style.transition = 'transform 0.1s ease-out, box-shadow 0.3s ease';
        this.card.style.transformStyle = 'preserve-3d';
        
        // Event listeners
        this.card.addEventListener('mouseenter', () => this.onMouseEnter());
        this.card.addEventListener('mouseleave', () => this.onMouseLeave());
        this.card.addEventListener('mousemove', (e) => this.onMouseMove(e));
    }
    
    onMouseEnter() {
        this.isHovering = true;
        this.card.style.transition = 'transform 0.1s ease-out, box-shadow 0.3s ease';
    }
    
    onMouseLeave() {
        this.isHovering = false;
        this.card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)';
        this.card.style.boxShadow = '';
        this.card.style.transition = 'transform 0.5s ease, box-shadow 0.5s ease';
    }
    
    onMouseMove(e) {
        if (!this.isHovering) return;
        
        const rect = this.card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = ((y - centerY) / centerY) * -10; // Max 10 graus
        const rotateY = ((x - centerX) / centerX) * 10;
        
        // Calcular intensidade da sombra baseado na rotação
        const shadowX = rotateY * 2;
        const shadowY = rotateX * -2;
        const shadowBlur = 30;
        
        this.card.style.transform = `
            perspective(1000px) 
            rotateX(${rotateX}deg) 
            rotateY(${rotateY}deg) 
            scale(1.05)
        `;
        
        this.card.style.boxShadow = `
            ${shadowX}px ${shadowY}px ${shadowBlur}px rgba(128, 128, 128, 0.3),
            0 10px 40px rgba(0, 0, 0, 0.5)
        `;
    }
}

// ============================================
// GLARE EFFECT (BRILHO QUE SEGUE O MOUSE)
// ============================================

class GlareEffect {
    constructor(card) {
        this.card = card;
        this.glare = null;
        
        this.init();
    }
    
    init() {
        // Criar elemento de brilho
        this.glare = document.createElement('div');
        this.glare.className = 'card-glare';
        this.glare.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.2), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10;
            border-radius: inherit;
        `;
        
        this.card.style.position = 'relative';
        this.card.style.overflow = 'hidden';
        this.card.appendChild(this.glare);
        
        // Event listeners
        this.card.addEventListener('mouseenter', () => {
            this.glare.style.opacity = '1';
        });
        
        this.card.addEventListener('mouseleave', () => {
            this.glare.style.opacity = '0';
        });
        
        this.card.addEventListener('mousemove', (e) => {
            const rect = this.card.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            this.glare.style.background = `
                radial-gradient(circle at ${x}% ${y}%, 
                rgba(255, 255, 255, 0.3), 
                transparent 50%)
            `;
        });
    }
}

// ============================================
// PARALLAX SUAVE NO SCROLL
// ============================================

class ParallaxEffect {
    constructor() {
        this.cards = [];
        this.init();
    }
    
    init() {
        window.addEventListener('scroll', () => this.onScroll(), { passive: true });
    }
    
    addCard(card) {
        this.cards.push(card);
    }
    
    onScroll() {
        const scrolled = window.pageYOffset;
        
        this.cards.forEach((card, index) => {
            const rect = card.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
            
            if (isVisible) {
                const speed = 0.05 + (index % 3) * 0.02; // Velocidades variadas
                const yPos = -(scrolled * speed);
                card.style.transform = `translateY(${yPos}px)`;
            }
        });
    }
}

// ============================================
// INICIALIZAÇÃO AUTOMÁTICA
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Inicializando efeitos 3D e animações
    
    // Background animado
    const background = new AnimatedBackground();
    
    // Parallax
    const parallax = new ParallaxEffect();
    
    // Aplicar efeitos 3D nos cards
    const productCards = document.querySelectorAll('.product-card, .card, [class*="product"]');
    
    productCards.forEach(card => {
        // Apenas cards que parecem ser de produto
        if (card.querySelector('img') || card.classList.contains('product-card')) {
            new Card3DEffect(card);
            new GlareEffect(card);
            parallax.addCard(card);
        }
    });
    
    // Cards com efeito 3D aplicado
});

// ============================================
// HOVER EFFECT ADICIONAL COM ESCALA
// ============================================

// Adicionar CSS dinamicamente
const style = document.createElement('style');
style.textContent = `
    .product-card, .card {
        cursor: pointer;
        will-change: transform;
        backface-visibility: hidden;
    }
    
    .product-card img, .card img {
        transition: transform 0.3s ease;
        will-change: transform;
    }
    
    .product-card:hover img, .card:hover img {
        transform: scale(1.1);
    }
    
    /* Animação suave de entrada */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .product-card, .card {
        animation: fadeInUp 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    /* Delay progressivo para cada card */
    .product-card:nth-child(1), .card:nth-child(1) { animation-delay: 0.1s; }
    .product-card:nth-child(2), .card:nth-child(2) { animation-delay: 0.2s; }
    .product-card:nth-child(3), .card:nth-child(3) { animation-delay: 0.3s; }
    .product-card:nth-child(4), .card:nth-child(4) { animation-delay: 0.4s; }
    .product-card:nth-child(5), .card:nth-child(5) { animation-delay: 0.5s; }
    .product-card:nth-child(6), .card:nth-child(6) { animation-delay: 0.6s; }
`;
document.head.appendChild(style);

// Exportar para uso global
window.BatripEffects = {
    AnimatedBackground,
    Card3DEffect,
    GlareEffect,
    ParallaxEffect
};
