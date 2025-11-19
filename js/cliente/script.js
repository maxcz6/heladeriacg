/**
 * CLIENT PANEL - Interactive Script
 * Minimalista Design + Responsive Functionality
 */

// ============================================
// MENU TOGGLE - Mobile Navigation
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const clientHeader = document.querySelector('.client-header');
    const navLinks = document.querySelectorAll('nav a');

    // Toggle menu on hamburger click
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            clientHeader.classList.toggle('nav-open');
        });
    }

    // Close menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            clientHeader.classList.remove('nav-open');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (clientHeader && clientHeader.classList.contains('nav-open')) {
            if (!clientHeader.contains(e.target)) {
                clientHeader.classList.remove('nav-open');
            }
        }
    });

    // Close menu on window resize (when reaching desktop breakpoint)
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            if (clientHeader) {
                clientHeader.classList.remove('nav-open');
            }
        }
    });
});

// ============================================
// ORDER FILTERING & SEARCH
// ============================================
function filterOrders() {
    const searchInput = document.querySelector('.search-filter input');
    const statusSelect = document.querySelector('.search-filter select');
    
    if (!searchInput || !statusSelect) return;

    const orders = document.querySelectorAll('.order-card');
    const searchTerm = searchInput.value.toLowerCase();
    const statusFilter = statusSelect.value;

    orders.forEach(order => {
        const orderText = order.textContent.toLowerCase();
        const orderStatus = order.querySelector('.status-tag')?.className;
        
        const matchesSearch = orderText.includes(searchTerm);
        const matchesStatus = !statusFilter || orderStatus.includes(statusFilter);

        order.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

// ============================================
// ORDER STATUS TRACKING
// ============================================
function updateOrderStatus(orderId, newStatus) {
    fetch(`/api/orders/${orderId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        showNotification('Estado del pedido actualizado', 'success');
        location.reload();
    })
    .catch(error => {
        showNotification('Error al actualizar el pedido', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// DELIVERY OPTIONS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const deliveryOptions = document.querySelectorAll('.delivery-option');
    
    deliveryOptions.forEach(option => {
        option.addEventListener('click', function() {
            deliveryOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});

// ============================================
// ORDER FORM SUBMISSION
// ============================================
function submitOrder(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    if (!isValid) {
        showNotification('Por favor completa todos los campos requeridos', 'error');
        return false;
    }

    return true;
}

// ============================================
// ACCESSIBILITY ENHANCEMENTS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button, .action-btn, .btn, .order-btn');
    
    buttons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Form field error handling
    const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });

        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('error');
            }
        });
    });
});

// ============================================
// NOTIFICATION SYSTEM
// ============================================
function showNotification(message, type = 'success', duration = 4000) {
    const notification = document.createElement('div');
    notification.className = `mensaje ${type}`;
    notification.innerText = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '400px';
    notification.style.animation = 'slideIn 0.3s ease-out';

    document.body.appendChild(notification);

    if (duration > 0) {
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    return notification;
}

// ============================================
// SMOOTH SCROLLING
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
