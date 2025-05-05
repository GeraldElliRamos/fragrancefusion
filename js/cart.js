// cart.js - Handles cart functionality with API integration
const cartItemsList = document.getElementById('cart-items-list');
const subtotalElement = document.getElementById('subtotal');
const totalElement = document.getElementById('total');
const emptyCartMessage = '<p id="empty-cart-message">Your cart is empty.</p>';

// Variables to store session info for JS
const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']); ?>;
const userId = <?php echo json_encode(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null); ?>;
const debugInfo = <?php echo $debugJson; ?>;

console.log('Cart Debug Info:', debugInfo);

// First-load initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing cart');
    // If not logged in or no server-side items were loaded, check localStorage
    if (!isLoggedIn || (isLoggedIn && debugInfo.db_items_found === 0)) {
        loadCartFromLocalStorage();
    }

    // Update the year in footer
    document.getElementById('current-year').textContent = new Date().getFullYear();
});

// Add this helper function to your cart.js file
function fixImagePath(path) {
    if (!path) return '../assets/product-placeholder.png';
    
    // Fix double path issues
    path = path.replace(/\.\.\/\.\.\/assets/, '../assets');
    
    // Ensure path starts correctly
    if (path.indexOf('../assets') === -1 && path.indexOf('http') === -1) {
        path = '../assets/' + path.split('/').pop();
    }
    
    return path;
}

/**
 * Load cart items from localStorage and display them
 */
function loadCartFromLocalStorage() {
    console.log('Loading cart from localStorage');
    try {
        // Get items from localStorage
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        console.log('Local storage cart items:', cartItems);
        
        // Get the container for cart items
        const cartItemsList = document.getElementById('cart-items-list');
        
        // If no items, show message
        if (cartItems.length === 0) {
            cartItemsList.innerHTML = '<div class="empty-cart-message">Your cart is empty.</div>';
            updateSubtotal(0);
            return;
        }
        
        // Clear any existing items if we're reloading
        if (!isLoggedIn) {
            cartItemsList.innerHTML = '';
        }
        
        // Calculate subtotal
        let subtotal = 0;
        
        // Add each item to the cart display
        cartItems.forEach(item => {
            // Skip if this item already exists in the DOM (for logged-in users whose items were server-rendered)
            if (document.querySelector(`.cart-item[data-id="${item.product_id}"]`)) {
                return;
            }
            
            // Calculate item total and add to subtotal
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            // Fix image path
            const imagePath = fixImagePath(item.image_url);
            
            // Create cart item element
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.dataset.id = item.product_id;
            
            itemElement.innerHTML = `
                <div class="cart-item-image">
                    <img src="${imagePath}" alt="${item.product_name}">
                </div>
                <div class="cart-item-details">
                    <h6>${item.product_name}</h6>
                    <p>$${item.price.toFixed(2)}</p>
                    <div class="quantity-controls">
                        <button onclick="updateQuantity(${item.product_id}, -1)">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button onclick="updateQuantity(${item.product_id}, 1)">+</button>
                    </div>
                </div>
                <button class="remove-btn" onclick="removeItem(${item.product_id})">×</button>
            `;
            
            // Add to cart
            cartItemsList.appendChild(itemElement);
        });
        
        // Update subtotal display
        updateSubtotal(subtotal);
    } catch (error) {
        console.error('Error loading cart from localStorage', error);
        document.getElementById('cart-items-list').innerHTML = 
            '<div class="alert alert-danger">There was an error loading your cart. Please try refreshing the page.</div>';
    }
}

// Update item quantity
function updateQuantity(productId, change) {
    console.log(`Updating quantity for product ${productId} by ${change}`);
    
    if (isLoggedIn) {
        // Update in database via API
        fetch('cart_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity_change: change
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update quantity display
                updateItemQuantityDisplay(productId, data.new_quantity);
                // Update subtotal
                updateSubtotal(data.new_total);
            } else {
                console.error('Failed to update item:', data.message);
                alert('Could not update item: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error updating item:', error);
            alert('Error updating item. Please try again.');
        });
    } else {
        // Update in localStorage
        try {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const itemIndex = cartItems.findIndex(item => item.product_id == productId);
            
            if (itemIndex > -1) {
                // Update quantity
                cartItems[itemIndex].quantity += change;
                
                // Remove item if quantity reaches 0
                if (cartItems[itemIndex].quantity <= 0) {
                    cartItems.splice(itemIndex, 1);
                }
                
                // Save updated cart
                localStorage.setItem('cart', JSON.stringify(cartItems));
                
                // Reload cart display
                loadCartFromLocalStorage();
            }
        } catch (e) {
            console.error('Error updating quantity in localStorage:', e);
            alert('Could not update item quantity');
        }
    }
}

// Remove item from cart
function removeItem(productId) {
    if (isLoggedIn) {
        // Remove from database
        fetch('cart_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from display
                const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (item) item.remove();
                // Update totals
                updateSubtotal(data.new_total);
            } else {
                console.error('Failed to remove item:', data.message);
                alert('Could not remove item: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            alert('Error removing item. Please try again.');
        });
    } else {
        // Remove from localStorage
        try {
            let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            cartItems = cartItems.filter(item => item.product_id != productId);
            localStorage.setItem('cart', JSON.stringify(cartItems));
            // Reload cart display
            loadCartFromLocalStorage();
        } catch (e) {
            console.error('Error removing item from localStorage:', e);
            alert('Could not remove item');
        }
    }
}

// Remove all items from cart
function removeAllItems() {
    if (confirm('Are you sure you want to remove all items from your cart?')) {
        if (isLoggedIn) {
            // Clear from database
            fetch('cart_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'clear'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear display
                    document.getElementById('cart-items-list').innerHTML = 
                        '<div class="empty-cart-message">Your cart is empty.</div>';
                    updateSubtotal(0);
                } else {
                    console.error('Failed to clear cart:', data.message);
                    alert('Could not clear cart: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
                alert('Error clearing cart. Please try again.');
            });
        } else {
            // Clear localStorage
            localStorage.removeItem('cart');
            document.getElementById('cart-items-list').innerHTML = 
                '<div class="empty-cart-message">Your cart is empty.</div>';
            updateSubtotal(0);
        }
    }
}

// Recalculate cart total based on current items
function recalculateTotal() {
    if (isLoggedIn) {
        // For logged in users - reload from server for most accurate total
        window.location.reload();
    } else {
        // For guests - calculate from local storage
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        let subtotal = 0;
        
        cartItems.forEach(item => {
            subtotal += item.price * item.quantity;
        });
        
        updateSubtotal(subtotal);
    }
}

// Update the subtotal and total display
function updateSubtotal(amount) {
    document.getElementById('subtotal').textContent = amount.toFixed(2);
    document.getElementById('total').textContent = amount.toFixed(2);
}

// Update the displayed quantity for an item
function updateItemQuantityDisplay(productId, newQuantity) {
    const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
    if (item) {
        if (newQuantity <= 0) {
            item.remove();
        } else {
            item.querySelector('.quantity').textContent = newQuantity;
        }
    }
}

// Add item to cart (for product pages)
function addToCart(productData) {
    if (isLoggedIn) {
        // For logged in users - use API
        fetch('cart_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productData.product_id,
                product_name: productData.product_name,
                price: productData.price,
                quantity: productData.quantity || 1,
                image_url: productData.image_url
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item added to cart!');
            } else {
                console.error('Failed to add item:', data.message);
            }
        })
        .catch(error => {
            console.error('Error adding item:', error);
        });
    } else {
        // For guests using localStorage
        let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cartItems.find(item => item.product_id == productData.product_id);
        
        if (existingItem) {
            existingItem.quantity += (productData.quantity || 1);
        } else {
            cartItems.push({
                product_id: productData.product_id,
                product_name: productData.product_name,
                price: productData.price,
                quantity: productData.quantity || 1,
                image_url: productData.image_url
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cartItems));
        alert('Item added to cart!');
    }
}

// Proceed to checkout
function proceedToCheckout() {
    // Check if cart is empty
    const subtotal = parseFloat(document.getElementById('subtotal').textContent);
    if (subtotal <= 0) {
        alert('Your cart is empty. Please add items before checking out.');
        return;
    }
    
    // Redirect to checkout
    window.location.href = 'checkout.php';
}
