/**
 * EMPLOYEE PANEL - Interactive Script
 * Minimalista Design + Responsive Functionality
 */

// ============================================
// MENU TOGGLE - Mobile Navigation
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const employeeHeader = document.querySelector('.employee-header');
    const navLinks = document.querySelectorAll('nav a');

    // Toggle menu on hamburger click
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            employeeHeader.classList.toggle('nav-open');
        });
    }

    // Close menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            employeeHeader.classList.remove('nav-open');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (employeeHeader && employeeHeader.classList.contains('nav-open')) {
            if (!employeeHeader.contains(e.target)) {
                employeeHeader.classList.remove('nav-open');
            }
        }
    });

    // Close menu on window resize (when reaching desktop breakpoint)
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            if (employeeHeader) {
                employeeHeader.classList.remove('nav-open');
            }
        }
    });
});

// ============================================
// PRODUCT CART FUNCTIONALITY
// ============================================
function addToCart(productId, productName, price) {
    const quantity = parseInt(document.getElementById(`qty-${productId}`).value) || 1;
    
    // Check if item already in cart
    const cartItems = document.querySelectorAll('.cart-item');
    let itemExists = false;

    cartItems.forEach(item => {
        if (item.dataset.productId === productId.toString()) {
            const currentQty = parseInt(item.querySelector('.item-quantity').textContent);
            item.querySelector('.item-quantity').textContent = currentQty + quantity;
            itemExists = true;
        }
    });

    if (!itemExists) {
        const cartItemsContainer = document.querySelector('.cart-items');
        const newItem = document.createElement('div');
        newItem.className = 'cart-item';
        newItem.dataset.productId = productId;
        newItem.innerHTML = `
            <div class="item-info">
                <h4>${productName}</h4>
                <p>$${price}</p>
            </div>
            <div class="item-total">
                <button class="quantity-btn" onclick="decreaseQuantity(${productId})">-</button>
                <span class="item-quantity">${quantity}</span>
                <button class="quantity-btn" onclick="increaseQuantity(${productId})">+</button>
                <button class="remove-btn" onclick="removeFromCart(${productId})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        cartItemsContainer.appendChild(newItem);
    }

    updateCartTotal();
}

function increaseQuantity(productId) {
    const item = document.querySelector(`[data-product-id="${productId}"]`);
    if (item) {
        const qtySpan = item.querySelector('.item-quantity');
        qtySpan.textContent = parseInt(qtySpan.textContent) + 1;
        updateCartTotal();
    }
}

function decreaseQuantity(productId) {
    const item = document.querySelector(`[data-product-id="${productId}"]`);
    if (item) {
        const qtySpan = item.querySelector('.item-quantity');
        const qty = parseInt(qtySpan.textContent);
        if (qty > 1) {
            qtySpan.textContent = qty - 1;
            updateCartTotal();
        }
    }
}

function removeFromCart(productId) {
    const item = document.querySelector(`[data-product-id="${productId}"]`);
    if (item) {
        item.remove();
        updateCartTotal();
    }
}

function updateCartTotal() {
    const items = document.querySelectorAll('.cart-item');
    let total = 0;

    items.forEach(item => {
        const price = parseFloat(item.querySelector('.item-info p').textContent.replace('$', ''));
        const qty = parseInt(item.querySelector('.item-quantity').textContent);
        total += price * qty;
    });

    const totalElement = document.querySelector('.cart-total h3');
    if (totalElement) {
        totalElement.textContent = `Total: $${total.toFixed(2)}`;
    }
}

// ============================================
// ACCESSIBILITY ENHANCEMENTS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button, .action-btn, .btn');
    
    buttons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
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
