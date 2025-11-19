/* ============================================
   PUBLIC PAGES SCRIPT
   Form validation, notifications, utilities
   ============================================ */

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;

    // Insert at top of page
    const container = document.body;
    container.insertBefore(notification, container.firstChild);

    // Add margin and animation
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.left = '50%';
    notification.style.transform = 'translateX(-50%)';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'slideDown 0.3s ease-out';

    // Auto remove
    if (duration > 0) {
        setTimeout(() => {
            notification.style.animation = 'slideUp 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    return notification;
}

// ============================================
// FORM VALIDATION
// ============================================

const FormValidator = {
    // Email validation
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    // Password strength (3+ chars - simplified)
    isValidPassword(password) {
        return password.length >= 3;
    },

    // Check password match
    passwordsMatch(pwd1, pwd2) {
        return pwd1 === pwd2;
    },

    // Validate required field
    isNotEmpty(value) {
        return value.trim().length > 0;
    },

    // Mark field as error
    markError(input, message = '') {
        const group = input.closest('.input-group');
        if (group) {
            group.classList.add('error');
            group.classList.remove('success');

            // Remove old error text
            const oldError = group.querySelector('.error-text');
            if (oldError) oldError.remove();

            // Add new error text
            if (message) {
                const errorEl = document.createElement('div');
                errorEl.className = 'error-text';
                errorEl.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                group.appendChild(errorEl);
            }
        }
    },

    // Mark field as success
    markSuccess(input) {
        const group = input.closest('.input-group');
        if (group) {
            group.classList.remove('error');
            group.classList.add('success');

            // Remove error text
            const errorEl = group.querySelector('.error-text');
            if (errorEl) errorEl.remove();
        }
    },

    // Clear all validations
    clearValidations(formElement) {
        const groups = formElement.querySelectorAll('.input-group');
        groups.forEach(group => {
            group.classList.remove('error', 'success');
            const errorEl = group.querySelector('.error-text');
            if (errorEl) errorEl.remove();
        });
    }
};

// ============================================
// FORM EVENT LISTENERS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Email input validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !FormValidator.isValidEmail(this.value)) {
                FormValidator.markError(this, 'Email inválido');
            } else if (this.value) {
                FormValidator.markSuccess(this);
            }
        });

        input.addEventListener('input', function() {
            const group = this.closest('.input-group');
            if (group && group.classList.contains('error')) {
                if (FormValidator.isValidEmail(this.value)) {
                    FormValidator.markSuccess(this);
                }
            }
        });
    });

    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const group = this.closest('.input-group');
            if (group && this.value) {
                if (FormValidator.isValidPassword(this.value)) {
                    FormValidator.markSuccess(this);
                } else if (this.value.length < 3) {
                    FormValidator.markError(this, 'Mínimo 3 caracteres');
                }
            }
        });
    });

    // Required field validation
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!FormValidator.isNotEmpty(this.value)) {
                FormValidator.markError(this, 'Este campo es requerido');
            }
        });

        input.addEventListener('input', function() {
            if (FormValidator.isNotEmpty(this.value)) {
                FormValidator.markSuccess(this);
            }
        });
    });

    // Form submit with validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');

            // Clear previous validations
            FormValidator.clearValidations(this);

            inputs.forEach(input => {
                if (!FormValidator.isNotEmpty(input.value)) {
                    FormValidator.markError(input, 'Este campo es requerido');
                    isValid = false;
                } else if (input.type === 'email' && !FormValidator.isValidEmail(input.value)) {
                    FormValidator.markError(input, 'Email inválido');
                    isValid = false;
                } else if (input.type === 'password' && !FormValidator.isValidPassword(input.value)) {
                    FormValidator.markError(input, 'Mínimo 3 caracteres');
                    isValid = false;
                } else {
                    FormValidator.markSuccess(input);
                }
            });

            // Check password match if there's a confirm password field
            const passwordField = this.querySelector('input[name="password"]');
            const confirmField = this.querySelector('input[name="password_confirm"]');
            if (passwordField && confirmField) {
                if (!FormValidator.passwordsMatch(passwordField.value, confirmField.value)) {
                    FormValidator.markError(confirmField, 'Las contraseñas no coinciden');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                showNotification('Por favor, completa el formulario correctamente', 'error');
            }
        });
    });

    // Real-time phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            // Format as: (XXX) XXX-XXXX
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + ' ' + value.slice(3);
                } else {
                    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 10);
                }
            }
            this.value = value;
        });
    });
});

// ============================================
// TOGGLE BUTTONS (Login/Register)
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const siblings = this.parentElement.querySelectorAll('.toggle-btn');
            siblings.forEach(sibling => sibling.classList.remove('active'));
            this.classList.add('active');

            // Trigger custom event for form switching
            const tabName = this.dataset.tab || this.getAttribute('data-tab');
            if (tabName) {
                const event = new CustomEvent('tabChanged', { detail: { tab: tabName } });
                document.dispatchEvent(event);
            }
        });
    });
});

// ============================================
// ACCESSIBILITY IMPROVEMENTS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Keyboard navigation for forms
    const inputs = document.querySelectorAll('input, select, textarea, button');
    inputs.forEach((input, index) => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                return; // Allow normal tab behavior
            }
            if (e.key === 'Enter' && (this.tagName !== 'TEXTAREA')) {
                e.preventDefault();
                const form = this.closest('form');
                if (form) {
                    const nextInput = inputs[Array.from(inputs).indexOf(this) + 1];
                    if (nextInput && nextInput.tagName === 'BUTTON') {
                        nextInput.click();
                    } else if (nextInput) {
                        nextInput.focus();
                    }
                }
            }
        });
    });

    // Focus visible for keyboard users
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('using-keyboard');
        }
    });

    document.addEventListener('mousedown', function() {
        document.body.classList.remove('using-keyboard');
    });
});

// ============================================
// SMOOTH SCROLL FOR ANCHORS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
});

// ============================================
// CSRF TOKEN HANDLING (if needed)
// ============================================

function getCSRFToken() {
    const token = document.querySelector('input[name="_token"]');
    return token ? token.value : '';
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function debounce(func, wait) {
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

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ============================================
// INIT SCRIPT
// ============================================

(function() {
    // Log script loaded
    console.log('Concelato Public Pages Script Loaded');

    // Set up animation styles
    if (!document.querySelector('style[data-script-animations]')) {
        const style = document.createElement('style');
        style.setAttribute('data-script-animations', 'true');
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
            @keyframes slideUp {
                from {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-20px);
                }
            }
            body.using-keyboard *:focus {
                outline: 2px solid var(--color-primary, #06b6d4);
                outline-offset: 2px;
            }
        `;
        document.head.appendChild(style);
    }
})();
