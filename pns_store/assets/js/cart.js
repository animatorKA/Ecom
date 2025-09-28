// Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    const cart = {
        init() {
            this.bindEvents();
        },

        bindEvents() {
            // Quantity adjustment buttons
            document.querySelectorAll('.qty-adjust').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const action = e.target.dataset.action;
                    const input = e.target.parentElement.querySelector('.qty-input');
                    const currentValue = parseInt(input.value);
                    
                    if (action === 'increase') {
                        input.value = Math.min(currentValue + 1, parseInt(input.max));
                    } else {
                        input.value = Math.max(currentValue - 1, parseInt(input.min));
                    }
                    
                    this.updateQuantity(input);
                });
            });

            // Manual quantity input
            document.querySelectorAll('.qty-input').forEach(input => {
                input.addEventListener('change', (e) => this.updateQuantity(e.target));
            });

            // Remove item buttons
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', (e) => this.removeItem(e.target.closest('tr')));
            });
        },

        updateQuantity(input) {
            const row = input.closest('tr');
            const productId = row.dataset.productId;
            const qty = parseInt(input.value);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch('update_cart_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&qty=${qty}&csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateCartDisplay(data);
                    // Update the cart count in the header if it exists
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                } else {
                    alert(data.message);
                    if (data.available_stock) {
                        input.value = data.available_stock;
                        this.updateQuantity(input);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the cart');
            });
        },

        removeItem(row) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            const productId = row.dataset.productId;

            fetch('remove_from_cart_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.remove();
                    this.updateCartDisplay(data);
                    // Update the cart count in the header if it exists
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    // If cart is empty, reload the page to show empty cart message
                    if (data.cart_count === 0) {
                        location.reload();
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the item');
            });
        },

        updateCartDisplay(data) {
            // Update total
            const totalElement = document.querySelector('.cart-total');
            if (totalElement) {
                totalElement.textContent = '₱' + parseFloat(data.total).toFixed(2);
            }

            // Enable/disable checkout button based on cart status
            const checkoutBtn = document.querySelector('a[href="checkout.php"]');
            if (checkoutBtn) {
                if (data.cart_count === 0) {
                    checkoutBtn.classList.add('disabled');
                } else {
                    checkoutBtn.classList.remove('disabled');
                }
            }
        }
    };

    // Initialize cart functionality
    cart.init();
});