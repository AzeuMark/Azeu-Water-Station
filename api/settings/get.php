<?php
/**
 * ============================================================================
 * AZEU WATER STATION - GET SYSTEM SETTINGS API
 * ============================================================================
 * 
 * Purpose: Get all system settings
 * Method: GET
 * Role: STAFF, ADMIN
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('settings/get');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

try {
    $settings_rows = db_fetch_all("SELECT setting_key, setting_value FROM settings");
    
    $settings = [];
    foreach ($settings_rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    json_response([
        'success' => true,
        'settings' => $settings
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
