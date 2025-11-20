/**
 * ============================================
 * UTILIDADES PARA DASHBOARD
 * ============================================
 * 
 * Funciones reutilizables para animaciones,
 * notificaciones y efectos en las páginas del admin
 */

// ============================================
// ANIMACIONES Y EFECTOS VISUALES
// ============================================

/**
 * Anima elementos con entrada escalonada
 * @param {String} selector - Selector CSS de elementos a animar
 * @param {Number} delay - Delay entre animaciones (ms)
 * @param {String} type - Tipo de animación ('slideUp', 'slideDown', 'fadeIn')
 */
function animateElements(selector, delay = 100, type = 'slideUp') {
    const elements = document.querySelectorAll(selector);
    
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        
        switch(type) {
            case 'slideUp':
                element.style.transform = 'translateY(20px)';
                break;
            case 'slideDown':
                element.style.transform = 'translateY(-20px)';
                break;
            case 'slideLeft':
                element.style.transform = 'translateX(-20px)';
                break;
            case 'slideRight':
                element.style.transform = 'translateX(20px)';
                break;
            default:
                element.style.transform = 'translateY(20px)';
        }
        
        setTimeout(() => {
            element.style.transition = 'all 0.4s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0) translateX(0)';
        }, delay * (index + 1));
    });
}

/**
 * Anima elementos con efecto pulse
 * @param {String} selector - Selector CSS
 */
function addPulseEffect(selector) {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .pulse { animation: pulse 2s infinite; }
    `;
    document.head.appendChild(style);
    
    document.querySelectorAll(selector).forEach(el => {
        el.classList.add('pulse');
    });
}

/**
 * Anima lista de items con cascada
 * @param {String} selector - Selector de items
 * @param {Number} stagger - Delay entre items (ms)
 */
function animateListCascade(selector, stagger = 50) {
    const items = document.querySelectorAll(selector);
    items.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-10px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, stagger * index);
    });
}

// ============================================
// NOTIFICACIONES (TOASTS)
// ============================================

/**
 * Muestra una notificación toast
 * @param {String} message - Mensaje a mostrar
 * @param {String} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {Number} duration - Duración en ms (0 = permanente)
 */
function showToast(message, type = 'info', duration = 3000) {
    const colors = {
        success: { bg: '#d1fae5', text: '#065f46', icon: 'check-circle' },
        error: { bg: '#fee2e2', text: '#991b1b', icon: 'exclamation-circle' },
        warning: { bg: '#fef3c7', text: '#78350f', icon: 'exclamation-triangle' },
        info: { bg: '#dbeafe', text: '#1e40af', icon: 'info-circle' }
    };

    const config = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${config.icon}"></i>
        <span>${message}</span>
    `;

    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        padding: '16px 20px',
        borderRadius: '8px',
        background: config.bg,
        color: config.text,
        boxShadow: '0 10px 25px rgba(0, 0, 0, 0.2)',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        zIndex: '9999',
        animation: 'slideInUp 0.3s ease',
        fontSize: '0.95rem',
        fontWeight: '500',
        maxWidth: '400px',
        minWidth: '250px'
    });

    document.body.appendChild(toast);

    if (duration > 0) {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    return toast;
}

/**
 * Muestra confirmación
 * @param {String} message - Mensaje
 * @param {Function} onConfirm - Callback de confirmación
 * @param {Function} onCancel - Callback de cancelación
 */
function showConfirm(message, onConfirm, onCancel) {
    const backdrop = document.createElement('div');
    backdrop.className = 'confirm-backdrop';
    Object.assign(backdrop.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        right: '0',
        bottom: '0',
        background: 'rgba(0, 0, 0, 0.5)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: '10000',
        backdropFilter: 'blur(4px)'
    });

    const dialog = document.createElement('div');
    Object.assign(dialog.style, {
        background: 'white',
        padding: '24px',
        borderRadius: '12px',
        boxShadow: '0 20px 60px rgba(0, 0, 0, 0.2)',
        maxWidth: '400px',
        animation: 'slideUp 0.3s ease'
    });

    dialog.innerHTML = `
        <p style="margin: 0 0 20px 0; font-size: 1rem; color: #1f2937; font-weight: 500;">
            ${message}
        </p>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button class="btn-cancel" style="
                padding: 10px 16px;
                border: 1px solid #e5e7eb;
                background: #f3f4f6;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.3s ease;
            ">Cancelar</button>
            <button class="btn-confirm" style="
                padding: 10px 16px;
                border: none;
                background: #0891b2;
                color: white;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.3s ease;
            ">Confirmar</button>
        </div>
    `;

    backdrop.appendChild(dialog);
    document.body.appendChild(backdrop);

    const btnConfirm = dialog.querySelector('.btn-confirm');
    const btnCancel = dialog.querySelector('.btn-cancel');

    btnConfirm.addEventListener('click', () => {
        backdrop.remove();
        if (onConfirm) onConfirm();
    });

    btnCancel.addEventListener('click', () => {
        backdrop.remove();
        if (onCancel) onCancel();
    });

    // Cerrar con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            backdrop.remove();
            if (onCancel) onCancel();
        }
    }, { once: true });
}

// ============================================
// EFECTOS HOVER AVANZADOS
// ============================================

/**
 * Agrega efectos hover a elementos
 * @param {String} selector - Selector CSS
 * @param {String} effect - Tipo de efecto
 */
function addHoverEffect(selector, effect = 'lift') {
    const elements = document.querySelectorAll(selector);
    
    elements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            switch(effect) {
                case 'lift':
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = '0 10px 15px rgba(0, 0, 0, 0.1)';
                    break;
                case 'grow':
                    this.style.transform = 'scale(1.05)';
                    break;
                case 'glow':
                    this.style.boxShadow = '0 0 20px rgba(8, 145, 178, 0.4)';
                    break;
                case 'rotate':
                    this.style.transform = 'rotate(2deg)';
                    break;
            }
            this.style.transition = 'all 0.3s ease';
        });

        el.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1) rotate(0)';
            this.style.boxShadow = 'none';
        });
    });
}

// ============================================
// ACTUALIZACIONES EN TIEMPO REAL
// ============================================

/**
 * Actualiza un elemento con animación
 * @param {String} selector - Selector CSS
 * @param {String} newContent - Contenido nuevo
 */
function updateWithAnimation(selector, newContent) {
    const element = document.querySelector(selector);
    if (!element) return;

    element.style.opacity = '0.5';
    element.style.transform = 'scale(0.95)';
    element.style.transition = 'all 0.3s ease';

    setTimeout(() => {
        element.innerHTML = newContent;
        element.style.opacity = '1';
        element.style.transform = 'scale(1)';
    }, 150);
}

/**
 * Actualiza automáticamente un elemento
 * @param {String} selector - Selector CSS
 * @param {String} url - URL para fetch
 * @param {Number} interval - Intervalo en ms
 */
function startAutoRefresh(selector, url, interval = 5000) {
    const element = document.querySelector(selector);
    if (!element) return;

    const refresh = () => {
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.html) {
                    updateWithAnimation(selector, data.html);
                }
            })
            .catch(err => console.error('Error en refresh:', err));
    };

    refresh();
    return setInterval(refresh, interval);
}

// ============================================
// UTILIDADES DE DATOS
// ============================================

/**
 * Formatea número como moneda
 * @param {Number} value - Valor a formatear
 * @param {String} currency - Código de moneda (ej: 'PEN', 'USD')
 */
function formatCurrency(value, currency = 'PEN') {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: currency
    }).format(value);
}

/**
 * Formatea fecha
 * @param {String|Date} date - Fecha a formatear
 * @param {String} format - Formato ('short', 'long', 'time')
 */
function formatDate(date, format = 'short') {
    const d = new Date(date);
    
    switch(format) {
        case 'short':
            return d.toLocaleDateString('es-ES');
        case 'long':
            return d.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        case 'time':
            return d.toLocaleTimeString('es-ES');
        case 'full':
            return d.toLocaleString('es-ES');
        default:
            return d.toLocaleDateString('es-ES');
    }
}

// ============================================
// UTILIDADES DE FORMULARIOS
// ============================================

/**
 * Agrega validación visual a campos
 * @param {String} selector - Selector del formulario
 */
function addFormValidation(selector) {
    const form = document.querySelector(selector);
    if (!form) return;

    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.style.borderColor = '#ef4444';
                this.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            } else {
                this.style.borderColor = '#e5e7eb';
                this.style.boxShadow = 'none';
            }
        });

        input.addEventListener('focus', function() {
            this.style.borderColor = '#0891b2';
            this.style.boxShadow = '0 0 0 3px rgba(8, 145, 178, 0.1)';
        });
    });
}

// ============================================
// UTILIDADES DE ACCESIBILIDAD
// ============================================

/**
 * Agrega atajos de teclado
 * @param {Object} shortcuts - Objeto con teclas y funciones
 */
function addKeyboardShortcuts(shortcuts) {
    document.addEventListener('keydown', (e) => {
        const key = e.ctrlKey || e.metaKey ? `ctrl+${e.key}` : 
                    e.altKey ? `alt+${e.key}` : 
                    e.key;

        if (shortcuts[key]) {
            e.preventDefault();
            shortcuts[key]();
        }
    });
}

/**
 * Anota elementos para screen readers
 * @param {String} selector - Selector CSS
 * @param {String} label - Etiqueta aria
 */
function addAriaLabel(selector, label) {
    document.querySelectorAll(selector).forEach(el => {
        el.setAttribute('aria-label', label);
    });
}

// ============================================
// EXPORTAR FUNCIONES
// ============================================

console.log('Dashboard Utils cargado - Funciones disponibles:');
console.log('✓ animateElements()');
console.log('✓ addPulseEffect()');
console.log('✓ animateListCascade()');
console.log('✓ showToast()');
console.log('✓ showConfirm()');
console.log('✓ addHoverEffect()');
console.log('✓ updateWithAnimation()');
console.log('✓ startAutoRefresh()');
console.log('✓ formatCurrency()');
console.log('✓ formatDate()');
console.log('✓ addFormValidation()');
console.log('✓ addKeyboardShortcuts()');
console.log('✓ addAriaLabel()');
