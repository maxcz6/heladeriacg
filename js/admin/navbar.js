/**
 * NAVBAR MEJORADA - Control interactivo
 * Funcionalidades: Toggle menu, ESC key, click outside, resize, smooth transitions
 */

const NavbarController = {
    /**
     * Inicializar controlador de navbar
     */
    init: function() {
        this.elements = {
            menuToggle: document.querySelector('.menu-toggle'),
            adminHeader: document.querySelector('.admin-header'),
            nav: document.querySelector('nav'),
            navLinks: document.querySelectorAll('nav a, nav li a'),
            logo: document.querySelector('.logo'),
            logoutBtn: document.querySelector('.logout-btn')
        };

        // Validar que existan elementos necesarios
        if (!this.elements.menuToggle || !this.elements.adminHeader) {
            console.warn('NavbarController: Elementos requeridos no encontrados');
            return;
        }

        this.setARIA();
        this.attachEventListeners();
        this.highlightActiveLink();
    },

    /**
     * Establecer atributos ARIA para accesibilidad
     */
    setARIA: function() {
        this.elements.menuToggle.setAttribute('aria-expanded', 'false');
        this.elements.menuToggle.setAttribute('aria-label', 'Alternar menú de navegación');
        this.elements.menuToggle.setAttribute('aria-controls', 'admin-nav');
        
        if (this.elements.nav) {
            this.elements.nav.setAttribute('id', 'admin-nav');
            this.elements.nav.setAttribute('role', 'navigation');
            this.elements.nav.setAttribute('aria-label', 'Navegación principal');
        }
    },

    /**
     * Adjuntar event listeners
     */
    attachEventListeners: function() {
        // Toggle menú al hacer click en hamburguesa
        this.elements.menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleMenu();
        });

        // Cerrar menú al hacer click en un link
        this.elements.navLinks.forEach(link => {
            link.addEventListener('click', () => {
                this.closeMenu();
            });
        });

        // Cerrar menú al hacer click fuera
        document.addEventListener('click', (e) => {
            if (this.isMenuOpen() && !this.elements.adminHeader.contains(e.target)) {
                this.closeMenu();
            }
        });

        // Cerrar menú al presionar ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen()) {
                this.closeMenu();
                this.elements.menuToggle.focus();
            }
        });

        // Cerrar menú al cambiar tamaño de ventana (llegar a breakpoint desktop)
        window.addEventListener('resize', this.debounce(() => {
            if (window.innerWidth > 1024 && this.isMenuOpen()) {
                this.closeMenu();
            }
        }, 150));

        // Highlight link activo cuando se carga la página
        window.addEventListener('load', () => {
            this.highlightActiveLink();
        });
    },

    /**
     * Toggle menú (abrir/cerrar)
     */
    toggleMenu: function() {
        if (this.isMenuOpen()) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    },

    /**
     * Abrir menú
     */
    openMenu: function() {
        this.elements.adminHeader.classList.add('nav-open');
        this.elements.menuToggle.classList.add('active');
        this.elements.menuToggle.setAttribute('aria-expanded', 'true');
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Anunciar para screen readers
        this.announce('Menú de navegación abierto');
    },

    /**
     * Cerrar menú
     */
    closeMenu: function() {
        this.elements.adminHeader.classList.remove('nav-open');
        this.elements.menuToggle.classList.remove('active');
        this.elements.menuToggle.setAttribute('aria-expanded', 'false');
        
        // Restaurar scroll del body
        document.body.style.overflow = '';
        
        // Anunciar para screen readers
        this.announce('Menú de navegación cerrado');
    },

    /**
     * Verificar si menú está abierto
     */
    isMenuOpen: function() {
        return this.elements.adminHeader.classList.contains('nav-open');
    },

    /**
     * Resaltar link activo basado en la URL actual
     */
    highlightActiveLink: function() {
        const currentPath = window.location.pathname;
        const currentPage = currentPath.split('/').pop() || 'index.php';

        this.elements.navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            
            // Comparar URLs
            if (href && (href.includes(currentPage) || 
                (currentPage === '' && href === 'index.php') ||
                href === currentPage.replace('admin/', ''))) {
                link.classList.add('active');
            }
        });
    },

    /**
     * Anunciar cambios a screen readers
     */
    announce: function(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        // Remover después de que se lea
        setTimeout(() => announcement.remove(), 1000);
    },

    /**
     * Debounce función para resize
     */
    debounce: function(fn, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    }
};

/**
 * Inicializar cuando DOM esté listo
 */
document.addEventListener('DOMContentLoaded', () => {
    NavbarController.init();
});

/**
 * Reinicializar si el contenido cambia dinámicamente
 */
if (window.MutationObserver) {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList' && mutation.target.classList.contains('admin-header')) {
                NavbarController.init();
            }
        });
    });

    const config = { childList: true, subtree: true };
    const header = document.querySelector('.admin-header');
    if (header) {
        observer.observe(header, config);
    }
}

// Exportar para uso global si es necesario
window.NavbarController = NavbarController;
