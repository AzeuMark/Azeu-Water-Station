<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE SYSTEM SETTINGS API
 * ============================================================================
 * 
 * Purpose: Update system settings
 * Method: POST
 * Role: ADMIN, SUPER_ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "settings": {
 *     "station_name": "New Name",
 *     "max_cancellation": "5",
 *     ...
 *   }
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('settings/update');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$input = json_decode(file_get_contents('php://input'), true);
$settings = $input['settings'] ?? [];

if (empty($settings) || !is_array($settings)) {
    json_response(['success' => false, 'message' => 'Settings array is required'], 400);
}

try {
    foreach ($settings as $key => $value) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both insert and update
        db_query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE setting_value = ?",
            [$key, $value, $value]
        );
    }
    
    // Clear settings cache
    unset($_SESSION['system_settings']);
    
    logger_info("System settings updated", [
        'updated_by' => $_SESSION['user_id'],
        'keys' => array_keys($settings)
    ]);
    
    json_response(['success' => true, 'message' => 'Settings updated']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
