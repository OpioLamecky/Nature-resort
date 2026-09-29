<?php
/**
 * Role-Based Access Control (RBAC) System
 * Manages permissions for different user roles
 */

// Define role hierarchy (higher value = more permissions)
define('ROLE_HIERARCHY', [
    'Super Admin' => 100,
    'Manager' => 80,
    'Receptionist' => 60,
    'Content Manager' => 50,
    'Staff' => 30,
    'Guest' => 10
]);

// Define permissions for each module
define('PERMISSIONS', [
    // Booking Management
    'bookings.view' => ['Super Admin', 'Manager', 'Receptionist'],
    'bookings.create' => ['Super Admin', 'Manager', 'Receptionist'],
    'bookings.edit' => ['Super Admin', 'Manager', 'Receptionist'],
    'bookings.delete' => ['Super Admin', 'Manager'],
    'bookings.cancel' => ['Super Admin', 'Manager', 'Receptionist'],
    'bookings.confirm' => ['Super Admin', 'Manager', 'Receptionist'],
    
    // Accommodation Management
    'accommodations.view' => ['Super Admin', 'Manager', 'Receptionist'],
    'accommodations.create' => ['Super Admin', 'Manager'],
    'accommodations.edit' => ['Super Admin', 'Manager'],
    'accommodations.delete' => ['Super Admin'],
    'accommodations.status' => ['Super Admin', 'Manager', 'Receptionist'],
    
    // User/Staff Management
    'users.view' => ['Super Admin', 'Manager'],
    'users.create' => ['Super Admin', 'Manager'],
    'users.edit' => ['Super Admin', 'Manager'],
    'users.delete' => ['Super Admin'],
    'users.roles' => ['Super Admin'],
    
    // Payment Management
    'payments.view' => ['Super Admin', 'Manager', 'Receptionist'],
    'payments.create' => ['Super Admin', 'Manager', 'Receptionist'],
    'payments.edit' => ['Super Admin', 'Manager'],
    'payments.delete' => ['Super Admin'],
    
    // Content Management
    'content.view' => ['Super Admin', 'Content Manager', 'Manager'],
    'content.create' => ['Super Admin', 'Content Manager', 'Manager'],
    'content.edit' => ['Super Admin', 'Content Manager', 'Manager'],
    'content.delete' => ['Super Admin', 'Content Manager'],
    'content.publish' => ['Super Admin', 'Content Manager'],
    
    // Analytics & Reports
    'analytics.view' => ['Super Admin', 'Manager'],
    'analytics.export' => ['Super Admin', 'Manager'],
    
    // Testimonials
    'testimonials.view' => ['Super Admin', 'Manager', 'Content Manager'],
    'testimonials.approve' => ['Super Admin', 'Manager'],
    'testimonials.delete' => ['Super Admin'],
    
    // Settings
    'settings.view' => ['Super Admin', 'Manager'],
    'settings.edit' => ['Super Admin'],
]);

/**
 * Check if current user has permission
 */
function has_permission($permission) {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    $userRole = $_SESSION['user']['role'] ?? 'Guest';
    
    // Super Admin has all permissions
    if ($userRole === 'Super Admin') {
        return true;
    }
    
    // Check if permission exists
    if (!isset(PERMISSIONS[$permission])) {
        return false;
    }
    
    // Check if user's role has this permission
    return in_array($userRole, PERMISSIONS[$permission]);
}

/**
 * Require permission (redirect if not authorized)
 */
function require_permission($permission, $redirectUrl = null) {
    if (!has_permission($permission)) {
        flash('You do not have permission to access this resource.', 'danger');
        $redirectUrl = $redirectUrl ?? base_url('admin/dashboard.php');
        redirect($redirectUrl);
        exit;
    }
}

/**
 * Check if user has role
 */
function has_role($role) {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    $userRole = $_SESSION['user']['role'] ?? 'Guest';
    return $userRole === $role;
}

/**
 * Check if user has at least this role level
 */
function has_role_level($minRole) {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    $userRole = $_SESSION['user']['role'] ?? 'Guest';
    $userLevel = ROLE_HIERARCHY[$userRole] ?? 0;
    $requiredLevel = ROLE_HIERARCHY[$minRole] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Get all available roles
 */
function get_all_roles() {
    return array_keys(ROLE_HIERARCHY);
}

/**
 * Get user's permissions list
 */
function get_user_permissions($role = null) {
    $role = $role ?? ($_SESSION['user']['role'] ?? 'Guest');
    
    if ($role === 'Super Admin') {
        return array_keys(PERMISSIONS);
    }
    
    $userPermissions = [];
    foreach (PERMISSIONS as $permission => $roles) {
        if (in_array($role, $roles)) {
            $userPermissions[] = $permission;
        }
    }
    
    return $userPermissions;
}

/**
 * Check if user can manage another user (based on role hierarchy)
 */
function can_manage_user($targetUserRole) {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    $currentUserRole = $_SESSION['user']['role'] ?? 'Guest';
    $currentLevel = ROLE_HIERARCHY[$currentUserRole] ?? 0;
    $targetLevel = ROLE_HIERARCHY[$targetUserRole] ?? 0;
    
    // Can only manage users with lower role level
    return $currentLevel > $targetLevel;
}

/**
 * Get accessible roles for current user (roles they can assign to others)
 */
function get_assignable_roles() {
    if (!isset($_SESSION['user'])) {
        return [];
    }
    
    $currentUserRole = $_SESSION['user']['role'] ?? 'Guest';
    $currentLevel = ROLE_HIERARCHY[$currentUserRole] ?? 0;
    
    $assignableRoles = [];
    foreach (ROLE_HIERARCHY as $role => $level) {
        // Can only assign roles lower than current user's role
        if ($level < $currentLevel) {
            $assignableRoles[] = $role;
        }
    }
    
    return $assignableRoles;
}

/**
 * Log admin action for audit trail
 */
function log_admin_action($action, $details = null, $relatedId = null) {
    global $pdo;
    
    if (!isset($_SESSION['user'])) {
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (user_id, action, details, related_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user']['user_id'],
            $action,
            is_array($details) ? json_encode($details) : $details,
            $relatedId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}
