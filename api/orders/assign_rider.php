<?php
/**
 * ============================================================================
 * AZEU WATER STATION - ASSIGN RIDER TO ORDER API
 * ============================================================================
 * 
 * Purpose: Assign a rider to a delivery order (staff/admin)
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "order_id": 123,
 *   "rider_id": 5
 * }
 * 
 * Notes:
 * - Only confirmed orders with delivery type can be assigned
 * - Rider must be active and available
 * - Status changes to 'assigned'
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Rider assigned"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/assign_rider');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$order_id = intval($input['order_id'] ?? 0);
$rider_id = intval($input['rider_id'] ?? 0);

if ($order_id <= 0 || $rider_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID and Rider ID are required'], 400);
}

try {
    // Get order
    $order = db_fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Check if order is delivery type
    if ($order['delivery_type'] !== DEL_DELIVERY) {
        json_response(['success' => false, 'message' => 'Cannot assign rider to pickup orders'], 400);
    }
    
    // Check order status
    if (!in_array($order['status'], [STATUS_CONFIRMED, STATUS_REASSIGN_REQUESTED])) {
        json_response(['success' => false, 'message' => 'Only confirmed orders can be assigned'], 400);
    }
    
    // Verify rider
    $rider = db_fetch(
        "SELECT * FROM users WHERE id = ? AND role = ? AND status = ?",
        [$rider_id, ROLE_RIDER, ACCOUNT_ACTIVE]
    );
    
    if (!$rider) {
        json_response(['success' => false, 'message' => 'Rider not found or not available'], 404);
    }
    
    if ($rider['is_available'] != 1) {
        json_response(['success' => false, 'message' => 'Rider is currently unavailable'], 400);
    }
    
    // Assign rider and update status
    db_update(
        "UPDATE orders SET rider_id = ?, status = ? WHERE id = ?",
        [$rider_id, STATUS_ASSIGNED, $order_id]
    );
    
    // Notify rider
    create_notification(
        $rider_id,
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
    
    logger_info("Rider assigned to order", [
        'order_id' => $order_id,
        'rider_id' => $rider_id,
        'rider_name' => $rider['full_name']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Rider assigned successfully',
        'rider_name' => $rider['full_name']
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
