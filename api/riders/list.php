<?php
/**
 * ============================================================================
 * AZEU WATER STATION - LIST RIDERS API
 * ============================================================================
 * 
 * Purpose: Get list of all riders with their statistics
 * Method: GET
 * Role: STAFF, ADMIN
 * 
 * Query Parameters:
 * - available_only: Only show available riders (optional)
 * 
 * Response:
 * {
 *   "success": true,
 *   "riders": [
 *     {
 *       "id": 5,
 *       "full_name": "John Rider",
 *       "phone": "1234567890",
 *       "is_available": 1,
 *       "total_deliveries": 50,
 *       "active_deliveries": 2
 *     }
 *   ]
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('riders/list');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$available_only = isset($_GET['available_only']) && $_GET['available_only'] === 'true';

try {
    $where = "role = 'rider' AND status = 'active' AND deleted_at IS NULL";
    
    if ($available_only) {
        $where .= " AND is_available = 1";
    }
    
    $query = "SELECT id, username, full_name, email, phone, is_available, created_at FROM users WHERE $where ORDER BY full_name ASC";
    $riders = db_fetch_all($query);
    
    // Get statistics for each rider
    foreach ($riders as &$rider) {
        $stats = db_fetch(
            "SELECT 
             COUNT(*) as total_deliveries,
             SUM(CASE WHEN status IN ('assigned', 'on_delivery', 'reassign_requested') THEN 1 ELSE 0 END) as active_deliveries,
             SUM(CASE WHEN status = 'on_delivery' THEN 1 ELSE 0 END) as on_delivery_count,
             SUM(CASE WHEN status IN ('delivered', 'accepted') THEN 1 ELSE 0 END) as completed_deliveries
             FROM orders WHERE rider_id = ?",
            [$rider['id']]
        );
        
        $rider['total_deliveries'] = $stats['total_deliveries'];
        $rider['active_deliveries'] = intval($stats['active_deliveries']);
        $rider['on_delivery_count'] = intval($stats['on_delivery_count']);
        $rider['completed_deliveries'] = $stats['completed_deliveries'];
    }
    
    json_response([
        'success' => true,
        'riders' => $riders
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
