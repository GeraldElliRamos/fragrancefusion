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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/collections2.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Parfum Extrait Fragrances</title>
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <div class="container modern-container">
        <h1 class="modern-heading">Find Your Perfect Fragrance</h1>
        <p class="modern-subheading">Discover scents tailored to your preferences</p>

        <h2 class="modern-section-title">Choose Your Style</h2>
        <div class="modern-grid men-women">
            <div class="modern-card" onclick="redirectTo('../html/sauvageamouage.php')">
                <img src="../assets/FORMEN5.jpg" alt="Male" class="modern-card-img">
                <div class="modern-card-body">
                    <h3 class="modern-card-title">Male</h3>
                    <p class="modern-card-text">Bold and sophisticated scents</p>
                </div>
            </div>
            <div class="modern-card" onclick="redirectTo('../html/maisonfrancisamouage.php')">
                <img src="../assets/FORWOMEN5.jpg" alt="Female" class="modern-card-img">
                <div class="modern-card-body">
                    <h3 class="modern-card-title">Female</h3>
                    <p class="modern-card-text">Elegant and refined fragrances</p>
                </div>
            </div>
        </div>

        <h2 class="modern-section-title">Select Your Season</h2>
        <div class="modern-grid seasons">
            <div class="modern-card" onclick="redirectTo('../seasons/Spring.php')">
                <div class="modern-card-body text-center">
                    <h3 class="modern-card-title">Spring</h3>
                    <p class="modern-card-text">Fresh floral and green notes</p>
                </div>
            </div>
            <div class="modern-card" onclick="redirectTo('../seasons/Summer.php')">
                <div class="modern-card-body text-center">
                    <h3 class="modern-card-title">Summer</h3>
                    <p class="modern-card-text">Light citrus and aquatic scents</p>
                </div>
            </div>
            <div class="modern-card" onclick="redirectTo('../seasons/Autumn.php')">
                <div class="modern-card-body text-center">
                    <h3 class="modern-card-title">Autumn</h3>
                    <p class="modern-card-text">Warm spicy and woody notes</p>
                </div>
            </div>
            <div class="modern-card" onclick="redirectTo('../seasons/Winter.php')">
                <div class="modern-card-body text-center">
                    <h3 class="modern-card-title">Winter</h3>
                    <p class="modern-card-text">Rich oriental and vanilla blends</p>
                </div>
            </div>
        </div>
    </div>  

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirectTo(target) {
            target = target.replace('.html', '.php');
            window.location.href = target;
        }
    </script>
</body>
</html>