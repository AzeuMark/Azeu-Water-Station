<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE USER PREFERENCES API
 * ============================================================================
 * 
 * Purpose: Update user preferences (dark mode, etc.)
 * Method: POST
 * Role: ALL AUTHENTICATED USERS
 * 
 * Request Body (JSON):
 * {
 *   "dark_mode": 1 | 0
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('settings/update_preferences');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$input = json_decode(file_get_contents('php://input'), true);
$dark_mode = isset($input['dark_mode']) ? intval($input['dark_mode']) : null;

try {
    $user_id = $_SESSION['user_id'];
    
    if ($dark_mode !== null) {
        db_update(
            "UPDATE user_preferences SET dark_mode = ? WHERE user_id = ?",
            [$dark_mode, $user_id]
        );
    }
    
    json_response(['success' => true, 'message' => 'Preferences updated']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
