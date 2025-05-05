<?php
// Start the session and include the database connection
session_start();
require_once __DIR__ . '/../config/db_connection.php';

// Get the season data
$stmt = $conn->prepare("SELECT * FROM seasons WHERE name = 'Spring'");
$stmt->execute();
$season = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get products for spring season
$stmt = $conn->prepare("
    SELECT p.* FROM products p
    JOIN product_seasons ps ON p.id = ps.product_id
    JOIN seasons s ON ps.season_id = s.id
    WHERE s.name = 'Spring'
    LIMIT 6
");
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

// Get cart count if user is logged in
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
    <title>Fragrance Fusion - Spring Collection</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/collections3.css">
    <style>
        .cart-link {
            position: relative;
            display: inline-block;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        }
        
        .toast {
            pointer-events: auto;
            opacity: 1 !important;
            display: block;
            border: none;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
        }
        
        .toast.success {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
        }
        .toast.danger {
            background: linear-gradient(to right, #dc3545, #c82333);
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
        
        /* Product add to cart button effects */
        .btn.add-to-cart {
            transition: all 0.3s ease;
        }
        .btn.add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../html/header.php'; ?>

<!-- Main Content -->
<div class="container mt-5">
    <h1 class="text-center modern-heading">Spring Fragrances - Eau De Extrait</h1>
    <p class="text-center modern-subheading">Explore our selection of bold and refined fragrances designed for the modern man.</p>

    <?php if($products->num_rows == 0): ?>
        <div class="alert alert-info text-center">
            No products found in this category. Please check back later.
        </div>
    <?php else: ?>
        <div class="modern-grid">
            <?php while($product = $products->fetch_assoc()): ?>
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
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

    <div class="toast-container"></div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Variables to store session info for JS
        const isLoggedIn = <?php echo isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] ? 'true' : 'false'; ?>;
        const userId = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'null'; ?>;
        let cartCount = <?php echo $cartCount; ?>; // Global cart count variable

        // Helper function to get local cart count
        function getLocalCartCount() {
            try {
                const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
                return cartItems.reduce((total, item) => total + item.quantity, 0);
            } catch (e) {
                console.error('Error reading localStorage cart:', e);
                return 0;
            }
        }

        // Show notification function
        function showNotification(title, message, type = 'success', duration = 3000) {
            const container = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="mr-auto">${title}</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            container.appendChild(toast);
            $(toast).toast({ delay: duration }).toast('show');
            
            // Auto remove after duration
            setTimeout(() => {
                $(toast).toast('hide');
                setTimeout(() => toast.remove(), 500);
            }, duration);
        }
        
        // Update cart badge function
        function updateCartBadge(count) {
            // Update global variable
            cartCount = count;
            
            let badge = document.querySelector('.cart-badge');
            if (!badge && count > 0) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                document.querySelector('.cart-link').appendChild(badge);
            }
            
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        // Add to cart function
        function addToCart(productData) {
            if (!productData) {
                console.error('No product data provided');
                return;
            }
            
            // Change button to loading state
            const button = event.currentTarget;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            button.disabled = true;
            
            if (isLoggedIn) {
                // For logged in users - use API
                fetch('/fragrancefusion/html/cart_api.php', {
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
                        
                        // Update cart count - IMPROVED CODE
                        if (data.data && data.data.cart_count !== undefined) {
                            updateCartBadge(data.data.cart_count);
                        } else {
                            // If server doesn't return cart count, increment locally
                            updateCartBadge(cartCount + 1);
                        }
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
                    
                    // Calculate new cart count IMMEDIATELY (outside setTimeout)
                    const newCartCount = cartItems.reduce((total, item) => total + item.quantity, 0);
                    updateCartBadge(newCartCount);
                    
                    setTimeout(() => {
                        // Reset button after slight delay
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                        
                        // Show notification
                        showNotification('Added to Cart', `${productData.product_name} has been added to your cart.`);
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
        
        $(document).ready(function() {
            // Initialize cart count for guests
            if (!isLoggedIn) {
                updateCartBadge(getLocalCartCount());
            }
            
            // Modal view details functionality 
            $('.view-details').click(function() {
                var card = $(this).closest('.fragrance-card');
                var name = card.data('name');
                var img = card.find('.card-img-top').attr('src');
                var description = card.find('.card-text:first-child').text();
                var price = card.find('.text-muted').text();
                var productId = card.data('id');
                
                $('#fragranceDetailsModalLabel').text(name);
                $('#fragranceDetailsBody').html(`
                    <img src="${img}" class="img-fluid mb-3" alt="${name}">
                    <p>${description}</p>
                    <p class="font-weight-bold">${price}</p>
                `);
                
                // Set up add-to-cart button in modal
                $('.add-to-cart-modal').off('click').on('click', function() {
                    addToCart({
                        product_id: productId,
                        product_name: name,
                        price: parseFloat(price.replace('$', '')),
                        quantity: 1,
                        image_url: img
                    });
                    $('#fragranceDetailsModal').modal('hide');
                });
                
                $('#fragranceDetailsModal').modal('show');
            });
        });
    </script>
</body>
</html>
