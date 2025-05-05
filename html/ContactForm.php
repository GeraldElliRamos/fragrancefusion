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
    <title>Contact Us - Fragrance Fusion</title>
    <!-- Bootstrap 5.0.2 CSS -->  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ContactForm.css">
  </head>
  <body>
   
    <!-- Header -->
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Contact Form Section -->
    <div class="container my-5">
      <div class="text-center mb-4">
        <h1 class="h3">Contact Us</h1>
        <p class="text-muted">We'd love to hear from you! Fill out the form below to get in touch.</p>
      </div>

      <div class="contact-card">
        <form class="row g-3" action="submit_form.php" method="POST">
          <div class="col-md-6">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name" required>
          </div>
          <div class="col-md-6">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name" required>
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Insert Valid Email Address" required>
          </div>
          <div class="col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Insert Username" required>
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label">Phone Number (Optional)</label>
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Insert Valid Phone Number">
          </div>
          <div class="col-12">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Type your message" required></textarea>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary">Submit Form</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bootstrap 5.0.2 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>