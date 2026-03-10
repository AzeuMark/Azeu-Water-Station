<?php
/**
 * ============================================================================
 * AZEU WATER STATION - AUTO ASSIGN RIDER API
 * ============================================================================
 * 
 * Purpose: Auto-assign the least-busy available rider to an order
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "order_id": 123
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/auto_assign');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$order_id = intval($input['order_id'] ?? 0);

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}

try {
    // Get order
    $order = db_fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Must be delivery type
    if ($order['delivery_type'] !== DEL_DELIVERY) {
        json_response(['success' => false, 'message' => 'Cannot assign rider to pickup orders'], 400);
    }
    
    // Must be confirmed status
    if ($order['status'] !== STATUS_CONFIRMED) {
        json_response(['success' => false, 'message' => 'Only confirmed orders can be auto-assigned'], 400);
    }
    
    // Find the least-busy available rider
    $rider = db_fetch(
        "SELECT u.id, u.full_name,
         (SELECT COUNT(*) FROM orders WHERE rider_id = u.id AND status IN ('assigned', 'on_delivery')) as active_count
         FROM users u 
         WHERE u.role = 'rider' AND u.status = 'active' AND u.is_available = 1
         ORDER BY active_count ASC LIMIT 1"
    );
    
    if (!$rider) {
        json_response(['success' => false, 'message' => 'No available riders found'], 400);
    }
    
    // Assign rider
    db_update(
        "UPDATE orders SET rider_id = ?, status = ? WHERE id = ?",
        [$rider['id'], STATUS_ASSIGNED, $order_id]
    );
    
    // Notify rider
    create_notification(
        $rider['id'],
        'New Delivery Assigned',
        "You have been assigned to deliver Order #$order_id",
        'order_assigned',
        $order_id
    );
    
    // Notify customer
    create_notification(
        $order['customer_id'],
        'Rider Assigned',
        "A rider has been assigned to your order #$order_id",
        'order_assigned',
        $order_id
    );
    
    logger_info("Rider auto-assigned to order", [
        'order_id' => $order_id,
        'rider_id' => $rider['id'],
        'rider_name' => $rider['full_name']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Rider ' . $rider['full_name'] . ' assigned',
        'rider_name' => $rider['full_name']
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
