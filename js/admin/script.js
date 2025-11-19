/**
 * ADMIN PANEL - Interactive Script
 * Minimalista Design + Responsive Functionality + WCAG 2.1 AA Accessibility
 * Menu Toggle, Form Validation, Modal Management, Notifications
 */

// ============================================
// UTILITY FUNCTIONS
// ============================================
const Utils = {
    debounce: (fn, delay = 300) => {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    },
    
    throttle: (fn, delay = 300) => {
        let lastCall = 0;
        return function(...args) {
            const now = Date.now();
            if (now - lastCall >= delay) {
                lastCall = now;
                return fn.apply(this, args);
            }
        };
    },

    announce: (message, priority = 'polite') => {
        const announcement = document.createElement('div');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', priority);
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        setTimeout(() => announcement.remove(), 1000);
    }
};

// ============================================
// MENU TOGGLE - Mobile Navigation with A11y
// ============================================
const MenuToggle = {
    init: function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const adminHeader = document.querySelector('.admin-header');
        const navLinks = document.querySelectorAll('nav a');

        if (!menuToggle) return;

        // Ensure proper ARIA attributes
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Alternar menú de navegación');
        menuToggle.setAttribute('aria-controls', 'admin-nav');

        // Toggle menu on hamburger click
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle(menuToggle, adminHeader);
        });

        // Close menu when a link is clicked (but keep expanded state in aria)
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                adminHeader.classList.remove('nav-open');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (adminHeader && adminHeader.classList.contains('nav-open')) {
                if (!adminHeader.contains(e.target)) {
                    adminHeader.classList.remove('nav-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });

        // Close menu on window resize (when reaching desktop breakpoint)
        window.addEventListener('resize', Utils.debounce(() => {
            if (window.innerWidth > 1024) {
                if (adminHeader) {
                    adminHeader.classList.remove('nav-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        }, 150));

        // ESC key support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && adminHeader.classList.contains('nav-open')) {
                adminHeader.classList.remove('nav-open');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.focus();
            }
        });
    },

    toggle: function(menuToggle, adminHeader) {
        const isOpen = adminHeader.classList.toggle('nav-open');
        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        Utils.announce(isOpen ? 'Menú abierto' : 'Menú cerrado');
    }
};

// ============================================
// FORM VALIDATOR - With Real-time Feedback
// ============================================
const FormValidator = {
    validators: {
        required: (value) => value.trim() !== '',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        phone: (value) => /^[\d\s\-\+\(\)]+$/.test(value) && value.trim().length >= 8,
        minLength: (value, length) => value.length >= length,
        maxLength: (value, length) => value.length <= length,
        number: (value) => !isNaN(value) && value.trim() !== ''
    },

    init: function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            this.setupForm(form);
        });
    },

    setupForm: function(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            // Ensure label is associated
            const label = form.querySelector(`label[for="${input.id}"]`);
            if (!label && !input.getAttribute('aria-label')) {
                input.setAttribute('aria-label', input.name || 'Campo requerido');
            }

            // Add ARIA attributes
            if (input.hasAttribute('required')) {
                const label = form.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    label.setAttribute('aria-required', 'true');
                }
            }

            // Real-time validation feedback
            input.addEventListener('blur', () => {
                this.validateInput(input);
            });

            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateInput(input);
                }
            });
        });

        // Form submission validation
        form.addEventListener('submit', (e) => {
            const isValid = this.validateFormSubmit(form);
            if (!isValid) {
                e.preventDefault();
                Utils.announce('Por favor, corrija los errores en el formulario');
            }
        });
    },

    validateInput: function(input) {
        const value = input.value;
        const isRequired = input.hasAttribute('required');
        const type = input.getAttribute('type') || input.tagName.toLowerCase();
        
        let isValid = true;

        if (isRequired && !this.validators.required(value)) {
            isValid = false;
        } else if (value && type === 'email') {
            isValid = this.validators.email(value);
        } else if (value && type === 'tel') {
            isValid = this.validators.phone(value);
        } else if (value && type === 'number') {
            isValid = this.validators.number(value);
        }

        if (isValid) {
            this.setValid(input);
        } else {
            this.setInvalid(input);
        }

        return isValid;
    },

    validateFormSubmit: function(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });

        return isValid;
    },

    setValid: function(input) {
        input.classList.remove('error');
        input.classList.add('success');
        input.setAttribute('aria-invalid', 'false');
        
        // Remove error message if exists
        const errorMsg = input.parentElement.querySelector('.form-error');
        if (errorMsg) {
            errorMsg.remove();
        }
    },

    setInvalid: function(input) {
        input.classList.remove('success');
        input.classList.add('error');
        input.setAttribute('aria-invalid', 'true');

        // Add error message if not exists
        if (!input.parentElement.querySelector('.form-error')) {
            const errorMsg = document.createElement('small');
            errorMsg.className = 'form-error';
            errorMsg.setAttribute('role', 'alert');
            errorMsg.textContent = this.getErrorMessage(input);
            input.parentElement.appendChild(errorMsg);
        }
    },

    getErrorMessage: function(input) {
        const type = input.getAttribute('type') || input.tagName.toLowerCase();
        if (!input.value.trim()) return 'Este campo es requerido';
        if (type === 'email') return 'Ingrese un correo válido';
        if (type === 'tel') return 'Ingrese un teléfono válido';
        if (type === 'number') return 'Ingrese un número válido';
        return 'Campo inválido';
    }
};

// ============================================
// MODAL MANAGER - With Focus Trap & A11y
// ============================================
const ModalManager = {
    openModals: [],

    init: function() {
        const modals = document.querySelectorAll('.modal, [role="dialog"]');
        modals.forEach(modal => {
            this.setupModal(modal);
        });
    },

    setupModal: function(modal) {
        // Ensure ARIA attributes
        if (!modal.getAttribute('role')) {
            modal.setAttribute('role', 'dialog');
        }
        modal.setAttribute('aria-modal', 'true');

        const closeBtn = modal.querySelector('.close, [aria-label*="Cerrar"]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.closeModal(modal);
            });
        }

        // Close on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal(modal);
            }
        });

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.openModals.includes(modal)) {
                this.closeModal(modal);
            }
        });
    },

    openModal: function(modalId) {
        const modal = document.getElementById(modalId) || document.querySelector(`[data-modal="${modalId}"]`);
        if (!modal) return;

        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        this.openModals.push(modal);

        // Focus trap
        this.trapFocus(modal);

        // Set initial focus to first focusable element or close button
        const firstFocusable = modal.querySelector('input, button, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            setTimeout(() => firstFocusable.focus(), 100);
        }

        Utils.announce('Diálogo abierto');
    },

    closeModal: function(modal) {
        if (!modal) return;
        
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        
        const index = this.openModals.indexOf(modal);
        if (index > -1) {
            this.openModals.splice(index, 1);
        }

        // Return focus to trigger element (if saved)
        const triggerBtn = document.querySelector(`[data-modal="${modal.id}"]`);
        if (triggerBtn) {
            triggerBtn.focus();
        }

        Utils.announce('Diálogo cerrado');
    },

    trapFocus: function(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        modal.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    }
};

function openModal(modalId) {
    ModalManager.openModal(modalId);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        ModalManager.closeModal(modal);
    }
}

// ============================================
// CONFIRM DELETE ACTION
// ============================================
function confirmDelete(message = '¿Está seguro de que desea eliminar este elemento?') {
    return confirm(message);
}

// ============================================
// TABLE SEARCH & FILTER with Accessibility
// ============================================
const TableManager = {
    init: function() {
        const searchInputs = document.querySelectorAll('[data-filter-table]');
        searchInputs.forEach(input => {
            const tableId = input.getAttribute('data-filter-table');
            this.setupFilter(input, tableId);
        });
    },

    setupFilter: function(input, tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        input.setAttribute('aria-label', `Filtrar tabla ${tableId}`);
        input.addEventListener('keyup', Utils.debounce(() => {
            this.filterTable(input, table);
        }, 300));
    },

    filterTable: function(input, table) {
        const filter = input.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(filter);
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Announce results to screen readers
        if (visibleCount === 0) {
            Utils.announce('Sin resultados encontrados');
        } else {
            Utils.announce(`Se encontraron ${visibleCount} resultado(s)`);
        }
    }
};

function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    if (input) {
        TableManager.setupFilter(input, tableId);
    }
}

// ============================================
// TABLE SORTING with Accessibility
// ============================================
const TableSorter = {
    init: function() {
        const sortableHeaders = document.querySelectorAll('[role="columnheader"][aria-sort], th[aria-sort]');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', (e) => {
                this.sortTable(e.currentTarget);
            });

            // Keyboard support for sorting
            header.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.sortTable(e.currentTarget);
                }
            });

            header.setAttribute('tabindex', '0');
        });
    },

    sortTable: function(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const sortDirection = header.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';

        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();

            if (isNaN(aValue) || isNaN(bValue)) {
                return sortDirection === 'ascending' 
                    ? aValue.localeCompare(bValue)
                    : bValue.localeCompare(aValue);
            }

            return sortDirection === 'ascending'
                ? aValue - bValue
                : bValue - aValue;
        });

        // Update DOM
        rows.forEach(row => tbody.appendChild(row));

        // Update aria-sort attributes
        table.querySelectorAll('th[aria-sort], [role="columnheader"][aria-sort]').forEach(h => {
            if (h === header) {
                h.setAttribute('aria-sort', sortDirection);
            } else {
                h.setAttribute('aria-sort', 'none');
            }
        });

        Utils.announce(`Tabla ordenada por ${header.textContent} en orden ${sortDirection === 'ascending' ? 'ascendente' : 'descendente'}`);
    }
};

// ============================================
// EXPORT FUNCTIONALITY
// ============================================
function exportToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        let csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(csvRow.join(','));
    });

    downloadCSV(csv.join('\n'), filename);
    Utils.announce(`Tabla exportada como ${filename}`);
}

function downloadCSV(csv, filename) {
    const link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    link.download = filename;
    link.click();
}

// ============================================
// NOTIFICATION SYSTEM with A11y
// ============================================
const NotificationManager = {
    container: null,

    init: function() {
        // Create notification container if doesn't exist
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.setAttribute('role', 'region');
            this.container.setAttribute('aria-live', 'polite');
            this.container.setAttribute('aria-atomic', 'true');
            this.container.style.position = 'fixed';
            this.container.style.top = '20px';
            this.container.style.right = '20px';
            this.container.style.zIndex = '9999';
            this.container.style.maxWidth = '400px';
            document.body.appendChild(this.container);
        }
    },

    show: function(message, type = 'success', duration = 4000) {
        this.init();

        const notification = document.createElement('div');
        notification.className = `mensaje ${type}`;
        notification.setAttribute('role', type === 'error' ? 'alert' : 'status');
        notification.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
        notification.setAttribute('aria-atomic', 'true');
        notification.innerText = message;
        notification.style.animation = 'slideIn 0.3s ease-out';

        this.container.appendChild(notification);

        if (duration > 0) {
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }

        return notification;
    }
};

function showNotification(message, type = 'success', duration = 4000) {
    return NotificationManager.show(message, type, duration);
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================
const KeyboardShortcuts = {
    init: function() {
        document.addEventListener('keydown', (e) => {
            // Alt+S for search
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                const searchInput = document.querySelector('[data-filter-table], input[type="search"]');
                if (searchInput) {
                    searchInput.focus();
                    Utils.announce('Enfoque en búsqueda');
                }
            }

            // Alt+C for create/add button
            if (e.altKey && e.key === 'c') {
                e.preventDefault();
                const createBtn = document.querySelector('[data-action="create"], .btn-primary');
                if (createBtn) {
                    createBtn.click();
                    Utils.announce('Formulario de creación abierto');
                }
            }

            // Alt+E for export
            if (e.altKey && e.key === 'e') {
                e.preventDefault();
                const exportBtn = document.querySelector('[data-action="export"]');
                if (exportBtn) {
                    exportBtn.click();
                    Utils.announce('Exportando datos');
                }
            }
        });
    }
};

// ============================================
// ANIMATION & SMOOTH SCROLLING
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    MenuToggle.init();
    FormValidator.init();
    ModalManager.init();
    TableManager.init();
    TableSorter.init();
    KeyboardShortcuts.init();
    NotificationManager.init();

    // Add keyboard navigation for buttons
    const buttons = document.querySelectorAll('button, .action-btn, .btn');
    buttons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Keyboard indicator for focus management
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-nav');
        }
    });

    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-nav');
    });

    // Load animation keyframes
    injectAnimationStyles();
});

// ============================================
// ANIMATION KEYFRAMES INJECTION
// ============================================
function injectAnimationStyles() {
    if (document.querySelector('#admin-animations')) return;

    const style = document.createElement('style');
    style.id = 'admin-animations';
    style.innerHTML = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        input.error,
        textarea.error,
        select.error {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
        }

        input.success,
        textarea.success,
        select.success {
            border-color: #10b981 !important;
            background: #f0fdf4 !important;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }

        body.keyboard-nav *:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    `;
    document.head.appendChild(style);
}
