<?php
/**
 * ============================================================================
 * AZEU WATER STATION - REVIEW APPEAL API
 * ============================================================================
 * 
 * Purpose: Approve or deny a cancellation appeal
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "appeal_id": 123,
 *   "action": "approve" | "deny",
 *   "admin_notes": "Optional notes"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('appeals/review');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$input = json_decode(file_get_contents('php://input'), true);
$appeal_id = intval($input['appeal_id'] ?? 0);
$action = sanitize($input['action'] ?? '');
$admin_notes = sanitize($input['admin_notes'] ?? '');

if ($appeal_id <= 0) {
    json_response(['success' => false, 'message' => 'Appeal ID is required'], 400);
}

if (!in_array($action, ['approve', 'deny'])) {
    json_response(['success' => false, 'message' => 'Invalid action'], 400);
}

try {
    $appeal = db_fetch("SELECT * FROM cancellation_appeals WHERE id = ?", [$appeal_id]);
    
    if (!$appeal) {
        json_response(['success' => false, 'message' => 'Appeal not found'], 404);
    }
    
    if ($appeal['status'] !== 'pending') {
        json_response(['success' => false, 'message' => 'Appeal already reviewed'], 400);
    }
    
    $new_status = $action === 'approve' ? 'approved' : 'denied';
    
    // Update appeal
    db_update(
        "UPDATE cancellation_appeals SET status = ?, reviewed_by = ?, admin_notes = ?, reviewed_at = NOW() WHERE id = ?",
        [$new_status, $_SESSION['user_id'], $admin_notes, $appeal_id]
    );
    
    // If approved, reset cancellation count and unflag account
    if ($action === 'approve') {
        db_update(
            "UPDATE users SET cancellation_count = 0, status = 'active' WHERE id = ?",
            [$appeal['customer_id']]
        );
        
        create_notification(
            $appeal['customer_id'],
            'Appeal Approved',
            'Your cancellation appeal has been approved. Your account has been restored.',
            'appeal_approved',
            $appeal_id
        );
    } else {
        create_notification(
            $appeal['customer_id'],
            'Appeal Denied',
            'Your cancellation appeal has been denied. ' . ($admin_notes ? "Reason: $admin_notes" : ''),
            'appeal_denied',
            $appeal_id
        );
    }
    
    logger_info("Appeal reviewed", [
        'appeal_id' => $appeal_id,
        'action' => $action,
        'reviewed_by' => $_SESSION['user_id']
    ]);
    
    json_response([
        'success' => true,
        'message' => "Appeal $action successfully"
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
