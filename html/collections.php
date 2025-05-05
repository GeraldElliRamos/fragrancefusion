<?php
// Start the session at the very beginning
session_start();

// Add at the top of collections.php, after session_start():
if (isset($_GET['debug'])) {
    echo '<div style="background: #ffe; border: 1px solid #ccc; padding: 10px; margin: 10px;">';
    echo '<h3>Debug Information</h3>';
    echo '<p><strong>Session Status:</strong> ' . (session_status() == PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '</p>';
    echo '<p><strong>User Logged In:</strong> ' . (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] ? 'Yes' : 'No') . '</p>';
    echo '<p><strong>User ID:</strong> ' . ($_SESSION['user_id'] ?? 'Not set') . '</p>';
    echo '<pre>' . print_r($_SESSION, true) . '</pre>';
    echo '</div>';
}

// Add this near the top of collections.php
function fixImagePath($path) {
    // Convert ./assets/ to the correct relative path if needed
    return str_replace('./assets/', '../assets/', $path);
}

require_once __DIR__ . '/../config/db_connection.php';

// Fetch collections from the database instead of hardcoding them
$stmt = $conn->prepare("SELECT * FROM collections ORDER BY id");
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrance Fusion Collections</title>
    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/collections.css">
    <style>

        /* Ensure no margins or padding on the body and html */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
        }


        /* Update the toast-container styling */
        .toast-container {
            position: fixed;  /* Changed from absolute to fixed */
            top: 20px;
            right: 20px;
            z-index: 1060;
        }
        
    </style>
</head>
<body>
    <!-- Toast notifications container -->
    <div class="toast-container"></div>

            
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Our Collections</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            
            <?php foreach($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo fixImagePath(htmlspecialchars($product['image'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($product['name']); ?>
                            <span class="price-pill">$<?php echo number_format($product['price'], 2); ?></span>
                        </h5>
                        <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="card-buttons">
                            <button class="btn btn-primary btn-sm cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                            </button>
                            <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $product['id']; ?>">
                                View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for this product -->
            <div class="modal fade" id="modal-<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="modalLabel-<?php echo $product['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel-<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <img src="<?php echo fixImagePath(htmlspecialchars($product['image'])); ?>" class="img-fluid mb-3" alt="<?php echo htmlspecialchars($product['name']); ?> Details">
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                            <p><strong>Notes:</strong> <?php echo htmlspecialchars($product['notes']); ?></p>
                            <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                            <p><strong>Availability:</strong> 
                            <?php if($product['stock'] > 10): ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php elseif($product['stock'] > 0): ?>
                                <span class="badge bg-warning text-dark">Only <?php echo $product['stock']; ?> left</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            <?php if($product['stock'] > 0): ?>
                                <button type="button" class="cart-btn btn btn-primary btn-sm" data-product-id="<?php echo $product['id']; ?>" data-bs-dismiss="modal">
                                    <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>
       

    <!-- Bootstrap 5.0.2 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>

    <script>
        // Update copyright year safely
        const yearElement = document.getElementById('current-year');
        if (yearElement) {
            yearElement.textContent = new Date().getFullYear();
        }
        
        // Variables to store session info for JS
        const isLoggedIn = <?php echo isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] ? 'true' : 'false'; ?>;
        const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        let cartCount = <?php echo $cartCount; ?>;
        
        // Function to create and show toast notification
        function showNotification(title, message, type = 'success', duration = 3000) {
            // Create toast element
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            // Generate unique ID for the toast
            const toastId = 'toast-' + Date.now();
            toastEl.id = toastId;
            
            // Set auto-hide
            toastEl.setAttribute('data-bs-delay', duration);
            toastEl.setAttribute('data-bs-autohide', 'true');
            
            // Create toast content
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            // Add to container
            document.querySelector('.toast-container').appendChild(toastEl);
            
            // Initialize and show
            const toastInstance = new bootstrap.Toast(toastEl);
            toastInstance.show();
            
            // Remove after it's hidden
            toastEl.addEventListener('hidden.bs.toast', function() {
                toastEl.remove();
            });
        }
        
        // Fix this function
        function updateCartBadge(count) {
            const badge = document.getElementById('cart-badge');
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Function to get current cart count from localStorage (for guests)
        function getLocalCartCount() {
            try {
                const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
                return cartItems.reduce((total, item) => total + item.quantity, 0);
            } catch (e) {
                return 0;
            }
        }
        
        // Initialize cart badge for guests
        if (!isLoggedIn) {
            updateCartBadge(getLocalCartCount());
        }
        
        // Add to cart function
        function addToCart(product, button) {
            console.log('Raw product data:', product);
            
            // Show loading state on button
            const originalContent = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            button.classList.add('loading');
            
            // Check stock first
            if (product.stock <= 0) {
                showNotification('Out of Stock', `${product.name} is currently out of stock.`, 'danger');
                button.innerHTML = originalContent;
                button.classList.remove('loading');
                return;
            }
            
            // Format the product data to ensure all required fields exist and are properly named
            const cartProduct = {
                action: 'add',
                product_id: parseInt(product.id) || Date.now(), // Ensure product_id is an integer
                product_name: product.name || 'Unknown Product',
                price: parseFloat(product.price) || 0.0,
                quantity: 1,
                image_url: product.image || '../assets/product-placeholder.png' // Map 'image' to 'image_url'
            };
            
            console.log('Sending to cart API:', cartProduct);
            
            if (isLoggedIn) {
                // For logged in users - use API
                fetch('cart_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(cartProduct)
                })
                .then(response => {
                    console.log('API Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`Server returned ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Restore button
                    button.innerHTML = originalContent;
                    button.classList.remove('loading');
                    
                    console.log('API Response data:', data);
                    if (data.success) {
                        // Update cart count from API response data
                        if (data.data && data.data.cart_count) {
                            cartCount = data.data.cart_count;
                        } else {
                            cartCount++;
                        }
                        updateCartBadge(cartCount);
                        showNotification('Added to Cart', `${product.name} has been added to your cart.`);
                        
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
                    // Restore button
                    button.innerHTML = originalContent;
                    button.classList.remove('loading');
                    
                    console.error('Error adding item:', error);
                    showNotification('Error', 'Could not connect to server. Please try again.', 'danger');
                });
            } else {
                // For guests using localStorage
                try {
                    let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
                    const existingItem = cartItems.find(item => item.product_id == product.id);
                    
                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        cartItems.push({
                            product_id: parseInt(product.id) || Date.now(),
                            product_name: product.name,
                            price: parseFloat(product.price),
                            quantity: 1,
                            image_url: product.image // Store image as image_url for consistency
                        });
                    }
                    
                    localStorage.setItem('cart', JSON.stringify(cartItems));
                    
                    // Restore button after slight delay
                    setTimeout(() => {
                        button.innerHTML = originalContent;
                        button.classList.remove('loading');
                        
                        // Update cart badge and show notification
                        updateCartBadge(getLocalCartCount());
                        showNotification('Added to Cart', `${product.name} has been added to your cart.`);
                        
                        // Add animation to cart icon
                        const cartIcon = document.querySelector('.cart-icon');
                        cartIcon.classList.add('pulse');
                        setTimeout(() => {
                            cartIcon.classList.remove('pulse');
                        }, 1000);
                    }, 500);
                } catch (e) {
                    // Restore button
                    button.innerHTML = originalContent;
                    button.classList.remove('loading');
                    
                    console.error('Error storing in localStorage:', e);
                    showNotification('Error', 'Could not add to cart: ' + e.message, 'danger');
                }
            }
        }

        // Add CSS for cart icon animation
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            .pulse {
                animation: pulse 0.5s;
            }
        `;
        document.head.appendChild(style);

        // Update the event listener code
        document.addEventListener('DOMContentLoaded', function() {
            // Get all product data for client-side use
            const productData = <?php echo json_encode($products); ?>;
            
            // Add click handlers to all Add to Cart buttons
            document.querySelectorAll('[data-product-id]').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const product = productData.find(p => p.id == productId);
                    
                    if (product) {
                        console.log('Adding to cart:', product);
                        addToCart(product, this); // Pass the button reference
                    } else {
                        console.error('Product not found:', productId);
                    }
                });
            });
        });
    </script>
</body>
</html>