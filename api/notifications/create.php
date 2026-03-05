<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CREATE NOTIFICATION API
 * ============================================================================
 * 
 * Purpose: Create a notification (admin only)
 * Method: POST
 * Role: ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "user_id": 123,
 *   "title": "Notification Title",
 *   "message": "Notification message",
 *   "type": "system"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('notifications/create');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$input = json_decode(file_get_contents('php://input'), true);
$user_id = intval($input['user_id'] ?? 0);
$title = sanitize($input['title'] ?? '');
$message = sanitize($input['message'] ?? '');
$type = sanitize($input['type'] ?? 'system');

if ($user_id <= 0 || empty($title) || empty($message)) {
    json_response(['success' => false, 'message' => 'All fields are required'], 400);
}

try {
    create_notification($user_id, $title, $message, $type, null);
    
    json_response(['success' => true, 'message' => 'Notification created']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
