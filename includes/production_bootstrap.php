<?php
/**
 * Production Bootstrap with Enhanced Security
 * Initializes all production-ready components
 */

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize security components
require_once __DIR__ . '/production_security.php';
require_once __DIR__ . '/enhanced_validation.php';
require_once __DIR__ . '/secure_database.php';
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/enhanced_file_upload.php';

// Initialize secure database
global $secure_db;
$secure_db = init_secure_database();

// Set production headers
set_security_headers();

// Initialize error handler
$errorHandler = new ProductionErrorHandler($config['debug'] ?? false);

// Log application start
log_event('application_bootstrap', [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
]);

// Initialize session with enhanced security
secure_session_start();

// Check for maintenance mode
$maintenanceFile = __DIR__ . '/../maintenance.enabled';
if (file_exists($maintenanceFile)) {
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(503);
        include __DIR__ . '/../public/maintenance.php';
        exit;
    }
}

// Load core functions
require_once __DIR__ . '/functions.php';

// Initialize CSRF tokens for all requests
csrf_generate_tokens();

// Set timezone
date_default_timezone_set('Africa/Kampala');

// Configure PHP for production
ini_set('display_errors', $config['debug'] ?? false ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Performance monitoring
$start_time = microtime(true);
register_shutdown_function(function() use ($start_time) {
    $execution_time = microtime(true) - $start_time;
    if ($execution_time > 5.0) { // Log slow requests
        log_event('slow_request', [
            'execution_time' => $execution_time,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'memory_usage' => memory_get_peak_usage(true)
        ], 'warning');
    }
});

// Security: Check for common attack patterns
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$suspiciousPatterns = [
    '/\.\./',           // Directory traversal
    '/<script/i',       // XSS attempts
    '/union.*select/i', // SQL injection attempts
    '/cmd=/i',          // Command injection
    '/eval\(/i',        // Code injection
];

foreach ($suspiciousPatterns as $pattern) {
    if (preg_match($pattern, $request_uri) || preg_match($pattern, $user_agent)) {
        log_security_event('suspicious_request_detected', [
            'pattern' => $pattern,
            'request_uri' => $request_uri,
            'user_agent' => $user_agent
        ]);
        
        // Block suspicious requests
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
}

// Initialize rate limiting for all requests
$rateLimit = rate_limit_enforce('general_request', 100, 60); // 100 requests per minute
if (!$rateLimit['allowed']) {
    log_security_event('general_rate_limit_exceeded', ['attempts' => $rateLimit['attempts']]);
    http_response_code(429);
    echo 'Too many requests';
    exit;
}

// Auto-include authentication if accessing admin area
if (strpos($request_uri, '/admin/') !== false) {
    require_once __DIR__ . '/auth.php';
    
    // Check if user is logged in for admin areas
    if (!is_logged_in()) {
        $currentUrl = base_url($request_uri);
        $loginUrl = base_url('public/login.php?next=' . urlencode($currentUrl));
        header('Location: ' . $loginUrl);
        exit;
    }
    
    // Log admin access
    log_security_event('admin_access', [
        'user_id' => $_SESSION['user']['id'] ?? null,
        'request_uri' => $request_uri
    ]);
}
