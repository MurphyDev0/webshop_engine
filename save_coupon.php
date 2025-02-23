<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit;
}

// Check if coupon was provided
if (!isset($_POST['coupon'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No coupon provided'
    ]);
    exit;
}

try {
    $coupon = $_POST['coupon'];
    $user_id = $_SESSION['user_id'];
    
    // Handle different types of coupons
    if ($coupon === 'Free Shipping') {
        $discount = 100;
        $type = 'shipping';
    } else {
        // Extract percentage from strings like "10% OFF"
        $discount = intval(str_replace(['%', ' OFF'], '', $coupon));
        $type = 'percentage';
    }
    
    // Generate unique coupon code
    $coupon_code = uniqid('SPIN_') . '_' . strtoupper(substr(md5(time()), 0, 6));
    
    // Get current date and set expiration date (30 days from now)
    $current_date = date('Y-m-d H:i:s');
    $expiry_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("
        INSERT INTO coupons (
            code, 
            discount_percent, 
            type,
            user_id, 
            created_at,
            expires_at,
            is_active
        ) VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    
    $stmt->bind_param(
        "sissssi", 
        $coupon_code, 
        $discount, 
        $type,
        $user_id, 
        $current_date,
        $expiry_date
    );
    
    if ($stmt->execute()) {
        // Log the coupon creation
        $log_stmt = $conn->prepare("
            INSERT INTO coupon_logs (
                coupon_code, 
                user_id, 
                action, 
                action_date
            ) VALUES (?, ?, 'created', NOW())
        ");
        $log_stmt->bind_param("si", $coupon_code, $user_id);
        $log_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'coupon_code' => $coupon_code,
            'message' => 'Coupon saved successfully',
            'expires' => $expiry_date,
            'discount' => $discount,
            'type' => $type
        ]);
    } else {
        throw new Exception("Failed to save coupon");
    }
    
} catch (Exception $e) {
    error_log("Error saving coupon: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while saving the coupon'
    ]);
}

// Close database connection
$conn->close();
?>