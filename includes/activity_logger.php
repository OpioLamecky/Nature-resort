<?php
/**
 * Activity Logger
 * Tracks user actions throughout the system
 */

// Guard against multiple inclusions
if (function_exists('log_activity')) {
    return;
}

/**
 * Log user activity
 * 
 * @param string $action The action performed (e.g., 'login', 'profile_update', 'booking_created')
 * @param string|null $description Optional detailed description
 * @param string|null $entityType Optional entity type (e.g., 'booking', 'user', 'post')
 * @param int|null $entityId Optional entity ID
 * @return bool Success status
 */
function log_activity(string $action, ?string $description = null, ?string $entityType = null, ?int $entityId = null): bool {
    global $pdo;
    
    try {
        // Get user ID from session
        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId) {
            return false; // Can't log without a user
        }
        
        // Get IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        
        // Get user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Insert activity log
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (user_id, action, description, entity_type, entity_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId,
            $action,
            $description,
            $entityType,
            $entityId,
            $ipAddress,
            $userAgent
        ]);
        
    } catch (Exception $e) {
        error_log("Activity logging error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user activity logs
 * 
 * @param int $userId User ID
 * @param int $limit Number of logs to retrieve
 * @param int $offset Offset for pagination
 * @return array Activity logs
 */
function get_user_activity(int $userId, int $limit = 50, int $offset = 0): array {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                log_id,
                action,
                description,
                entity_type,
                entity_id,
                ip_address,
                created_at
            FROM activity_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Get activity error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent system activity (for admins)
 * 
 * @param int $limit Number of logs to retrieve
 * @return array Activity logs with user information
 */
function get_recent_system_activity(int $limit = 100): array {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.log_id,
                a.action,
                a.description,
                a.entity_type,
                a.entity_id,
                a.ip_address,
                a.created_at,
                u.username,
                u.email,
                u.role
            FROM activity_logs a
            INNER JOIN users u ON a.user_id = u.user_id
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Get system activity error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get activity count for a user
 * 
 * @param int $userId User ID
 * @return int Total activity count
 */
function get_user_activity_count(int $userId): int {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
        
    } catch (Exception $e) {
        error_log("Get activity count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Format action name for display
 * 
 * @param string $action Action name
 * @return string Formatted action name
 */
function format_action_name(string $action): string {
    $formatted = str_replace('_', ' ', $action);
    return ucwords($formatted);
}

/**
 * Get action icon
 * 
 * @param string $action Action name
 * @return string Font Awesome icon class
 */
function get_action_icon(string $action): string {
    $icons = [
        'login' => 'fa-sign-in-alt',
        'logout' => 'fa-sign-out-alt',
        'profile_update' => 'fa-user-edit',
        'password_change' => 'fa-key',
        'photo_upload' => 'fa-camera',
        'booking_created' => 'fa-calendar-plus',
        'booking_updated' => 'fa-calendar-check',
        'booking_cancelled' => 'fa-calendar-times',
        'post_created' => 'fa-file-alt',
        'post_updated' => 'fa-edit',
        'post_deleted' => 'fa-trash',
        'user_created' => 'fa-user-plus',
        'user_updated' => 'fa-user-cog',
        'user_deleted' => 'fa-user-times',
        'settings_updated' => 'fa-cog',
    ];
    
    return $icons[$action] ?? 'fa-circle';
}

/**
 * Get action color class
 * 
 * @param string $action Action name
 * @return string Bootstrap color class
 */
function get_action_color(string $action): string {
    if (str_contains($action, 'delete') || str_contains($action, 'cancel')) {
        return 'danger';
    } elseif (str_contains($action, 'create') || str_contains($action, 'add')) {
        return 'success';
    } elseif (str_contains($action, 'update') || str_contains($action, 'edit')) {
        return 'primary';
    } elseif (str_contains($action, 'login')) {
        return 'info';
    } elseif (str_contains($action, 'logout')) {
        return 'secondary';
    }
    
    return 'dark';
}
