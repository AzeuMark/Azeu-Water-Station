<?php
/**
 * ============================================================================
 * AZEU WATER STATION - COUNT UNREAD NOTIFICATIONS API
 * ============================================================================
 * 
 * Purpose: Get count of unread notifications
 * Method: GET
 * Role: ALL AUTHENTICATED USERS
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('notifications/count_unread');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

try {
    $user_id = $_SESSION['user_id'];
    
    $result = db_fetch(
        "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
        [$user_id]
    );
    
    json_response([
        'success' => true,
        'count' => $result['count']
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
