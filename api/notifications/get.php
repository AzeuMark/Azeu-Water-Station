<?php
/**
 * ============================================================================
 * AZEU WATER STATION - GET NOTIFICATIONS API
 * ============================================================================
 * 
 * Purpose: Get user's notifications
 * Method: GET
 * Role: ALL AUTHENTICATED USERS
 * 
 * Query Parameters:
 * - limit: Number of notifications (default: 20)
 * - unread_only: Only unread notifications (optional)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('notifications/get');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

try {
    $user_id = $_SESSION['user_id'];
    $where = "user_id = ?";
    $params = [$user_id];
    
    if ($unread_only) {
        $where .= " AND is_read = 0";
    }
    
    $query = "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $notifications = db_fetch_all($query, $params);
    
    json_response([
        'success' => true,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
