<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CREATE CANCELLATION APPEAL API
 * ============================================================================
 * 
 * Purpose: Submit a cancellation appeal to reset cancellation count
 * Method: POST
 * Role: CUSTOMER
 * 
 * Request Body (JSON):
 * {
 *   "reason": "Explanation for appeal"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('appeals/create');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_CUSTOMER]);

$input = json_decode(file_get_contents('php://input'), true);
$reason = sanitize($input['reason'] ?? '');

if (empty($reason)) {
    json_response(['success' => false, 'message' => 'Reason is required'], 400);
}

try {
    $customer_id = $_SESSION['user_id'];
    
    // Check if customer has pending appeal
    $pending = db_fetch(
        "SELECT id FROM cancellation_appeals WHERE customer_id = ? AND status = 'pending'",
        [$customer_id]
    );
    
    if ($pending) {
        json_response(['success' => false, 'message' => 'You already have a pending appeal'], 400);
    }
    
    // Create appeal
    $appeal_id = db_insert(
        "INSERT INTO cancellation_appeals (customer_id, reason, status) VALUES (?, ?, 'pending')",
        [$customer_id, $reason]
    );
    
    // Notify staff/admins
    $staff_admins = db_fetch_all(
        "SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'"
    );
    
    foreach ($staff_admins as $admin) {
        create_notification(
            $admin['id'],
            'New Cancellation Appeal',
            $_SESSION['full_name'] . " has submitted a cancellation appeal",
            'appeal_submitted',
            $appeal_id
        );
    }
    
    logger_info("Cancellation appeal created", ['appeal_id' => $appeal_id, 'customer_id' => $customer_id]);
    
    json_response([
        'success' => true,
        'message' => 'Appeal submitted successfully',
        'appeal_id' => $appeal_id
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
