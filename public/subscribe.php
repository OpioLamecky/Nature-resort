<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['success' => false, 'message' => ''];

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Get JSON input or form data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    // Validate required fields
    $email = trim($input['email'] ?? '');
    $name = trim($input['name'] ?? '');

    if (empty($email)) {
        throw new Exception('Email address is required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    if (strlen($email) > 191) {
        throw new Exception('Email address is too long (maximum 191 characters)');
    }

    // Generate unsubscribe token
    $unsubscribeToken = bin2hex(random_bytes(32));
    
    // Get client information
    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT subscription_id, status FROM newsletter_subscriptions WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'active') {
            $response['message'] = 'You are already subscribed to our newsletter!';
            $response['success'] = true;
        } else {
            // Reactivate subscription
            $stmt = $pdo->prepare("
                UPDATE newsletter_subscriptions 
                SET status = 'active', 
                    name = ?, 
                    subscription_date = CURRENT_TIMESTAMP,
                    unsubscribe_token = ?,
                    ip_address = ?,
                    user_agent = ?
                WHERE email = ?
            ");
            $stmt->execute([$name, $unsubscribeToken, $ipAddress, $userAgent, $email]);
            
            $response['message'] = 'Welcome back! Your newsletter subscription has been reactivated.';
            $response['success'] = true;
        }
    } else {
        // Create new subscription
        $stmt = $pdo->prepare("
            INSERT INTO newsletter_subscriptions (email, name, unsubscribe_token, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$email, $name, $unsubscribeToken, $ipAddress, $userAgent]);
        
        $response['message'] = 'Thank you for subscribing to our newsletter! You will receive updates about Kalitusi Nature Resort.';
        $response['success'] = true;
    }

    // Log successful subscription (optional)
    error_log("Newsletter subscription: {$email} from IP: {$ipAddress}");

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Newsletter subscription error: " . $e->getMessage());
}

echo json_encode($response);
exit;
