<?php
/**
 * Azeu Water Station - Utility Functions
 * Shared helper functions used throughout the system
 */

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/logger.php';

/**
 * Sanitize user input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format datetime to readable format in Manila timezone
 */
function format_date($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    $date = new DateTime($datetime);
    $date->setTimezone(new DateTimeZone('Asia/Manila'));
    return $date->format('M d, Y h:i A');
}

/**
 * Format currency with peso sign
 */
function format_currency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Generate cryptographically secure random token
 */
function generate_token($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Convert datetime to "time ago" format
 */
function time_ago($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return format_date($datetime);
    }
}

/**
 * Get setting value from database (with caching)
 */
function get_setting($key) {
    static $settings = null;
    
    if ($settings === null) {
        global $pdo;
        if (!$pdo) {
            return null;
        }
        
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? null;
}

/**
 * Update setting in database
 */
function update_setting($key, $value) {
    global $pdo;
    if (!$pdo) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    $result = $stmt->execute([$key, $value, $value]);
    
    // Clear cache
    get_setting('__clear_cache__');
    
    return $result;
}

/**
 * Get CSS class for status badge
 */
function get_status_badge_class($status) {
    return 'badge-' . str_replace('_', '-', $status);
}

/**
 * Get CSS class for role badge
 */
function get_role_badge_class($role) {
    return 'badge-' . str_replace('_', '-', $role);
}

/**
 * Get human-readable status label
 */
function get_status_label($status) {
    $labels = [
        STATUS_PENDING => 'Pending',
        STATUS_CONFIRMED => 'Confirmed',
        STATUS_ASSIGNED => 'Assigned',
        STATUS_ON_DELIVERY => 'On Delivery',
        STATUS_DELIVERED => 'Delivered',
        STATUS_ACCEPTED => 'Accepted',
        STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
        STATUS_PICKED_UP => 'Picked Up',
        STATUS_CANCELLED => 'Cancelled'
    ];
    
    return $labels[$status] ?? ucfirst($status);
}

/**
 * Send JSON response and exit
 */
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get user by ID from database
 */
function get_user_by_id($id) {
    global $pdo;
    if (!$pdo) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a notification for a user
 */
function create_notification($user_id, $title, $message, $type, $reference_id = null) {
    global $pdo;
    if (!$pdo) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, reference_id) 
                          VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type, $reference_id]);
}

/**
 * Get client IP address (handles proxies)
 */
function get_client_ip() {
    $ip = 'unknown';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return trim($ip);
}

/**
 * Get role display name
 */
function get_role_display_name($role) {
    $names = [
        ROLE_CUSTOMER => 'Customer',
        ROLE_RIDER => 'Rider',
        ROLE_STAFF => 'Staff',
        ROLE_ADMIN => 'Admin',
        ROLE_SUPER_ADMIN => 'Super Admin'
    ];
    
    return $names[$role] ?? ucfirst($role);
}

/**
 * Get role icon (Material Icon name)
 */
function get_role_icon($role) {
    $icons = [
        ROLE_CUSTOMER => 'person',
        ROLE_RIDER => 'directions_bike',
        ROLE_STAFF => 'badge',
        ROLE_ADMIN => 'admin_panel_settings',
        ROLE_SUPER_ADMIN => 'shield'
    ];
    
    return $icons[$role] ?? 'account_circle';
}

/**
 * Check if user can access a role
 */
function can_access_role($user_role, $required_role) {
    $hierarchy = [
        ROLE_SUPER_ADMIN => 5,
        ROLE_ADMIN => 4,
        ROLE_STAFF => 3,
        ROLE_RIDER => 2,
        ROLE_CUSTOMER => 1
    ];
    
    $user_level = $hierarchy[$user_role] ?? 0;
    $required_level = $hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}
