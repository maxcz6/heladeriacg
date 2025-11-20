/**
 * Sistema de Modales Modernos para Cliente
 * Reemplaza los alert(), confirm() y prompt() nativos
 */

const ModalCliente = {
    /**
     * Muestra un modal de alerta
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
     * @param {string} title - Título del modal (opcional)
     */
    alert: function(message, type = 'info', title = null) {
        return new Promise((resolve) => {
            const overlay = this.createOverlay();
            const modal = this.createAlertModal(message, type, title);
            
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            setTimeout(() => overlay.classList.add('active'), 10);
            
            const closeModal = () => {
                overlay.classList.remove('active');
                setTimeout(() => {
                    document.body.removeChild(overlay);
                    resolve();
                }, 300);
            };
            
            const btnOk = modal.querySelector('.modal-alert-btn-primary');
            btnOk.addEventListener('click', closeModal);
            
            // Cerrar con ESC
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    closeModal();
                    document.removeEventListener('keydown', handleEsc);
                }
            };
            document.addEventListener('keydown', handleEsc);
        });
    },

    /**
     * Muestra un modal de confirmación
     * @param {string} message - Mensaje a mostrar
     * @param {string} title - Título del modal (opcional)
     */
    confirm: function(message, title = '¿Estás seguro?') {
        return new Promise((resolve) => {
            const overlay = this.createOverlay();
            const modal = this.createConfirmModal(message, title);
            
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            setTimeout(() => overlay.classList.add('active'), 10);
            
            const closeModal = (result) => {
                overlay.classList.remove('active');
                setTimeout(() => {
                    document.body.removeChild(overlay);
                    resolve(result);
                }, 300);
            };
            
            const btnConfirm = modal.querySelector('.btn-confirm');
            const btnCancel = modal.querySelector('.btn-cancel');
            
            btnConfirm.addEventListener('click', () => closeModal(true));
            btnCancel.addEventListener('click', () => closeModal(false));
            
            // Cerrar con ESC = cancelar
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    closeModal(false);
                    document.removeEventListener('keydown', handleEsc);
                }
            };
            document.addEventListener('keydown', handleEsc);
        });
    },

    /**
     * Muestra un modal de éxito
     */
    success: function(message, title = '¡Éxito!') {
        return this.alert(message, 'success', title);
    },

    /**
     * Muestra un modal de error
     */
    error: function(message, title = 'Error') {
        return this.alert(message, 'error', title);
    },

    /**
     * Muestra un modal de advertencia
     */
    warning: function(message, title = 'Advertencia') {
        return this.alert(message, 'warning', title);
    },

    /**
     * Muestra un modal de información
     */
    info: function(message, title = 'Información') {
        return this.alert(message, 'info', title);
    },

    /**
     * Muestra un modal de carga
     */
    loading: function(message = 'Procesando...') {
        const overlay = this.createOverlay();
        const modal = this.createLoadingModal(message);
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        setTimeout(() => overlay.classList.add('active'), 10);
        
        return {
            close: () => {
                overlay.classList.remove('active');
                setTimeout(() => {
                    if (document.body.contains(overlay)) {
                        document.body.removeChild(overlay);
                    }
                }, 300);
            }
        };
    },

    // Métodos auxiliares
    createOverlay: function() {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay-alert';
        return overlay;
    },

    createAlertModal: function(message, type, title) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const titles = {
            success: '¡Éxito!',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };

        const modal = document.createElement('div');
        modal.className = 'modal-alert';
        modal.innerHTML = `
            <div class="modal-alert-header">
                <div class="modal-alert-icon ${type}">
                    <i class="fas ${icons[type]}"></i>
                </div>
                <div class="modal-alert-title">
                    <h3>${title || titles[type]}</h3>
                </div>
            </div>
            <div class="modal-alert-body">
                <p class="modal-alert-message">${message}</p>
            </div>
            <div class="modal-alert-actions">
                <button class="modal-alert-btn modal-alert-btn-primary">
                    Aceptar
                </button>
            </div>
        `;
        return modal;
    },

    createConfirmModal: function(message, title) {
        const modal = document.createElement('div');
        modal.className = 'modal-alert modal-confirm';
        modal.innerHTML = `
            <div class="modal-alert-header">
                <div class="modal-alert-icon warning">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="modal-alert-title">
                    <h3>${title}</h3>
                </div>
            </div>
            <div class="modal-alert-body">
                <p class="modal-alert-message">${message}</p>
            </div>
            <div class="modal-alert-actions">
                <button class="modal-alert-btn modal-alert-btn-secondary btn-cancel">
                    Cancelar
                </button>
                <button class="modal-alert-btn modal-alert-btn-primary btn-confirm">
                    Confirmar
                </button>
            </div>
        `;
        return modal;
    },

    createLoadingModal: function(message) {
        const modal = document.createElement('div');
        modal.className = 'modal-alert modal-loading';
        modal.innerHTML = `
            <div class="modal-spinner"></div>
            <p class="modal-alert-message">${message}</p>
        `;
        return modal;
    }
};

// Hacer disponible globalmente
window.ModalCliente = ModalCliente;
