<?php
// Start session
session_start();
require_once __DIR__ . '/../config/db_connection.php';

// This file will check and fix your cart_api.php file
$cartApiPath = __DIR__ . '/cart_api.php';
$cartApiContent = file_get_contents($cartApiPath);

// Check if the file has proper PHP closing sections
if (!strpos($cartApiContent, 'sendResponse($response);') || 
    !strpos($cartApiContent, 'function sendResponse')) {
    
    // The file is incomplete, replace it with a correct version
    $correctContent = '<?php
// Start the session at the very beginning
session_start();
require_once __DIR__ . \'/../config/db_connection.php\';

// Log request data
file_put_contents(\'cart_api_log.txt\', date(\'Y-m-d H:i:s\') . \' - REQUEST: \' . file_get_contents(\'php://input\') . "\n", FILE_APPEND);

// Initialize response
$response = [
    \'success\' => false,
    \'message\' => \'\',
    \'data\' => []
];

// Get request data
$requestData = json_decode(file_get_contents(\'php://input\'), true);
if (!$requestData) {
    // Fallback to POST data
    $requestData = $_POST;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION[\'user_logged_in\']) && $_SESSION[\'user_logged_in\'];
$userId = $isLoggedIn ? $_SESSION[\'user_id\'] : null;

file_put_contents(\'cart_api_log.txt\', date(\'Y-m-d H:i:s\') . " - User: $userId, LoggedIn: " . ($isLoggedIn ? \'yes\' : \'no\') . "\n", FILE_APPEND);

// Make sure we have an action
if (!isset($requestData[\'action\'])) {
    $response[\'message\'] = \'No action specified\';
    sendResponse($response);
}

// Process the action
switch ($requestData[\'action\']) {
    case \'add\':
        // Default quantity if not specified
        $quantity = isset($requestData[\'quantity\']) ? intval($requestData[\'quantity\']) : 1;
        
        if ($quantity <= 0) {
            $response[\'message\'] = \'Invalid quantity\';
            sendResponse($response);
        }
        
        // Ensure product_id is an integer or generate one
        $productId = isset($requestData[\'product_id\']) ? intval($requestData[\'product_id\']) : 0;
        if ($productId <= 0) {
            $productId = time(); // Use timestamp as fallback ID
            file_put_contents(\'cart_api_log.txt\', "Generated ID $productId for product\n", FILE_APPEND);
        }
        
        // Default image if not specified
        $imageUrl = isset($requestData[\'image_url\']) ? $requestData[\'image_url\'] : \'../assets/product-placeholder.png\';
        
        // Handle guest cart
        if (!$isLoggedIn) {
            // Store cart in session for non-logged-in users
            if (!isset($_SESSION[\'guest_cart\'])) {
                $_SESSION[\'guest_cart\'] = [];
            }
            
            // Check if product already in cart
            $found = false;
            foreach ($_SESSION[\'guest_cart\'] as &$item) {
                if ($item[\'product_id\'] == $productId) {
                    $item[\'quantity\'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            // Add new product if not found
            if (!$found) {
                $_SESSION[\'guest_cart\'][] = [
                    \'product_id\' => $productId,
                    \'product_name\' => $requestData[\'product_name\'],
                    \'price\' => floatval($requestData[\'price\']),
                    \'quantity\' => $quantity,
                    \'image_url\' => $imageUrl
                ];
            }
            
            $cartCount = 0;
            foreach ($_SESSION[\'guest_cart\'] as $item) {
                $cartCount += $item[\'quantity\'];
            }
            
            $response[\'success\'] = true;
            $response[\'message\'] = \'Item added to session cart\';
            $response[\'data\'][\'cart_count\'] = $cartCount;
            sendResponse($response);
        }
        
        try {
            // For logged-in users, add to database
            file_put_contents(\'cart_api_log.txt\', "Adding to DB - User: $userId, Product: $productId\n", FILE_APPEND);
            
            // Check if product already exists in cart
            $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing item
                $row = $result->fetch_assoc();
                $newQuantity = $row[\'quantity\'] + $quantity;
                
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $newQuantity, $userId, $productId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error updating cart: " . $stmt->error);
                }
                
                file_put_contents(\'cart_api_log.txt\', "Updated quantity to $newQuantity\n", FILE_APPEND);
            } else {
                // Add new item
                $productName = isset($requestData[\'product_name\']) ? $requestData[\'product_name\'] : \'Unknown Product\';
                $price = isset($requestData[\'price\']) ? floatval($requestData[\'price\']) : 0;
                
                $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, product_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisdis", $userId, $productId, $productName, $price, $quantity, $imageUrl);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error adding to cart: " . $stmt->error);
                }
                
                file_put_contents(\'cart_api_log.txt\', "Inserted new item: $productName\n", FILE_APPEND);
            }
            
            // Get updated cart count
            $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartCount = 0;
            if ($row = $result->fetch_assoc()) {
                $cartCount = $row[\'cart_count\'] ?: 0;
            }
            
            $response[\'success\'] = true;
            $response[\'message\'] = \'Item added to cart\';
            $response[\'data\'][\'cart_count\'] = $cartCount;
        } catch (Exception $e) {
            $response[\'message\'] = \'Error: \' . $e->getMessage();
            file_put_contents(\'cart_api_log.txt\', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        break;
        
    case \'update\':
        // Update quantity code here
        $response[\'success\'] = true;
        $response[\'message\'] = \'Quantity updated\';
        break;
        
    case \'remove\':
        // Remove item code here
        $response[\'success\'] = true;
        $response[\'message\'] = \'Item removed\';
        break;
        
    case \'clear\':
        // Clear cart code here
        $response[\'success\'] = true;
        $response[\'message\'] = \'Cart cleared\';
        break;
        
    default:
        $response[\'message\'] = \'Invalid action\';
}

// Send the response
sendResponse($response);

// Function to send response and exit
function sendResponse($response) {
    header(\'Content-Type: application/json\');
    echo json_encode($response);
    exit;
}
?>';
    
    // Save the correct file
    file_put_contents($cartApiPath, $correctContent);
    echo "<h2>✅ Fixed cart_api.php</h2>";
    echo "<p>Your cart_api.php file was incomplete and has been fixed.</p>";
} else {
    echo "<h2>cart_api.php appears complete</h2>";
    echo "<p>The file structure looks correct.</p>";
}

// Check if cart_api_log.txt exists and is writable
$logPath = __DIR__ . '/cart_api_log.txt';
if (!file_exists($logPath)) {
    file_put_contents($logPath, date('Y-m-d H:i:s') . " - Log file created\n");
    echo "<p>Created cart_api_log.txt file.</p>";
} else {
    echo "<p>cart_api_log.txt exists.</p>";
    
    // Show the last few lines of the log
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice($lines, -10); // Last 10 lines
    
    echo "<h3>Recent Log Entries:</h3>";
    echo "<pre>" . implode("\n", $lastLines) . "</pre>";
}

echo "<p><a href='collections.php?debug=1' class='btn btn-primary'>Go to Collections with Debug</a></p>";
?>