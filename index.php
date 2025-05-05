<?php
session_start();

// Initialize session variable if not set
if (!isset($_SESSION['user_logged_in'])) {
    $_SESSION['user_logged_in'] = false; // Default to not logged in
}

// Include database connection
require_once __DIR__ . '/config/db_connection.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrance Fusion</title>
    <meta name="description" content="Fragrance Fusion - Unique, memorable scents that tell stories">
    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">    
</head>
<body>
    <?php include __DIR__ . '/html/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay">
            <div class="hero-content">
                <h1 class="hero-title">Discover the Art of Fragrance</h1>
                <p class="hero-subtitle">
                    Immerse yourself in a world of handcrafted scents that tell stories, evoke emotions, and create lasting memories.
                </p>
                <div class="hero-buttons">
                    <a href="html/collections.php" class="hero-btn primary-btn">Explore Collections</a>
                    <a href="html/AboutUs.php" class="hero-btn secondary-btn">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfume Categories -->
    <section class="perfume-categories-section">
        <div class="container">
            <h2 class="section-title">Explore Our Perfume Categories</h2>
            <div class="perfume-categories-grid">
                <div class="perfume-category-row">
                    <div class="category-image-wrapper">
                        <img src="assets/EAUDE2.jpg" alt="Eau De Cologne" class="category-image">
                    </div>
                    <div class="category-info">
                        <h3 class="category-title">Eau De Cologne</h3>
                        <p class="category-description">Fresh and light fragrances for every occasion.</p>
                        <a href="html/eaudecologneseasons.php" class="category-link">Learn More</a>
                    </div>
                </div>
                <div class="perfume-category-row">
                    <div class="category-image-wrapper">
                        <img src="assets/EAUDEPARFUM.jpg" alt="Eau De Parfum" class="category-image">
                    </div>
                    <div class="category-info">
                        <h3 class="category-title">Eau De Parfum</h3>
                        <p class="category-description">Long-lasting scents with a touch of elegance.</p>
                        <a href="html/eaudeparfumseasons.php" class="category-link">Learn More</a>
                    </div>
                </div>
                <div class="perfume-category-row">
                    <div class="category-image-wrapper">
                        <img src="assets/EAUDETOILETTE.jpg" alt="Eau De Toilette" class="category-image">
                    </div>
                    <div class="category-info">
                        <h3 class="category-title">Eau De Toilette</h3>
                        <p class="category-description">Perfect for daily wear with a subtle charm.</p>
                        <a href="html/eaudetoilette.php" class="category-link">Learn More</a>
                    </div>
                </div>
                <div class="perfume-category-row">
                    <div class="category-image-wrapper">
                        <img src="assets/EAUDEFRAICHE.jpg" alt="Eau De Fraiche" class="category-image">
                    </div>
                    <div class="category-info">
                        <h3 class="category-title">Eau De Fraiche</h3>
                        <p class="category-description">Light and refreshing fragrances for a breezy feel.</p>
                        <a href="html/eaudefraiche.php" class="category-link">Learn More</a>
                    </div>
                </div>
                <div class="perfume-category-row">
                    <div class="category-image-wrapper">
                        <img src="assets/PARFUMEXTRAIT.jpg" alt="Parfum Extrait" class="category-image">
                    </div>
                    <div class="category-info">
                        <h3 class="category-title">Parfum Extrait</h3>
                        <p class="category-description">Intense and luxurious scents for special moments.</p>
                        <a href="html/parfumextraitseasons.php" class="category-link">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <?php include __DIR__ . '/html/newsletter.php'; ?>

    <?php include __DIR__ . '/html/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/home.js"></script>
</body>
</html>