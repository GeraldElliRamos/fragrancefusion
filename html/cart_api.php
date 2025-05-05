<?php
// filepath: c:\XAMP\htdocs\fragrancefusion\html\cart_api.php
// Start the session at the very beginning
session_start();
require_once __DIR__ . '/../config/db_connection.php';

// For debugging - log all requests to a file
file_put_contents('cart_api_log.txt', date('Y-m-d H:i:s') . ' - ' . file_get_contents('php://input') . "\n", FILE_APPEND);

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Get request data
$requestData = json_decode(file_get_contents('php://input'), true);
if (!$requestData) {
    // Fallback to POST data
    $requestData = $_POST;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Make sure we have an action
if (!isset($requestData['action'])) {
    $response['message'] = 'No action specified';
    sendResponse($response);
}

// Process the action
switch ($requestData['action']) {
    case 'add':
        if (!$isLoggedIn) {
            $response['success'] = true;
            $response['message'] = 'Item saved to localStorage (not logged in)';
            sendResponse($response);
        }
        
        // Required fields
        if (!isset($requestData['product_id']) || !isset($requestData['product_name']) || !isset($requestData['price'])) {
            $response['message'] = 'Missing required fields';
            sendResponse($response);
        }
        
        // Default quantity if not specified
        $quantity = isset($requestData['quantity']) ? intval($requestData['quantity']) : 1;
        if ($quantity <= 0) {
            $response['message'] = 'Invalid quantity';
            sendResponse($response);
        }
        
        // Default image if not specified
        $imageUrl = isset($requestData['image_url']) ? $requestData['image_url'] : '';
        
        try {
            // Check if item already exists
            $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $requestData['product_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Item exists, update quantity
                $row = $result->fetch_assoc();
                $newQuantity = $row['quantity'] + $quantity;
                
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $newQuantity, $userId, $requestData['product_id']);
            } else {
                // New item, insert it
                $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, product_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisdis", $userId, $requestData['product_id'], $requestData['product_name'], $requestData['price'], $quantity, $imageUrl);
            }
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Item added to cart';
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $response['message'] = 'Exception: ' . $e->getMessage();
        }
        break;
    
    case 'update':
        if (!$isLoggedIn) {
            $response['success'] = true;
            $response['message'] = 'Update handled by localStorage (not logged in)';
            sendResponse($response);
        }
        
        // Required fields
        if (!isset($requestData['product_id']) || !isset($requestData['quantity_change'])) {
            $response['message'] = 'Missing required fields for update';
            sendResponse($response);
        }
        
        $productId = intval($requestData['product_id']);
        $quantityChange = intval($requestData['quantity_change']);
        
        try {
            // Get current quantity
            $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $currentQuantity = $row['quantity'];
                $newQuantity = $currentQuantity + $quantityChange;
                
                // Enforce maximum quantity limit
                if ($newQuantity > 100) {
                    $response['success'] = false;
                    $response['message'] = 'Maximum limit is 100 units per item.';
                    sendResponse($response);
                }
                
                // Handle zero or negative quantity (remove item)
                if ($newQuantity <= 0) {
                    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $userId, $productId);
                    $stmt->execute();
                } else {
                    // Update quantity
                    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $newQuantity, $userId, $productId);
                    $stmt->execute();
                }
                
                // Return success
                $response['success'] = true;
                $response['message'] = 'Cart updated';
                $response['data'] = [
                    'new_quantity' => $newQuantity > 0 ? $newQuantity : 0
                ];
            } else {
                $response['success'] = false;
                $response['message'] = 'Item not found in cart';
            }
        } catch (Exception $e) {
            $response['message'] = 'Exception: ' . $e->getMessage();
        }
        break;
    
    case 'remove':
        if (!$isLoggedIn) {
            $response['success'] = true;
            $response['message'] = 'Remove handled by localStorage (not logged in)';
            sendResponse($response);
        }
        
        // Required fields
        if (!isset($requestData['product_id'])) {
            $response['message'] = 'Missing product_id';
            sendResponse($response);
        }
        
        $productId = intval($requestData['product_id']);
        
        try {
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Item removed';
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $response['message'] = 'Exception: ' . $e->getMessage();
        }
        break;
    
    case 'clear':
        if (!$isLoggedIn) {
            $response['success'] = true;
            $response['message'] = 'Clear handled by localStorage (not logged in)';
            sendResponse($response);
        }
        
        try {
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cart cleared';
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $response['message'] = 'Exception: ' . $e->getMessage();
        }
        break;
        
    default:
        $response['message'] = 'Invalid action';
}

// Send response
sendResponse($response);

/**
 * Send JSON response and exit
 */
function sendResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>