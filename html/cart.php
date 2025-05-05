<?php
// Start the session at the very beginning
session_start();
require_once __DIR__ . '/../config/db_connection.php';

// Debugging info
$debug = [];
$debug['logged_in'] = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$debug['userId'] = $_SESSION['user_id'] ?? 'not set';

// Get cart items for logged in user
$cartItems = [];
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    try {
        $user_id = $_SESSION['user_id'];
        $debug['querying_for_user'] = $user_id;
        
        // Prepare the SQL for debugging
        $sql = "SELECT * FROM cart_items WHERE user_id = " . intval($user_id);
        $debug['sql_query'] = $sql;
        
        $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $debug['db_items_found'] = $result->num_rows;
        
        // Capture the actual row data for debugging
        $debug['db_items'] = [];
        while ($row = $result->fetch_assoc()) {
            // Store a copy for debug
            $debug['db_items'][] = $row;
            
            // Fix image URLs that might have incorrect paths
            if (isset($row['image_url'])) {
                // Remove any potential path issues
                $row['image_url'] = preg_replace('/\.\.\/\.\.\/assets/', '../assets', $row['image_url']);
                // Ensure it starts with the correct path
                if (strpos($row['image_url'], '../assets') === false && strpos($row['image_url'], 'http') === false) {
                    $row['image_url'] = '../assets/' . basename($row['image_url']);
                }
            }
            $cartItems[] = $row;
        }
    } catch (Exception $e) {
        $debug['error'] = $e->getMessage();
    }
}

// Debug info as JSON for JavaScript
$debugJson = json_encode($debug);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Fragrance Fusion - Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../css/cart.css" />
    <link rel="stylesheet" href="../css/headerfooter.css" />
    <style>
      /* Add these styles to your existing style section */
      .quantity-controls {
        display: flex;
        align-items: center;
      }
      
      .quantity-input {
        width: 50px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 0;
        margin: 0 5px;
        padding: 2px 5px;
        height: 30px;
      }
      
      /* Remove spinner arrows from number input */
      .quantity-input::-webkit-inner-spin-button,
      .quantity-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }
      
      .quantity-input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield; /* Add standard property for better compatibility */
      }
      
      .quantity-controls button {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        width: 30px;
        height: 30px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
      }
      
      .quantity-controls button:hover {
        background-color: #e9ecef;
      }
      
      /* Toast styles */
      .toast-container {
        z-index: 1050;
      }
      .toast {
        min-width: 250px;
      }
      .toast.success {
        background-color: #d1e7dd;
        border-color: #badbcc;
        color: #0f5132;
      }
      .toast.danger {
        background-color: #f8d7da;
        border-color: #f5c2c7;
        color: #842029;
      }
      .toast.limit-warning {
        background-color: #fff3cd;
        border-color: #ffecb5;
        color: #664d03;
      }

      /* Add this to your existing style section */
      .toast.clear-all {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        font-weight: 500;
        font-size: 1.05rem;
        min-width: 300px;
      }
      
      /* Add animation to the notification */
      @keyframes toastFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
      
      .toast {
        animation: toastFadeIn 0.3s ease-out forwards;
      }

      /* Add to your existing style section */
      #confirmationModal .modal-content {
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      }
      
      #confirmationModal .modal-header {
        border-bottom: 1px solid rgba(0,0,0,0.1);
        background-color: #f8f9fa;
      }
      
      #confirmationModal .modal-footer {
        border-top: 1px solid rgba(0,0,0,0.1);
      }
      
      #confirmActionButton {
        background-color: #212529;
        border-color: #212529;
      }
      
      #confirmActionButton:hover {
        background-color: #000;
      }
    </style>
  </head>
  <body>
  <?php include __DIR__ . '/header.php'; ?>

  <!-- Cart Section -->
  <div class="container my-5 cart-container">
    <h2 class="mb-4">Shopping Cart</h2>
    
    <?php if(!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
      <!-- Message for guest users -->
      <div class="alert alert-info">
        <p>Please <a href="login.php">login</a> to save your cart items for later.</p>
      </div>
    <?php endif; ?>
    
    <div class="row">
      <!-- Cart Items -->
      <div class="col-md-8">
        <div id="cart-items" class="card p-3">
          <h5 class="mb-4">Items</h5>
          <div id="cart-items-list">
            <!-- PHP will populate this if user is logged in -->
            <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && count($cartItems) > 0): ?>
              <?php foreach($cartItems as $item): ?>
                <div class="cart-item" data-id="<?php echo $item['product_id']; ?>">
                  <div class="cart-item-image">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? '../assets/product-placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                  </div>
                  <div class="cart-item-details">
                    <h6><?php echo htmlspecialchars($item['product_name']); ?></h6>
                    <p>$<?php echo number_format($item['price'], 2); ?></p>
                    <div class="quantity-controls">
                      <button onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                      <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                        min="1" max="100" onchange="updateQuantityDirect(<?php echo $item['product_id']; ?>, this.value)">
                      <button onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                    </div>
                  </div>
                  <button class="remove-btn" onclick="removeItem(<?php echo $item['product_id']; ?>)">×</button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <button class="btn btn-danger mt-3" onclick="removeAllItems()">Remove All</button>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Order Summary</h5>
          <p>Subtotal: $<span id="subtotal">
            <?php 
              $subtotal = 0;
              if (isset($cartItems)) {
                foreach($cartItems as $item) {
                  $subtotal += $item['price'] * $item['quantity'];
                }
              }
              echo number_format($subtotal, 2);
            ?>
          </span>
          <hr>
          <p><strong>Total: $<span id="total">
            <?php echo number_format($subtotal, 2); ?>
          </span></strong></p>
         
          </ul>
        </div>
      </div>    
    </div>
  </div>
    
  <?php include __DIR__ . '/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Variables to store session info for JS
      const isLoggedIn = <?php echo isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] ? 'true' : 'false'; ?>;
      const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
      const debugInfo = <?php echo $debugJson; ?>;
      console.log("Debug Info:", debugInfo);
      
      // Set up the cart as soon as the page loads
      document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing cart');
        
        // Set current year in footer
        document.getElementById('current-year').textContent = new Date().getFullYear();
        
        // For guest users, load cart from localStorage
        if (!isLoggedIn) {
          loadCartFromLocalStorage();
        } else {
          // For logged in users with no items, try to load from localStorage
          if (debugInfo.db_items_found === 0) {
            loadCartFromLocalStorage();
          }
        }
      });
      
      // Load cart from localStorage for guest users
      function loadCartFromLocalStorage() {
        console.log("Loading cart from localStorage");
        try {
          // Get cart items from localStorage
          const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
          console.log("Cart items from localStorage:", cartItems);
          
          const cartItemsList = document.getElementById('cart-items-list');
          
          // If no items, show empty message
          if (cartItems.length === 0) {
            cartItemsList.innerHTML = '<div class="empty-cart-message">Your cart is empty.</div>';
            document.getElementById('subtotal').textContent = '0.00';
            document.getElementById('total').textContent = '0.00';
            return;
          }
          
          // For guest users, clear existing HTML and display localStorage items
          if (!isLoggedIn) {
            cartItemsList.innerHTML = '';
          }
          
          let subtotal = 0;
          
          // Add each item to the display
          cartItems.forEach(item => {
            // Skip if this item is already in the DOM (for logged in users)
            if (document.querySelector(`.cart-item[data-id="${item.product_id}"]`)) {
              subtotal += item.price * item.quantity;
              return;
            }
            
            // Calculate total for this item
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            // Create the cart item element
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.dataset.id = item.product_id;
            
            // Fix image path if needed
            let imagePath = item.image_url || '../assets/product-placeholder.png';
            
            itemElement.innerHTML = `
              <div class="cart-item-image">
                <img src="${imagePath}" alt="${item.product_name}">
              </div>
              <div class="cart-item-details">
                <h6>${item.product_name}</h6>
                <p>$${parseFloat(item.price).toFixed(2)}</p>
                <div class="quantity-controls">
                  <button onclick="updateQuantity(${item.product_id}, -1)">-</button>
                  <input type="number" class="quantity-input" value="${item.quantity}" 
                    min="1" max="100" onchange="updateQuantityDirect(${item.product_id}, this.value)">
                  <button onclick="updateQuantity(${item.product_id}, 1)">+</button>
                </div>
              </div>
              <button class="remove-btn" onclick="removeItem(${item.product_id})">×</button>
            `;
            
            cartItemsList.appendChild(itemElement);
          });
          
          // Update the subtotal and total
          document.getElementById('subtotal').textContent = subtotal.toFixed(2);
          document.getElementById('total').textContent = subtotal.toFixed(2);
        } catch (error) {
          console.error("Error loading cart from localStorage:", error);
          document.getElementById('cart-items-list').innerHTML = 
            '<div class="alert alert-danger">Error loading cart items. Please try refreshing the page.</div>';
        }
      }
      
      // Update item quantity
      function updateQuantity(productId, change) {
        console.log(`Updating quantity for product ${productId} by ${change}`);
        
        // Find current quantity element - FIXED: use quantity-input instead of quantity
        const quantityInput = document.querySelector(`.cart-item[data-id="${productId}"] .quantity-input`);
        if (!quantityInput) {
          console.error(`Quantity input not found for product ${productId}`);
          return;
        }
        
        // Get current quantity from input value instead of textContent
        const currentQuantity = parseInt(quantityInput.value);
        
        // Calculate new quantity
        const newQuantity = currentQuantity + change;
        
        // Check against limits
        if (newQuantity < 1) {
          showToast('Minimum quantity is 1', 'limit-warning');
          return;
        }
        
        if (newQuantity > 100) {
          showToast('Maximum limit is 100 units per item', 'limit-warning');
          return;
        }
        
        // Update the input value immediately for better UX
        quantityInput.value = newQuantity;
        
        if (isLoggedIn) {
          // Update in database for logged-in users
          fetch('cart_api.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              action: 'update',
              product_id: productId,
              quantity_change: change,
              new_quantity: newQuantity
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update totals without page reload
              updateCartTotals();
              showToast('Quantity updated', 'success');
            } else {
              console.error('Failed to update item:', data.message);
              showToast('Could not update item: ' + (data.message || 'Unknown error'), 'danger');
              // Revert the input value
              quantityInput.value = currentQuantity;
            }
          })
          .catch(error => {
            console.error('Error updating item:', error);
            showToast('Error updating item. Please try again.', 'danger');
            // Revert the input value
            quantityInput.value = currentQuantity;
          });
        } else {
          // Update in localStorage for guests
          try {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const itemIndex = cartItems.findIndex(item => item.product_id == productId);
            
            if (itemIndex > -1) {
              // Update quantity directly
              cartItems[itemIndex].quantity = newQuantity;
              
              // Save to localStorage
              localStorage.setItem('cart', JSON.stringify(cartItems));
              
              // Update totals without full reload
              updateCartTotals();
              showToast('Quantity updated', 'success');
            }
          } catch (e) {
            console.error('Error updating quantity in localStorage:', e);
            showToast('Could not update item quantity', 'danger');
            // Revert the input value
            quantityInput.value = currentQuantity;
          }
        }
      }
      
      // Add this function to show the custom confirmation dialog
      function showConfirmation(message, confirmCallback) {
        // Set the message in the modal
        document.getElementById('confirmationModalBody').textContent = message;
        
        // Create a Bootstrap modal instance
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        
        // Set up the confirm button action
        const confirmButton = document.getElementById('confirmActionButton');
        
        // Remove any previous event listeners
        const newConfirmButton = confirmButton.cloneNode(true);
        confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
        
        // Add the new event listener
        newConfirmButton.addEventListener('click', function() {
          modal.hide();
          confirmCallback();
        });
        
        // Show the modal
        modal.show();
      }

      // Replace the removeItem function
      function removeItem(productId) {
        // Get the product name for a more specific confirmation message
        const itemElement = document.querySelector(`.cart-item[data-id="${productId}"]`);
        const productName = itemElement ? itemElement.querySelector('.cart-item-details h6').textContent : 'this item';
        
        // Use custom confirmation instead of browser confirm
        showConfirmation(`Are you sure you want to remove ${productName} from your cart?`, function() {
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
                // Show removal notification
                showToast(`${productName} has been removed from your cart`, 'clear-all');
                
                // Slight delay before reload to show notification
                setTimeout(() => {
                  location.reload();
                }, 1500);
              } else {
                console.error('Failed to remove item:', data.message);
                showToast('Could not remove item: ' + (data.message || 'Unknown error'), 'danger');
              }
            })
            .catch(error => {
              console.error('Error removing item:', error);
              showToast('Error removing item. Please try again.', 'danger');
            });
          } else {
            // Remove from localStorage
            try {
              let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
              cartItems = cartItems.filter(item => item.product_id != productId);
              localStorage.setItem('cart', JSON.stringify(cartItems));
              
              // Show removal notification
              showToast(`${productName} has been removed from your cart`, 'clear-all');
              
              // Update the cart display
              loadCartFromLocalStorage();
            } catch (e) {
              console.error('Error removing item from localStorage:', e);
              showToast('Could not remove item', 'danger');
            }
          }
        });
      }

      // Replace the removeAllItems function
      function removeAllItems() {
        // Use custom confirmation instead of browser confirm
        showConfirmation('Are you sure you want to remove all items from your cart?', function() {
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
                // Show enhanced notification first
                showToast('All items have been removed from your cart', 'clear-all');
                
                // Increase delay before reload to ensure notification is seen
                setTimeout(() => {
                  location.reload();
                }, 2500);
              } else {
                console.error('Failed to clear cart:', data.message);
                showToast('Could not clear cart: ' + (data.message || 'Unknown error'), 'danger');
              }
            })
            .catch(error => {
              console.error('Error clearing cart:', error);
              showToast('Error clearing cart. Please try again.', 'danger');
            });
          } else {
            // Clear localStorage
            localStorage.removeItem('cart');
            
            // Show enhanced notification
            showToast('All items have been removed from your cart', 'clear-all');
            
            // Update UI
            loadCartFromLocalStorage();
          }
        });
      }

      // Proceed to checkout
      function proceedToCheckout() {
        // Check if cart is empty
        const subtotal = parseFloat(document.getElementById('subtotal').textContent);
        if (subtotal <= 0) {
          showToast('Your cart is empty. Please add items before checking out.', 'danger');
          return;
        }
        
        // Redirect to checkout page
        window.location.href = 'checkout.php';
      }

      // Handle direct quantity input
      function updateQuantityDirect(productId, newQuantity) {
        console.log(`Updating quantity for product ${productId} to ${newQuantity}`);
        
        // Convert to number
        newQuantity = parseInt(newQuantity);
        
        // Find current quantity element
        const quantityInput = document.querySelector(`.cart-item[data-id="${productId}"] .quantity-input`);
        const currentQuantity = parseInt(quantityInput.value);
        
        // Validate input
        if (isNaN(newQuantity) || newQuantity < 1) {
          showToast('Quantity must be at least 1', 'limit-warning');
          quantityInput.value = currentQuantity; // Reset to previous value
          return;
        }
        
        // Check against maximum limit (100)
        if (newQuantity > 100) {
          showToast('Maximum limit is 100 units per item', 'limit-warning');
          quantityInput.value = 100;
          newQuantity = 100;
        }
        
        // Calculate change
        const change = newQuantity - currentQuantity;
        
        // If no change, do nothing
        if (change === 0) return;
        
        if (isLoggedIn) {
          // Update in database for logged-in users
          fetch('cart_api.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              action: 'update',
              product_id: productId,
              quantity_change: change,
              new_quantity: newQuantity // Send the exact new quantity for direct updates
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update subtotal without reloading
              updateCartTotals();
              showToast('Quantity updated', 'success');
            } else {
              console.error('Failed to update item:', data.message);
              showToast('Could not update quantity: ' + (data.message || 'Unknown error'), 'danger');
              // Reset input to previous value
              quantityInput.value = currentQuantity;
            }
          })
          .catch(error => {
            console.error('Error updating item:', error);
            showToast('Error updating quantity. Please try again.', 'danger');
            // Reset input to previous value
            quantityInput.value = currentQuantity;
          });
        } else {
          // Update in localStorage for guests
          try {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const itemIndex = cartItems.findIndex(item => item.product_id == productId);
            
            if (itemIndex > -1) {
              // Update quantity directly
              cartItems[itemIndex].quantity = newQuantity;
              
              // Save to localStorage
              localStorage.setItem('cart', JSON.stringify(cartItems));
              
              // Update subtotals without full reload
              updateCartTotals();
              showToast('Quantity updated', 'success');
            }
          } catch (e) {
            console.error('Error updating quantity in localStorage:', e);
            showToast('Could not update quantity', 'danger');
            // Reset input to previous value
            quantityInput.value = currentQuantity;
          }
        }
      }

      // Function to update cart totals without reloading
      function updateCartTotals() {
        let subtotal = 0;
        
        // For logged in users
        if (isLoggedIn) {
          document.querySelectorAll('.cart-item').forEach(item => {
            const price = parseFloat(item.querySelector('.cart-item-details p').textContent.replace('$', ''));
            const quantity = parseInt(item.querySelector('.quantity-input').value);
            subtotal += price * quantity;
          });
        } 
        // For guest users
        else {
          const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
          subtotal = cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
        }
        
        // Update the subtotal and total displays
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total').textContent = subtotal.toFixed(2);
      }

      // Show toast notification - improved version
      function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type} align-items-center`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        // Set toast content
        toast.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">
              ${message}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        `;
        
        // Add to container
        toastContainer.appendChild(toast);
        
        try {
          // Initialize Bootstrap toast
          const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
          });
          
          // Show toast
          bsToast.show();
        } catch(e) {
          // Fallback if Bootstrap JS is not loaded
          console.warn('Bootstrap Toast initialization failed, using fallback', e);
          toast.style.display = 'block';
          setTimeout(() => toast.remove(), 3000);
        }
        
        // Remove from DOM after hidden
        toast.addEventListener('hidden.bs.toast', function() {
          toast.remove();
        });
      }
    </script>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <!-- Toasts will be added here dynamically -->
    </div>

    <style>
      .toast-container {
        z-index: 1050;
      }
      .toast {
        min-width: 250px;
      }
      .toast.limit-warning {
        background-color: #fff3cd;
        border-color: #ffecb5;
        color: #664d03;
      }
    </style>

    <!-- Add this modal HTML just before the closing </body> tag -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="confirmationModalBody">
            Are you sure you want to proceed?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmActionButton">Confirm</button>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
``` 