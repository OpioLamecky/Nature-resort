<?php
/**
 * Production Error Handler
 * Comprehensive error handling and logging for production
 */

class ProductionErrorHandler {
    private bool $debug;
    private string $logPath;
    private array $errorTypes = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];
    
    public function __construct(bool $debug = false, string $logPath = null) {
        $this->debug = $debug;
        $this->logPath = $logPath ?? (__DIR__ . '/../logs/');
        
        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        // Set error and exception handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool {
        // Don't handle errors that are suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorType = $this->errorTypes[$errno] ?? 'Unknown Error';
        
        $errorData = [
            'type' => $errorType,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'post_data' => $this->sanitizePostData(),
            'session_data' => $this->sanitizeSessionData(),
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];
        
        // Log the error
        $this->logError($errorData);
        
        // Display error page or continue
        if ($this->isFatalError($errno)) {
            $this->displayErrorPage($errorData);
            exit(1);
        }
        
        return true;
    }
    
    public function handleException(Throwable $exception): void {
        $errorData = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'post_data' => $this->sanitizePostData(),
            'session_data' => $this->sanitizeSessionData(),
            'stack_trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? get_class($exception->getPrevious()) : null
        ];
        
        $this->logError($errorData);
        $this->displayErrorPage($errorData);
    }
    
    public function handleShutdown(): void {
        $error = error_get_last();
        if ($error && $this->isFatalError($error['type'])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }
    
    private function isFatalError(int $type): bool {
        return in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
    }
    
    private function logError(array $errorData): void {
        $logFile = $this->logPath . 'error_' . date('Y-m-d') . '.log';
        
        $logEntry = json_encode($errorData) . "\n" . str_repeat('-', 80) . "\n";
        
        // Write to file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP error log
        $message = sprintf(
            "[%s] %s in %s on line %d: %s",
            $errorData['timestamp'],
            $errorData['type'],
            $errorData['file'],
            $errorData['line'],
            $errorData['message']
        );
        
        error_log($message);
        
        // Send critical errors to admin email (if configured)
        if ($this->isFatalError($this->getErrorTypeFromName($errorData['type']))) {
            $this->notifyAdmin($errorData);
        }
    }
    
    private function getErrorTypeFromName(string $typeName): int {
        $reverseTypes = array_flip($this->errorTypes);
        return $reverseTypes[$typeName] ?? E_USER_ERROR;
    }
    
    private function sanitizePostData(): array {
        $sensitiveKeys = ['password', 'pass', 'secret', 'token', 'key', 'csrf'];
        $sanitized = [];
        
        foreach ($_POST as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = is_string($value) ? substr($value, 0, 200) : $value;
            }
        }
        
        return $sanitized;
    }
    
    private function sanitizeSessionData(): array {
        $allowedKeys = ['user_id', 'username', 'role', 'last_activity'];
        $sanitized = [];
        
        foreach ($allowedKeys as $key) {
            if (isset($_SESSION[$key])) {
                $sanitized[$key] = $_SESSION[$key];
            }
        }
        
        return $sanitized;
    }
    
    private function displayErrorPage(array $errorData): void {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Set proper HTTP status code
        http_response_code(500);
        
        if ($this->debug) {
            // Show detailed error in debug mode
            $this->displayDebugError($errorData);
        } else {
            // Show user-friendly error in production
            $this->displayProductionError($errorData);
        }
    }
    
    private function displayDebugError(array $errorData): void {
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Error - Debug Mode</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .error-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-type { color: #d32f2f; font-weight: bold; }
        .error-message { font-size: 18px; margin: 10px 0; }
        .error-details { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .stack-trace { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-type">' . htmlspecialchars($errorData['type']) . '</h1>
        <div class="error-message">' . htmlspecialchars($errorData['message']) . '</div>
        
        <div class="error-details">
            <strong>File:</strong> ' . htmlspecialchars($errorData['file']) . '<br>
            <strong>Line:</strong> ' . htmlspecialchars($errorData['line']) . '<br>
            <strong>Time:</strong> ' . htmlspecialchars($errorData['timestamp']) . '<br>
            <strong>Request:</strong> ' . htmlspecialchars($errorData['request_uri']) . '
        </div>';
        
        if (!empty($errorData['stack_trace'])) {
            echo '<h3>Stack Trace:</h3>
            <div class="stack-trace">' . htmlspecialchars(is_string($errorData['stack_trace']) ? $errorData['stack_trace'] : print_r($errorData['stack_trace'], true)) . '</div>';
        }
        
        echo '</div></body></html>';
    }
    
    private function displayProductionError(array $errorData): void {
        // Load error page template
        $errorPage = __DIR__ . '/../public/error-500.php';
        
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            // Fallback error page
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Service Temporarily Unavailable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; color: #333; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #d32f2f; margin-bottom: 20px; }
        p { line-height: 1.6; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Service Temporarily Unavailable</h1>
        <p>We\'re sorry, but something went wrong. Our team has been notified and is working to fix the issue.</p>
        <p>Please try again in a few moments, or contact our support team if the problem persists.</p>
        <a href="/" class="btn">Return to Homepage</a>
    </div>
</body>
</html>';
        }
    }
    
    private function notifyAdmin(array $errorData): void {
        $to = 'admin@kalitusiresort.com'; // Configure this
        $subject = 'Critical Error on Kalitusi Resort Website';
        
        $message = sprintf(
            "A critical error occurred on the website:\n\n" .
            "Type: %s\n" .
            "Message: %s\n" .
            "File: %s\n" .
            "Line: %d\n" .
            "Time: %s\n" .
            "Request: %s\n" .
            "IP: %s\n\n" .
            "Please check the error logs for more details.",
            $errorData['type'],
            $errorData['message'],
            $errorData['file'],
            $errorData['line'],
            $errorData['timestamp'],
            $errorData['request_uri'],
            $errorData['remote_addr']
        );
        
        $headers = 'From: errors@kalitusiresort.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        @mail($to, $subject, $message, $headers);
    }
    
    /**
     * Log custom events
     */
    public function logEvent(string $event, array $context = [], string $level = 'info'): void {
        $logData = [
            'event' => $event,
            'level' => $level,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'context' => $context
        ];
        
        $logFile = $this->logPath . 'events_' . date('Y-m-d') . '.log';
        $logEntry = json_encode($logData) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get error statistics
     */
    public function getErrorStats(int $days = 7): array {
        $stats = [
            'total_errors' => 0,
            'fatal_errors' => 0,
            'warnings' => 0,
            'notices' => 0,
            'by_type' => [],
            'by_hour' => array_fill(0, 24, 0)
        ];
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $logFile = $this->logPath . 'error_' . $date . '.log';
            
            if (file_exists($logFile)) {
                $content = file_get_contents($logFile);
                $lines = explode("\n", $content);
                
                foreach ($lines as $line) {
                    if (empty($line) || $line === str_repeat('-', 80)) {
                        continue;
                    }
                    
                    $error = json_decode($line, true);
                    if ($error) {
                        $stats['total_errors']++;
                        
                        $type = $error['type'] ?? 'Unknown';
                        $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
                        
                        if (strpos(strtolower($type), 'error') !== false) {
                            $stats['fatal_errors']++;
                        } elseif (strpos(strtolower($type), 'warning') !== false) {
                            $stats['warnings']++;
                        } elseif (strpos(strtolower($type), 'notice') !== false) {
                            $stats['notices']++;
                        }
                        
                        $hour = (int)date('H', strtotime($error['timestamp']));
                        $stats['by_hour'][$hour]++;
                    }
                }
            }
        }
        
        return $stats;
    }
}

// Initialize error handler
$config = require __DIR__ . '/config.php';
$errorHandler = new ProductionErrorHandler($config['debug'] ?? false);

/**
 * Helper function to log events
 */
function log_event(string $event, array $context = [], string $level = 'info'): void {
    global $errorHandler;
    $errorHandler->logEvent($event, $context, $level);
}
