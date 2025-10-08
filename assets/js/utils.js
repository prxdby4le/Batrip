/**
 * Batrip - Funções JavaScript Compartilhadas
 * Criado em: 2025-10-08
 * 
 * Este arquivo centraliza funções JavaScript reutilizáveis
 * para evitar duplicação de código.
 */

/**
 * Exibe um alerta customizado no topo da página
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo do alerta ('success', 'danger', 'warning', 'info')
 * @param {number} duration - Duração em ms (0 = não fecha automaticamente)
 */
function showAlert(message, type = 'info', duration = 5000) {
    // Remove alertas existentes
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Cria o alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show custom-alert`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 300px;
        max-width: 600px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Adiciona ao DOM
    document.body.appendChild(alertDiv);
    
    // Remove automaticamente após duração especificada
    if (duration > 0) {
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, duration);
    }
}

/**
 * Valida formato de email
 * @param {string} email - Email a ser validado
 * @returns {boolean}
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Valida CEP brasileiro (formato: 12345-678 ou 12345678)
 * @param {string} cep - CEP a ser validado
 * @returns {boolean}
 */
function isValidCEP(cep) {
    const cleanCEP = cep.replace(/\D/g, '');
    return cleanCEP.length === 8;
}

/**
 * Formata CEP para o padrão brasileiro (12345-678)
 * @param {string} cep - CEP a ser formatado
 * @returns {string}
 */
function formatCEP(cep) {
    const cleanCEP = cep.replace(/\D/g, '');
    if (cleanCEP.length === 8) {
        return cleanCEP.replace(/(\d{5})(\d{3})/, '$1-$2');
    }
    return cep;
}

/**
 * Formata valor monetário para Real brasileiro
 * @param {number} value - Valor numérico
 * @returns {string}
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Debounce: Limita a frequência de execução de uma função
 * Útil para otimizar eventos como keyup, scroll, resize
 * @param {Function} func - Função a ser executada
 * @param {number} wait - Tempo de espera em ms
 * @returns {Function}
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle: Garante que uma função seja executada no máximo uma vez a cada intervalo
 * @param {Function} func - Função a ser executada
 * @param {number} limit - Intervalo mínimo em ms
 * @returns {Function}
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Copia texto para a área de transferência
 * @param {string} text - Texto a ser copiado
 * @returns {Promise<boolean>}
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch (err) {
        console.error('Erro ao copiar:', err);
        return false;
    }
}

/**
 * Gera um hash simples de uma string (para cache keys, etc)
 * @param {string} str - String a ser transformada em hash
 * @returns {number}
 */
function hashCode(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}

/**
 * Verifica se o usuário está em um dispositivo móvel
 * @returns {boolean}
 */
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Smooth scroll para um elemento
 * @param {string} selector - Seletor CSS do elemento
 * @param {number} offset - Offset adicional em pixels
 */
function smoothScrollTo(selector, offset = 0) {
    const element = document.querySelector(selector);
    if (element) {
        const y = element.getBoundingClientRect().top + window.pageYOffset + offset;
        window.scrollTo({ top: y, behavior: 'smooth' });
    }
}

/**
 * Sanitiza HTML para prevenir XSS
 * @param {string} html - HTML a ser sanitizado
 * @returns {string}
 */
function sanitizeHTML(html) {
    const temp = document.createElement('div');
    temp.textContent = html;
    return temp.innerHTML;
}

/**
 * Carrega uma imagem de forma assíncrona
 * @param {string} src - URL da imagem
 * @returns {Promise<HTMLImageElement>}
 */
function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

// Exportar para uso global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showAlert,
        isValidEmail,
        isValidCEP,
        formatCEP,
        formatCurrency,
        debounce,
        throttle,
        copyToClipboard,
        hashCode,
        isMobile,
        smoothScrollTo,
        sanitizeHTML,
        loadImage
    };
}
