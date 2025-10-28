/*
 * Main JavaScript file for SecureShop
 * ====================================
 * Contains:
 * 1. "Added to Cart" Toast Notification Handler
 * 2. "Special Offer" Countdown Timer
 * 3. Full AJAX Cart System
 */

// Wait for the DOM to be fully loaded before running scripts
document.addEventListener('DOMContentLoaded', () => {

    /*
     * 1. Toast Notification Handler
     */
    const toast = document.getElementById('toast-notification');
    
    // Check if the toast element exists and has the 'data-show="true"' attribute
    if (toast && toast.dataset.show === 'true') {
        // Use the showToast function to display it
        showToast('Product added to cart!');
        
        // Clean up the URL (optional, good practice)
        window.history.replaceState(null, null, window.location.pathname);
    }


    /*
     * 2. "Special Offer" Countdown Timer
     */
    const countdownElement = document.getElementById('countdown-timer');
    
    if (countdownElement && typeof countdownTargetDate !== 'undefined') {
        const targetTime = new Date(countdownTargetDate).getTime();
        const countdownInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = targetTime - now;
            
            if (distance < 0) {
                clearInterval(countdownInterval);
                countdownElement.innerHTML = "EXPIRED";
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const format = (num) => num < 10 ? '0' + num : num;

            document.getElementById('days').innerText = format(days);
            document.getElementById('hours').innerText = format(hours);
            document.getElementById('minutes').innerText = format(minutes);
            document.getElementById('seconds').innerText = format(seconds);
            
        }, 1000);
    }
    
    /*
     * 3. AJAX Cart System
     */

    // --- Handle "Add to Cart" from product grids ---
    document.querySelectorAll('.ajax-add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop page reload
            
            const button = this.querySelector('.add-to-cart-btn');
            const productId = button.dataset.id;
            const quantityInput = this.querySelector('input[name="quantity"]');
            const quantity = quantityInput ? quantityInput.value : '1';
            
            const originalButtonText = button.textContent;
            button.textContent = 'Adding...';
            button.disabled = true;
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    button.textContent = 'Added!';
                    updateCartCount(data.cart_count);
                    // Reset button text after a delay
                    setTimeout(() => { 
                        button.textContent = originalButtonText;
                        button.disabled = false;
                    }, 2000);
                } else {
                    showToast(data.message, 'error');
                    button.textContent = originalButtonText;
                    button.disabled = false;
                }
            })
            .catch(() => {
                showToast('Network error.', 'error');
                button.textContent = originalButtonText;
                button.disabled = false;
            });
        });
    });

    // --- Handle Cart Page (+, -, Remove) ---
    const cartContent = document.getElementById('cart-content');
    if (cartContent) {
        cartContent.addEventListener('click', e => {
            // Check for quantity button clicks
            if (e.target.classList.contains('btn-quantity')) {
                const id = e.target.dataset.id;
                const action = e.target.dataset.action;
                updateCartItem(id, action, e.target);
            }
            // Check for remove button clicks
            if (e.target.classList.contains('btn-remove')) {
                const id = e.target.dataset.id;
                removeCartItem(id);
            }
        });
    }
});

// --- Helper Functions ---

/**
 * Updates an item's quantity in the cart via AJAX.
 */
function updateCartItem(productId, action, button) {
    // Disable button to prevent spam
    button.disabled = true;

    fetch('update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ product_id: productId, action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            
            const row = document.querySelector(`#cart-content tr[data-product-id="${productId}"]`);
            if (!row) return;

            const quantitySpan = row.querySelector('.cart-quantity span');
            let quantity = parseInt(quantitySpan.textContent);

            if (action === 'increase') {
                quantity++;
            } else {
                quantity--;
            }

            if (quantity <= 0) {
                row.remove(); // Remove row if quantity is 0
            } else {
                // Update quantity
                quantitySpan.textContent = quantity;
                
                // Update subtotal
                const priceCell = row.cells[1]; // Assuming price is 2nd cell
                const price = parseFloat(priceCell.textContent.replace('$', ''));
                const subtotalCell = row.querySelector('.cart-subtotal');
                subtotalCell.textContent = '$' + (price * quantity).toFixed(2);
            }
            
            // Recalculate and update grand total
            updateGrandTotal();
        } else {
            showToast(data.message, 'error');
            button.disabled = false; // Re-enable only on error
        }
    })
    .catch(() => {
        showToast('Network error.', 'error');
        button.disabled = false;
    });
}

/**
 * Removes an item from the cart via AJAX.
 */
function removeCartItem(productId) {
    if (!confirm('Are you sure you want to remove this item?')) return;
    
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            
            const row = document.querySelector(`#cart-content tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }
            
            updateGrandTotal();
            showToast('Item removed from cart.');
        } else {
            showToast('Failed to remove item.', 'error');
        }
    });
}

/**
 * Recalculates the grand total on the cart page.
 */
function updateGrandTotal() {
    const totalEl = document.querySelector('.cart-total');
    if (!totalEl) return;
    
    let grandTotal = 0;
    const subtotals = document.querySelectorAll('.cart-subtotal');
    
    if (subtotals.length === 0) {
        // Cart is now empty
        const cartContent = document.getElementById('cart-content');
        cartContent.innerHTML = '<p>Your cart is empty!</p>';
    } else {
        subtotals.forEach(cell => {
            grandTotal += parseFloat(cell.textContent.replace('$', ''));
        });
        totalEl.textContent = 'Total: $' + grandTotal.toFixed(2);
    }
}

/**
 * Shows the toast notification.
 */
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;

    toast.textContent = message;
    toast.className = 'toast show';
    
    if (type === 'error') {
        toast.style.backgroundColor = '#c51212';
    } else {
        toast.style.backgroundColor = '#333';
    }

    setTimeout(() => {
        toast.className = toast.className.replace('show', '');
    }, 3000);
}

/**
 * Updates the cart count bubble in the header.
 */
function updateCartCount(count) {
    const cartLink = document.querySelector('.nav-links a[href="cart.php"]');
    if (!cartLink) return;

    // Find or create the bubble
    let bubble = document.getElementById('cart-count-bubble');
    if (!bubble && cartLink.parentElement.tagName === 'LI') {
        bubble = document.createElement('span');
        bubble.id = 'cart-count-bubble';
        cartLink.parentElement.appendChild(bubble);
    }

    if (bubble) {
        if (count > 0) {
            bubble.textContent = count;
            bubble.classList.add('visible');
        } else {
            bubble.classList.remove('visible');
        }
    }
}