<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE DELIVERY PRIORITY API
 * ============================================================================
 * 
 * Purpose: Update delivery priority order for a rider
 * Method: POST
 * Role: RIDER (own)
 * 
 * Request Body (JSON):
 * {
 *   "priorities": [
 *     {"order_id": 1, "priority": 1},
 *     {"order_id": 2, "priority": 2}
 *   ]
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('riders/update_priority');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_RIDER]);

$input = json_decode(file_get_contents('php://input'), true);
$priorities = $input['priorities'] ?? [];

if (empty($priorities) || !is_array($priorities)) {
    json_response(['success' => false, 'message' => 'Priorities array is required'], 400);
}

try {
    $rider_id = $_SESSION['user_id'];
    
    // Delete existing priorities
    db_delete("DELETE FROM delivery_priority WHERE rider_id = ?", [$rider_id]);
    
    // Insert new priorities
    foreach ($priorities as $priority) {
        $order_id = intval($priority['order_id'] ?? 0);
        $priority_order = intval($priority['priority'] ?? 0);
        
        if ($order_id > 0) {
            db_insert(
                "INSERT INTO delivery_priority (rider_id, order_id, priority_order) VALUES (?, ?, ?)",
                [$rider_id, $order_id, $priority_order]
            );
        }
    }
    
    logger_info("Delivery priority updated", ['rider_id' => $rider_id, 'count' => count($priorities)]);
    
    json_response([
        'success' => true,
        'message' => 'Priority updated'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
