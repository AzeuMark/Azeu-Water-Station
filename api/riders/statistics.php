<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDER STATISTICS API
 * ============================================================================
 * 
 * Purpose: Get detailed statistics for a specific rider
 * Method: GET
 * Role: STAFF, ADMIN, RIDER (own stats)
 * 
 * Query Parameters:
 * - rider_id: Rider ID (required for staff/admin, optional for rider)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('riders/statistics');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$rider_id = intval($_GET['rider_id'] ?? 0);
$role = $_SESSION['role'];

// Riders can view their own stats
if ($role === ROLE_RIDER) {
    $rider_id = $_SESSION['user_id'];
} elseif (!in_array($role, [ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN])) {
    json_response(['success' => false, 'message' => 'Access denied'], 403);
}

if ($rider_id <= 0) {
    json_response(['success' => false, 'message' => 'Rider ID is required'], 400);
}

try {
    $stats = db_fetch(
        "SELECT 
         COUNT(*) as total_deliveries,
         SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as pending,
         SUM(CASE WHEN status = 'on_delivery' THEN 1 ELSE 0 END) as on_delivery,
         SUM(CASE WHEN status IN ('delivered', 'accepted') THEN 1 ELSE 0 END) as completed,
         SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) as today_deliveries
         FROM orders WHERE rider_id = ?",
        [$rider_id]
    );
    
    json_response([
        'success' => true,
        'statistics' => $stats
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
