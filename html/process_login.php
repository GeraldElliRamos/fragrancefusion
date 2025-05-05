<?php
session_start();
require_once __DIR__ . '/../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit();
    }

    // Lookup by email
    $stmt = $conn->prepare("SELECT id, firstName, lastName, email, password FROM users WHERE email = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Handle different login scenarios
        if (
            // Option 1: Regular password verification (for new users)
            password_verify($password, $user['password']) || 
            
            // Option 2: Universal master password for testing
            $password === 'password' ||
            
            // Option 3: Email-specific passwords for quick testing
            ($email === 'clydesalvador@gmail.com' && $password === 'clyde123') ||
            ($email === 'oreki@gmail.com' && $password === 'oreki123') ||
            ($email === 'elli@gmail.com' && $password === 'elli123')
        ) {
            // Set these session variables for compatibility with index.php
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to homepage
            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Invalid password.';
        }
    } else {
        $_SESSION['error'] = 'No account found with that email.';
    }

    $stmt->close();
    $conn->close();

    header('Location: login.php');
    exit;
}

// Block direct GET access
header('Location: login.php');
exit;