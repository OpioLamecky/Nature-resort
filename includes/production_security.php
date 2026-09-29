<?php
/**
 * Production Security Enhancements
 * Comprehensive security measures for production deployment
 */

// Check if we're in local development
function is_local_development(): bool {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? '';
    return (strpos($host, 'localhost') !== false || 
            strpos($host, '127.0.0.1') !== false || 
            strpos($host, '::1') !== false ||
            $scheme === 'http');
}

// Enhanced session security
function secure_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    $isLocal = is_local_development();
    
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $isLocal ? 0 : 1);
    ini_set('session.cookie_samesite', $isLocal ? 'Lax' : 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 7200); // 2 hours
    ini_set('session.cookie_lifetime', 7200);
    
    // Set secure session name
    session_name('KALITUSI_SESS');
    
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Enhanced input validation
function validate_input(string $input, string $type = 'string'): array {
    $result = ['valid' => false, 'value' => null, 'error' => null];
    
    if (empty($input)) {
        $result['error'] = 'Input cannot be empty';
        return $result;
    }
    
    switch ($type) {
        case 'email':
            $email = filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['valid'] = true;
                $result['value'] = $email;
            } else {
                $result['error'] = 'Invalid email format';
            }
            break;
            
        case 'name':
            $name = trim(preg_replace('/[^a-zA-Z\s\-\'\.]/u', '', $input));
            if (strlen($name) >= 2 && strlen($name) <= 100) {
                $result['valid'] = true;
                $result['value'] = $name;
            } else {
                $result['error'] = 'Name must be 2-100 characters';
            }
            break;
            
        case 'phone':
            $phone = preg_replace('/[^\d\+\-\s\(\)]/', '', $input);
            if (preg_match('/^[\+]?[\d\s\-\(\)]{10,}$/', $phone)) {
                $result['valid'] = true;
                $result['value'] = $phone;
            } else {
                $result['error'] = 'Invalid phone number format';
            }
            break;
            
        case 'text':
            $text = trim($input);
            if (strlen($text) >= 1 && strlen($text) <= 5000) {
                $result['valid'] = true;
                $result['value'] = $text;
            } else {
                $result['error'] = 'Text must be 1-5000 characters';
            }
            break;
            
        case 'numeric':
            if (is_numeric($input)) {
                $result['valid'] = true;
                $result['value'] = (float)$input;
            } else {
                $result['error'] = 'Invalid numeric value';
            }
            break;
            
        case 'integer':
            if (filter_var($input, FILTER_VALIDATE_INT) !== false) {
                $result['valid'] = true;
                $result['value'] = (int)$input;
            } else {
                $result['error'] = 'Invalid integer value';
            }
            break;
            
        default:
            $result['valid'] = true;
            $result['value'] = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    return $result;
}

// Enhanced rate limiting with database storage
function rate_limit_enforce(string $action, int $maxAttempts = 5, int $decayMinutes = 15): array {
    global $pdo;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = md5($action . $ip);
    
    try {
        // Clean old attempts
        $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([$decayMinutes]);
        
        // Check current attempts
        $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM rate_limits WHERE action_key = ?");
        $stmt->execute([$key]);
        $attempts = $stmt->fetchColumn();
        
        if ($attempts >= $maxAttempts) {
            return ['allowed' => false, 'attempts' => $attempts, 'remaining' => 0];
        }
        
        // Log this attempt
        $stmt = $pdo->prepare("INSERT INTO rate_limits (action_key, ip_address, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$key, $ip]);
        
        return ['allowed' => true, 'attempts' => $attempts + 1, 'remaining' => $maxAttempts - $attempts - 1];
        
    } catch (Exception $e) {
        error_log("Rate limiting error: " . $e->getMessage());
        // Fallback to session-based rate limiting
        return rate_limit_fallback($action, $maxAttempts, $decayMinutes);
    }
}

// Fallback rate limiting using session
function rate_limit_fallback(string $action, int $maxAttempts = 5, int $decayMinutes = 15): array {
    $key = "rate_limit_{$action}";
    $attempts = $_SESSION[$key] ?? [];
    $now = time();
    
    // Remove old attempts
    $attempts = array_filter($attempts, fn($time) => $time > ($now - ($decayMinutes * 60)));
    
    if (count($attempts) >= $maxAttempts) {
        return ['allowed' => false, 'attempts' => count($attempts), 'remaining' => 0];
    }
    
    $attempts[] = $now;
    $_SESSION[$key] = $attempts;
    
    return ['allowed' => true, 'attempts' => count($attempts), 'remaining' => $maxAttempts - count($attempts)];
}

// Enhanced CSRF protection with double-submit cookie
function csrf_generate_tokens(): array {
    if (session_status() === PHP_SESSION_NONE) {
        secure_session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    
    $isLocal = is_local_development();
    
    // Set cookie with appropriate security settings
    $cookieOptions = [
        'expires' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isLocal ? false : true,
        'httponly' => true,
        'samesite' => $isLocal ? 'Lax' : 'Strict'
    ];
    
    setcookie('csrf_token', $token, $cookieOptions);
    
    return ['session' => $token, 'cookie' => $token];
}

function csrf_verify_request(): bool {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $cookieToken = $_COOKIE['csrf_token'] ?? '';
    $requestToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    return !empty($sessionToken) && 
           !empty($cookieToken) && 
           !empty($requestToken) &&
           hash_equals($sessionToken, $cookieToken) &&
           hash_equals($sessionToken, $requestToken);
}

// Security headers for API responses
function set_security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; " .
           "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com; " .
           "font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com; " .
           "img-src 'self' data: https:; " .
           "connect-src 'self'; " .
           "frame-src 'self' https://*.google.com; " .
           "frame-ancestors 'none'; " .
           "base-uri 'self'; " .
           "form-action 'self';";
    header("Content-Security-Policy: {$csp}");
}

// Enhanced password validation
function validate_password_strength(string $password): array {
    $result = ['valid' => false, 'score' => 0, 'issues' => []];
    
    if (strlen($password) < 8) {
        $result['issues'][] = 'Password must be at least 8 characters';
    } else {
        $result['score'] += 20;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $result['issues'][] = 'Password must contain uppercase letter';
    } else {
        $result['score'] += 20;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $result['issues'][] = 'Password must contain lowercase letter';
    } else {
        $result['score'] += 20;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $result['issues'][] = 'Password must contain number';
    } else {
        $result['score'] += 20;
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $result['issues'][] = 'Password must contain special character';
    } else {
        $result['score'] += 20;
    }
    
    $result['valid'] = empty($result['issues']) && $result['score'] >= 80;
    
    return $result;
}

// SQL injection prevention helper
function sanitize_sql_param(string $param): string {
    return trim($param);
}

// Log security events
function log_security_event(string $event, array $context = []): void {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user']['id'] ?? null,
        'context' => $context
    ];
    
    error_log('SECURITY: ' . json_encode($logEntry));
    
    // Store in database if available
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO security_logs (event_type, ip_address, user_agent, user_id, context, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $event,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $_SESSION['user']['id'] ?? null,
            json_encode($context)
        ]);
    } catch (Exception $e) {
        error_log("Failed to log security event to database: " . $e->getMessage());
    }
}

// Initialize security measures - call this manually when needed
// secure_session_start();
// set_security_headers();

// Create rate limits table if not exists
try {
    global $pdo;
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action_key VARCHAR(64) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action_key (action_key),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS security_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            user_id INT,
            context JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_type (event_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {
    error_log("Failed to create security tables: " . $e->getMessage());
}
