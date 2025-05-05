<?php
// Start the session at the very beginning
session_start();
require_once __DIR__ . '/../config/db_connection.php';

// Get cart count for the badge (if logged in)
$cartCount = 0;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cartCount = $row['cart_count'] ?: 0;
    }
    $stmt->close();
}

// Fetch cologne products for men from the database
$category = 'cologne';
$gender = 'male';
$stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND gender = ?");
$stmt->bind_param("ss", $category, $gender);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrance Fusion - Men's Cologne</title>
    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add this after the Bootstrap CSS link in the head -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/collections3.css">
    <style>
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999; /* Increased z-index */
            pointer-events: none; /* Let clicks pass through the container but not the toasts */
        }
        
        /* Make individual toasts receive pointer events */
        .toast {
            pointer-events: auto;
            opacity: 1 !important; /* Force opacity */
            display: block; /* Ensure display */
            border: none;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            margin-bottom: 10px; /* Add space between toasts */
        }
        
        .toast.success {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
        }
        .toast.danger {
            background: linear-gradient(to right, #dc3545, #c82333);
            color: white;
        }
        .toast.info {
            background: linear-gradient(to right, #17a2b8, #138496);
            color: white;
        }
        .toast-header {
            background-color: rgba(255, 255, 255, 0.1);
            color: inherit;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .toast-body {
            font-weight: 500;
        }
        
    </style>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<!-- Main Content -->
<div class="container mt-5">
    <h1 class="text-center modern-heading">Male Fragrances - Eau De Cologne</h1>
    <p class="text-center modern-subheading">Explore our selection of bold and refined fragrances designed for the modern man.</p>

    <?php if(empty($products)): ?>
        <div class="alert alert-info text-center">
            No products found in this category. Please check back later.
        </div>
    <?php else: ?>
        <div class="modern-grid">
            <?php foreach($products as $product): ?>
                <div class="modern-card" id="product-<?php echo $product['id']; ?>">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="modern-card-img">
                    <div class="modern-card-body">
                        <h3 class="modern-card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="modern-card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="modern-card-price">$<?php echo number_format($product['price'], 2); ?></p>
                        <button 
                            class="btn btn-dark add-to-cart" 
                            onclick="addToCart({
                                product_id: <?php echo $product['id']; ?>, 
                                product_name: '<?php echo addslashes($product['name']); ?>', 
                                price: <?php echo $product['price']; ?>, 
                                quantity: 1, 
                                image_url: '<?php echo addslashes($product['image_url']); ?>'
                            })"
                        >
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables to store session info for JS
        const isLoggedIn = <?php echo isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] ? 'true' : 'false'; ?>;
        const userId = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'null'; ?>;
        
        // Define a global cart count variable
        let cartCount = <?php echo $cartCount; ?>;

        // Updated showNotification function with working close button
        function showNotification(title, message, type = 'success', duration = 3000) {
            console.log('Showing notification:', title, message);
            
            // Make sure toast container exists
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element with a unique ID
            const toastId = 'toast-' + new Date().getTime();
            const toastEl = document.createElement('div');
            toastEl.className = `toast ${type}`;
            toastEl.id = toastId;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            // Toast content
            toastEl.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            // Add to container
            toastContainer.appendChild(toastEl);
            
            // Create and show the toast manually
            toastEl.style.display = 'block';
            
            // Store the timeout ID so we can clear it if the toast is closed manually
            let closeTimeout = setTimeout(() => {
                hideToast(toastEl);
            }, duration);
            
            // Add manual close button functionality (in case Bootstrap fails)
            const closeButton = toastEl.querySelector('.btn-close');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    clearTimeout(closeTimeout); // Clear the auto-close timeout
                    hideToast(toastEl);
                });
            }
            
            // Try to use Bootstrap Toast if available
            try {
                const toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: duration
                });
                toast.show();
                
                // Listen for bootstrap's hidden event
                toastEl.addEventListener('hidden.bs.toast', function() {
                    toastEl.remove();
                });
            } catch(e) {
                console.warn('Bootstrap Toast initialization failed, using fallback', e);
            }
            
            // Helper function to hide and remove toast
            function hideToast(el) {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    el.remove();
                }, 500);
            }
        }
        
        // Test notification on page load (for debugging)
        document.addEventListener('DOMContentLoaded', function() {
            // Uncomment to test notifications
            // setTimeout(() => {
            //     showNotification('Test Notification', 'This is a test notification');
            // }, 1000);
        });
        
        // Enhance your updateCartBadge function
        function updateCartBadge(count) {
            // Update the global cart count
            cartCount = count;
            
            // Get the cart badge element
            const cartLink = document.querySelector('.cart-link');
            let badge = document.querySelector('.cart-badge');
            
            // If count is greater than 0, show the badge
            if (count > 0) {
                // Create badge if it doesn't exist
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    cartLink.appendChild(badge);
                }
                
                // Update the count text
                badge.textContent = count;
                badge.style.display = '';
            } 
            // Hide the badge if count is 0
            else if (badge) {
                badge.style.display = 'none';
            }
        }
        
        // Inline implementation of addToCart to ensure it works
        function addToCart(productData) {
            if (!productData) {
                console.error('No product data provided');
                return;
            }
            
            console.log('Adding to cart:', productData); // Debug logging
            
            // Change button to loading state
            const button = event.currentTarget;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            button.disabled = true;
            
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
                    // Reset button state
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    
                    if (data.success) {
                        showNotification('Added to Cart', `${productData.product_name} has been added to your cart.`);
                        
                        // Update cart count immediately
                        if (data.data && data.data.cart_count !== undefined) {
                            updateCartBadge(data.data.cart_count);
                        } else {
                            // If server doesn't return cart count, increment locally
                            updateCartBadge(cartCount + 1);
                        }
                        
                        // Add animation to cart icon
                        const cartIcon = document.querySelector('.cart-icon');
                        cartIcon.classList.add('pulse');
                        setTimeout(() => {
                            cartIcon.classList.remove('pulse');
                        }, 1000);
                    } else {
                        console.error('Failed to add item:', data.message);
                        showNotification('Error', 'Could not add to cart: ' + (data.message || 'Unknown error'), 'danger');
                    }
                })
                .catch(error => {
                    // Reset button state
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    
                    console.error('Error adding item:', error);
                    showNotification('Error', 'Could not connect to server. Please try again.', 'danger');
                });
            } else {
                // For guests using localStorage
                try {
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
                    
                    // Calculate new cart count immediately after updating localStorage
                    const newCartCount = cartItems.reduce((total, item) => total + item.quantity, 0);
                    
                    // Update badge right away before timeout
                    updateCartBadge(newCartCount);
                    
                    setTimeout(() => {
                        // Reset button after slight delay
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                        
                        // Show notification
                        showNotification('Added to Cart', `${productData.product_name} has been added to your cart.`);
                        
                        // Add animation to cart icon
                        const cartIcon = document.querySelector('.cart-icon');
                        cartIcon.classList.add('pulse');
                        setTimeout(() => {
                            cartIcon.classList.remove('pulse');
                        }, 1000);
                    }, 500);
                } catch (e) {
                    // Reset button state
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    
                    console.error('Error storing in localStorage:', e);
                    showNotification('Error', 'Could not add to cart: ' + e.message, 'danger');
                }
            }
        }

        // Initialize cart badge for guests on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (!isLoggedIn) {
                const localCartCount = getLocalCartCount();
                updateCartBadge(localCartCount);
            }
        });

        // Helper function to get local cart count
        function getLocalCartCount() {
            try {
                const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
                return cartItems.reduce((total, item) => total + item.quantity, 0);
            } catch (e) {
                console.error('Error reading localStorage:', e);
                return 0;
            }
        }
    </script>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <!-- Toasts will be injected here -->
    </div>
</body>
</html>