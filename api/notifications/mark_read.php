<?php
/**
 * ============================================================================
 * AZEU WATER STATION - MARK NOTIFICATION AS READ API
 * ============================================================================
 * 
 * Purpose: Mark notification(s) as read
 * Method: POST
 * Role: ALL AUTHENTICATED USERS
 * 
 * Request Body (JSON):
 * {
 *   "notification_id": 123,
 *   "mark_all": true (optional)
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('notifications/mark_read');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$input = json_decode(file_get_contents('php://input'), true);
$notification_id = intval($input['notification_id'] ?? 0);
$mark_all = isset($input['mark_all']) && $input['mark_all'] === true;

try {
    $user_id = $_SESSION['user_id'];
    
    if ($mark_all) {
        db_update("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$user_id]);
    } elseif ($notification_id > 0) {
        db_update("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notification_id, $user_id]);
    } else {
        json_response(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    json_response(['success' => true, 'message' => 'Marked as read']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
