<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CONFIRM DELIVERY API
 * ============================================================================
 * 
 * Purpose: Customer confirms receipt of delivered order
 * Method: POST
 * Role: CUSTOMER
 * 
 * Request Body (JSON):
 * {
 *   "order_id": 123
 * }
 * 
 * Notes:
 * - Only delivered/picked_up orders can be confirmed
 * - Status changes to 'accepted'
 * - This is the final step in the order lifecycle
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Order confirmed"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/confirm_delivery');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_CUSTOMER]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$order_id = intval($input['order_id'] ?? 0);

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get order
    $order = db_fetch("SELECT * FROM orders WHERE id = ? AND customer_id = ?", [$order_id, $user_id]);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Check if order can be confirmed
    $confirmable_statuses = [STATUS_DELIVERED, STATUS_PICKED_UP];
    
    if (!in_array($order['status'], $confirmable_statuses)) {
        json_response(['success' => false, 'message' => 'Order cannot be confirmed yet'], 400);
    }
    
    if ($order['customer_confirmed'] == 1) {
        json_response(['success' => false, 'message' => 'Order already confirmed'], 400);
    }
    
    // Confirm order
    db_update(
        "UPDATE orders SET status = ?, customer_confirmed = 1, customer_confirmed_at = NOW() WHERE id = ?",
        [STATUS_ACCEPTED, $order_id]
    );
    
    // Notify rider if delivery order
    if ($order['rider_id']) {
        create_notification(
            $order['rider_id'],
            'Delivery Confirmed',
            "Customer has confirmed receipt of Order #$order_id",
            'order_accepted',
            $order_id
        );
    }
    
    // Notify staff/admin
    $staff_admins = db_fetch_all(
        "SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'"
    );
    
    foreach ($staff_admins as $admin) {
        create_notification(
            $admin['id'],
            'Order Completed',
            "Order #$order_id has been confirmed by customer",
            'order_accepted',
            $order_id
        );
    }
    
    logger_info("Order confirmed by customer", ['order_id' => $order_id]);
    
    json_response([
        'success' => true,
        'message' => 'Order confirmed successfully. Thank you!'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
