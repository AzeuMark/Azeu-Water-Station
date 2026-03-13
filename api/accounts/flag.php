<?php
/**
 * ============================================================================
 * AZEU WATER STATION - FLAG/UNFLAG ACCOUNT API
 * ============================================================================
 * 
 * Purpose: Flag or unflag a user account
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "user_id": 123,
 *   "action": "flag" | "unflag",
 *   "reason": "Reason for flagging" (required if action = "flag")
 * }
 * 
 * Notes:
 * - Flagging prevents user from placing orders
 * - Unflagging also resets cancellation count
 * - User is notified
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Account flagged/unflagged"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('accounts/flag');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$user_id = intval($input['user_id'] ?? 0);
$action = sanitize($input['action'] ?? '');
$reason = sanitize($input['reason'] ?? '');

if ($user_id <= 0) {
    json_response(['success' => false, 'message' => 'User ID is required'], 400);
}

if (!in_array($action, ['flag', 'unflag'])) {
    json_response(['success' => false, 'message' => 'Invalid action. Use "flag" or "unflag"'], 400);
}

if ($action === 'flag' && empty($reason)) {
    json_response(['success' => false, 'message' => 'Reason is required for flagging'], 400);
}

try {
    // Get user
    $user = db_fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$user_id]);
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // SECURITY RULE: Only customers and riders can be flagged
    if (!in_array($user['role'], ['customer', 'rider'])) {
        json_response(['success' => false, 'message' => 'Only customers and riders can be flagged'], 403);
    }
    
    if ($action === 'flag') {
        // Flag account
        db_update("UPDATE users SET status = ?, flag_reason = ? WHERE id = ?", [ACCOUNT_FLAGGED, $reason, $user_id]);
        
        // Notify user
        create_notification(
            $user_id,
            'Account Flagged',
            "Your account has been flagged. Reason: $reason. Please contact support or submit an appeal.",
            'account_flagged',
            null
        );
        
        $message = 'Account flagged successfully';
        
        logger_warning("Account flagged", [
            'user_id' => $user_id,
            'flagged_by' => $_SESSION['user_id'],
            'reason' => $reason
        ]);
        
    } else {
        // Unflag account
        db_update(
            "UPDATE users SET status = ?, cancellation_count = 0, flag_reason = NULL WHERE id = ?",
            [ACCOUNT_ACTIVE, $user_id]
        );
        
        // Notify user
        create_notification(
            $user_id,
            'Account Unflagged',
            'Your account has been unflagged. You can now place orders again.',
            'account_approved',
            null
        );
        
        $message = 'Account unflagged successfully';
        
        logger_info("Account unflagged", [
            'user_id' => $user_id,
            'unflagged_by' => $_SESSION['user_id']
        ]);
    }
    
    json_response([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
