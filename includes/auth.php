<?php
// Auth helpers
require_once __DIR__ . '/database.php';

function login(string $email, string $password): bool {
    global $pdo;
    
    try {
        // Log the login attempt
        error_log("Attempting login for email: " . $email);
        
        // Check if the users table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
        if (!$tableExists) {
            error_log("Error: 'users' table does not exist in the database.");
            return false;
        }
        
        // Prepare and execute the query
        $stmt = $pdo->prepare('SELECT user_id, username, email, password_hash, role, department, profile_photo FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            $error = $pdo->errorInfo();
            error_log("Database error: " . print_r($error, true));
            return false;
        }
        
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            error_log("No user found with email: " . $email);
            return false;
        }
        
        if (!isset($user['password_hash'])) {
            error_log("Error: 'password_hash' column not found in the users table.");
            return false;
        }
        
        // Verify the password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, log the user in
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            session_regenerate_id(true);
            
            // Update last_login
            try {
                $updateStmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE user_id = ?');
                $updateStmt->execute([$user['user_id']]);
            } catch (PDOException $e) {
                error_log("Failed to update last_login: " . $e->getMessage());
                // Continue with login even if this fails
            }
            
            // Log login activity if logger exists
            if (file_exists(__DIR__ . '/activity_logger.php')) {
                require_once __DIR__ . '/activity_logger.php';
                log_activity('login', 'User logged into the system', 'auth');
            }
            
            error_log("Login successful for user: " . $user['email']);
            return true;
        } else {
            error_log("Password verification failed for user: " . $email);
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in login(): " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("Unexpected error in login(): " . $e->getMessage());
        return false;
    }
}

function logout(): void {
    // Log logout activity before destroying session
    if (isset($_SESSION['user']) && file_exists(__DIR__ . '/activity_logger.php')) {
        require_once __DIR__ . '/activity_logger.php';
        log_activity('logout', 'User logged out of the system', 'auth');
    }
    
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
