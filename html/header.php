<!-- filepath: z:\xampp\htdocs\fragrancefusion\includes\header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
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
// Handle login simulation (for testing purposes)
if (isset($_GET['login'])) {
  $_SESSION['user_logged_in'] = true; // CHANGED TO TRUE - This was the problem!
  header("Location: index.php"); // Redirect to avoid re-triggering login
  exit();
}

// Handle logout
if (isset($_GET['logout'])) {
  session_unset(); // Unset all session variables
  session_destroy(); // Destroy the session
  header("Location: index.php"); // Redirect to the homepage
  exit();
}
// Default cart count if not set
$cartCount = $cartCount ?? 0;
?>
<header class="header">
    <style>
/* Base styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
}

body {
  background-color: white;
  color: #111111;
  line-height: 1.5;
  padding-top: 80px; /* Adjust this value depending on the height of the navbar */
}

.container {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 1280px;
  margin: 0 auto;
  overflow: hidden;
}

a {
  text-decoration: none;
  color: inherit;
  
}

/* Header styles */
.header {
  background-color: white;
  display: flex;
  width: 100%; /* Ensures the navbar spans the full width */
  justify-content: space-between;
  padding: 17px 39px;
  align-items: center;
  flex-wrap: wrap;
  position: fixed; /* Fixes the navbar to the top */
  top: 0; /* Aligns the navbar to the top of the page */
  left: 0; /* Ensures the navbar spans the full width */
  right: 0;
  z-index: 1000; /* Ensures the navbar stays above other content */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Optional: adds a shadow for better visibility */
}

.header-content {
  width: 100%; /* Ensures the content inside the header spans the full width */
  max-width: 1280px; /* Optional: limits the content width */
  margin: 0 auto; /* Centers the content */
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 31px;
}

.logo {
  font-size: 1.5rem;
  font-weight: bold;
  color: #111111;
  text-decoration: none; /* Ensure no underline */
}

.logo:hover {
  color: #111111; /* Keep the same color on hover */
  text-decoration: none; /* Ensure no underline on hover */
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 31px;
  font-size: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 23px;
  text-align: center;
}

.nav-link {
  color: #111111;
  text-decoration: none; /* Remove underline on all links */
  transition: transform 0.3s ease, color 0.3s ease; /* Smooth transition for zoom and color */
}

.nav-link:hover {
  transform: scale(1.1); /* Zoom effect */
  color: #333333; /* Optional: change color on hover */
}

.signup-btn {
  border-radius: 4px;
  background-color: #111111;
  color: white;
  padding: 8px 16px;
  transition: background-color 0.3s ease; /* Smooth transition for background color */
  text-decoration: none; /* Ensure no underline */
}

.signup-btn:hover {
  background-color: #333333; /* Darker background on hover */
  color: white; /* Keep the text color white */
  text-decoration: none; /* Ensure no underline on hover */
}

.cart-icon {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

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
    </style>
    <div class="header-content">
        <div class="header-left">
            <a href="/fragrancefusion/index.php" class="logo">FRAGRANCE FUSION</a>
            <nav class="main-nav">
                <a href="/fragrancefusion/html/collections.php" class="nav-link">Collections</a>
                <a href="/fragrancefusion/html/AboutUs.php" class="nav-link">About</a>
                <a href="/fragrancefusion/html/ContactForm.php" class="nav-link">Contact</a>
                <a href="/fragrancefusion/html/FAQ.php" class="nav-link">FAQ</a>
            </nav>
        </div>
        <div class="header-right">
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <span class="me-3" style="display: inline-flex; align-items: center;">
                    Hello, <strong class="ms-1"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></strong>
                </span>
                <a href="?logout=true" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="/fragrancefusion/html/login.php" class="nav-link">Sign In</a>
                <a href="/fragrancefusion/html/signup.php" class="signup-btn">Sign Up</a>
            <?php endif; ?>
            <a href="/fragrancefusion/html/cart.php" class="cart-link">
                <img src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/5e0645d417ccc7b0f84ef323887e2f0a37abc5a3?placeholderIfAbsent=true" alt="Shopping cart" class="cart-icon">
                <?php if (isset($cartCount) && $cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>