<?php
/**
 * Azeu Water Station - Session Management
 * Session initialization, auth guards, and maintenance checks
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/logger.php';

// Run cleanup tasks (once per session)
run_cleanup_tasks();

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user data from session
 */
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'phone' => $_SESSION['phone'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'status' => $_SESSION['status'] ?? null
    ];
}

/**
 * Require user to be logged in
 */
function require_login() {
    if (!is_logged_in()) {
        logger_warning("Unauthorized access attempt - not logged in");
        redirect('../login.php');
    }
}

/**
 * Require user to have one of the specified roles
 */
function require_role($allowed_roles) {
    require_login();
    
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    $user_role = $_SESSION['role'] ?? null;
    
    if (!in_array($user_role, $allowed_roles)) {
        logger_warning("Access denied - insufficient permissions", [
            'required_roles' => implode(',', $allowed_roles),
            'user_role' => $user_role
        ]);
        redirect('../errors/403.php');
    }
}

/**
 * Check maintenance mode
 */
function check_maintenance() {
    // Skip if not logged in (maintenance.php will handle public access)
    if (!is_logged_in()) {
        return;
    }
    
    $maintenance_mode = get_setting('maintenance_mode');
    $user_role = $_SESSION['role'] ?? null;
    
    // Allow admin and super_admin to bypass maintenance mode
    $bypass_roles = [ROLE_ADMIN, ROLE_SUPER_ADMIN];
    
    if ($maintenance_mode == '1' && !in_array($user_role, $bypass_roles)) {
        redirect('../maintenance.php');
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = generate_token(32);
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF token (alias for generate_csrf_token)
 */
function get_csrf_token() {
    return generate_csrf_token();
}

/**
 * Run cleanup tasks (once per session or hourly)
 */
function run_cleanup_tasks() {
    // Check if cleanup has run recently (within the last hour)
    $last_cleanup = $_SESSION['last_cleanup'] ?? 0;
    $now = time();
    
    if ($now - $last_cleanup < 3600) {
        return; // Skip if run within last hour
    }
    
    global $pdo;
    if (!$pdo) {
        return;
    }
    
    // Update last cleanup timestamp
    $_SESSION['last_cleanup'] = $now;
    
    // Task 1: Delete expired pending accounts
    $pending_expiry_days = get_setting('pending_expiry_days') ?? 7;
    $stmt = $pdo->prepare("DELETE FROM users WHERE status = 'pending' AND DATEDIFF(NOW(), created_at) > ?");
    $deleted = $stmt->execute([$pending_expiry_days]);
    
    if ($stmt->rowCount() > 0) {
        logger_info("Deleted {$stmt->rowCount()} expired pending accounts");
    }
    
    // Task 2: Reset monthly cancellation counts
    $stmt = $pdo->query("SELECT id, cancellation_count, cancellation_reset_date 
                        FROM users 
                        WHERE role = 'customer' AND cancellation_reset_date <= CURDATE()");
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($customers as $customer) {
        // Reset count
        $updateStmt = $pdo->prepare("UPDATE users 
                                    SET cancellation_count = 0, 
                                        cancellation_reset_date = DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)
                                    WHERE id = ?");
        $updateStmt->execute([$customer['id']]);
    }
    
    if (count($customers) > 0) {
        logger_info("Reset cancellation counts for " . count($customers) . " customers");
    }
    
    // Task 3: Clean up old password reset tokens
    $stmt = $pdo->query("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");
    if ($stmt->rowCount() > 0) {
        logger_info("Deleted {$stmt->rowCount()} expired password reset tokens");
    }
    
    logger_debug("Cleanup tasks completed");
}

/**
 * Load system settings into session
 */
function load_system_settings() {
    if (!isset($_SESSION['system_settings']) || empty($_SESSION['system_settings'])) {
        global $pdo;
        if (!$pdo) {
            return;
        }
        
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $_SESSION['system_settings'] = $settings;
    }
}

// Load system settings on session start
load_system_settings();

/**
 * Refresh system settings cache
 */
function refresh_system_settings() {
    unset($_SESSION['system_settings']);
    load_system_settings();
}
