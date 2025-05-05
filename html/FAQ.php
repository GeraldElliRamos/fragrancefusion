<?php
// Start the session at the very beginning
session_start();

// Include the database connection
require_once __DIR__ . '/../config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Fragrance Fusion</title>
    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/FAQ.css">
  </head>
  <body>

    <?php include __DIR__ . '/header.php'; ?>
  
    <!-- FAQ Section -->
    <div class="container my-5">
      <div class="text-center mb-4">
        <h1 class="h3">Frequently Asked Questions</h1>
        <p class="text-muted">Find answers to the most common questions about Fragrance Fusion</p>
      </div>

      <div class="faq-cards">
        <div class="faq-card">
          <h5 class="faq-question">What is Fragrance Fusion?</h5>
          <p class="faq-answer">Fragrance Fusion is a platform where you can explore and purchase unique fragrances crafted to tell stories and create memories.</p>
        </div>

        <div class="faq-card">
          <h5 class="faq-question">How can I contact customer support?</h5>
          <p class="faq-answer">You can contact our customer support team by visiting the <a href="contact.html" class="text-decoration-none">Contact Us</a> page and filling out the form.</p>
        </div>

        <div class="faq-card">
          <h5 class="faq-question">What is your return policy?</h5>
          <p class="faq-answer">We offer a 30-day return policy for unopened and unused products. For more details, visit our <a href="returns.html" class="text-decoration-none">Returns</a> page.</p>
        </div>


        <div class="faq-card">
          <h5 class="faq-question">Can I customize my fragrance?</h5>
          <p class="faq-answer">at the moment, we do not offer customization options. However, we are always looking to expand our offerings, so stay tuned!</p>
      </div>
    </div>
    
   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/aboutus.js"></script>
  </body>
</html>