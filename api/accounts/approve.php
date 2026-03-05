<?php
/**
 * ============================================================================
 * AZEU WATER STATION - APPROVE PENDING ACCOUNT API
 * ============================================================================
 * 
 * Purpose: Approve a pending customer registration
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "user_id": 123
 * }
 * 
 * Notes:
 * - Only pending accounts can be approved
 * - Status changes from 'pending' to 'active'
 * - Customer is notified via notification
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Account approved"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('accounts/approve');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$user_id = intval($input['user_id'] ?? 0);

if ($user_id <= 0) {
    json_response(['success' => false, 'message' => 'User ID is required'], 400);
}

try {
    // Get user
    $user = db_fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$user_id]);
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    if ($user['status'] !== ACCOUNT_PENDING) {
        json_response(['success' => false, 'message' => 'Only pending accounts can be approved'], 400);
    }
    
    // Approve account
    db_update("UPDATE users SET status = ? WHERE id = ?", [ACCOUNT_ACTIVE, $user_id]);
    
    // Notify user
    create_notification(
        $user_id,
        'Account Approved',
        'Your account has been approved! You can now log in and start placing orders.',
        'account_approved',
        null
    );
    
    logger_info("Account approved", [
        'user_id' => $user_id,
        'approved_by' => $_SESSION['user_id']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Account approved successfully'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
