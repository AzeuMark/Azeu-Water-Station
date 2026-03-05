<?php
/**
 * ============================================================================
 * AZEU WATER STATION - GET ACCOUNT DETAILS API
 * ============================================================================
 * 
 * Purpose: Get detailed information about a user account
 * Method: GET
 * Role: STAFF, ADMIN (or own account)
 * 
 * Query Parameters:
 * - id: User ID (required)
 * 
 * Response:
 * {
 *   "success": true,
 *   "account": {
 *     "id": 1,
 *     "username": "johndoe",
 *     ...
 *   },
 *   "preferences": {...},
 *   "statistics": {...}
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('accounts/get');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_login();

$user_id = intval($_GET['id'] ?? 0);

if ($user_id <= 0) {
    json_response(['success' => false, 'message' => 'User ID is required'], 400);
}

try {
    $current_user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Staff/Admin can view any account, others can only view their own
    $can_view = in_array($role, [ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]) || $user_id == $current_user_id;
    
    if (!$can_view) {
        json_response(['success' => false, 'message' => 'Access denied'], 403);
    }
    
    // Get account
    $account = db_fetch(
        "SELECT id, username, full_name, email, phone, role, status, is_available,
         cancellation_count, cancellation_reset_date, login_attempts, 
         created_at, updated_at 
         FROM users WHERE id = ? AND deleted_at IS NULL",
        [$user_id]
    );
    
    if (!$account) {
        json_response(['success' => false, 'message' => 'Account not found'], 404);
    }
    
    // Get preferences
    $preferences = db_fetch(
        "SELECT dark_mode FROM user_preferences WHERE user_id = ?",
        [$user_id]
    );
    
    // Get statistics based on role
    $statistics = [];
    
    if ($account['role'] === ROLE_CUSTOMER) {
        // Customer statistics
        $stats = db_fetch(
            "SELECT 
             COUNT(*) as total_orders,
             SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as completed_orders,
             SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
             SUM(CASE WHEN status = 'accepted' THEN total_amount ELSE 0 END) as total_spent
             FROM orders WHERE customer_id = ?",
            [$user_id]
        );
        $statistics = $stats;
        
        // Get addresses count
        $addr_count = db_fetch(
            "SELECT COUNT(*) as address_count FROM customer_addresses WHERE customer_id = ?",
            [$user_id]
        );
        $statistics['addresses'] = $addr_count['address_count'];
    }
    
    if ($account['role'] === ROLE_RIDER) {
        // Rider statistics
        $stats = db_fetch(
            "SELECT 
             COUNT(*) as total_deliveries,
             SUM(CASE WHEN status IN ('delivered', 'accepted') THEN 1 ELSE 0 END) as completed_deliveries,
             SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as pending_deliveries
             FROM orders WHERE rider_id = ?",
            [$user_id]
        );
        $statistics = $stats;
    }
    
    logger_debug("Account details retrieved", ['user_id' => $user_id]);
    
    json_response([
        'success' => true,
        'account' => $account,
        'preferences' => $preferences,
        'statistics' => $statistics
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
