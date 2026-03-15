<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE ORDER STATUS API
 * ============================================================================
 * 
 * Purpose: Update order status (staff/admin/rider)
 * Method: POST
 * Role: RIDER, STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "order_id": 123,
 *   "status": "confirmed",
 *   "staff_comment": "optional comment"
 * }
 * 
 * Status Flow:
 * - Staff/Admin: pending → confirmed → ready_for_pickup
 * - Rider: assigned → on_delivery → delivered
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Status updated"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/update_status');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_RIDER, ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$order_id = intval($input['order_id'] ?? 0);
$new_status = sanitize($input['status'] ?? '');
$staff_comment = sanitize($input['staff_comment'] ?? '');

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}

$valid_statuses = [
    STATUS_PENDING, STATUS_CONFIRMED, STATUS_ASSIGNED, STATUS_REASSIGN_REQUESTED, STATUS_ON_DELIVERY,
    STATUS_DELIVERED, STATUS_READY_FOR_PICKUP, STATUS_PICKED_UP
];

if (!in_array($new_status, $valid_statuses)) {
    json_response(['success' => false, 'message' => 'Invalid status'], 400);
}

try {
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    
    // Get order
    $order = db_fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Validate status transition based on role
    $allowed = false;
    
    if (in_array($role, [ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN])) {
        // Staff/Admin can change to: confirmed, ready_for_pickup, on_delivery, delivered
        $allowed = in_array($new_status, [STATUS_CONFIRMED, STATUS_READY_FOR_PICKUP, STATUS_ON_DELIVERY, STATUS_DELIVERED, STATUS_PICKED_UP]);
    }
    
    if ($role === ROLE_RIDER) {
        // Riders can only update their assigned orders
        if ($order['rider_id'] != $user_id) {
            json_response(['success' => false, 'message' => 'Not your assigned order'], 403);
        }
        
        // Rider can change to: on_delivery, delivered
        $allowed = in_array($new_status, [STATUS_ON_DELIVERY, STATUS_DELIVERED]);
    }
    
    if (!$allowed) {
        json_response(['success' => false, 'message' => 'Invalid status transition for your role'], 403);
    }
    
    // Update order
    $update_fields = ["status = ?"];
    $update_params = [$new_status];
    
    if (!empty($staff_comment)) {
        $update_fields[] = "staff_comment = ?";
        $update_params[] = $staff_comment;
    }
    
    if ($new_status === STATUS_DELIVERED) {
        $update_fields[] = "delivered_at = NOW()";
    }
    
    $update_params[] = $order_id;
    
    $sql = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
    db_update($sql, $update_params);
    
    // Deduct stock when order is confirmed (stock is reserved only at confirmation)
    if ($new_status === STATUS_CONFIRMED) {
        $items = db_fetch_all(
            "SELECT inventory_id, quantity FROM order_items WHERE order_id = ?",
            [$order_id]
        );
        foreach ($items as $oi) {
            // Check sufficient stock
            $inv = db_fetch("SELECT stock_count, item_name FROM inventory WHERE id = ?", [$oi['inventory_id']]);
            if (!$inv || $inv['stock_count'] < $oi['quantity']) {
                // Not enough stock — auto-cancel this order instead
                $item_name = $inv['item_name'] ?? 'Unknown item';
                $cancel_reason = "Item \"{$item_name}\" has insufficient stock to confirm this order.";
                db_update(
                    "UPDATE orders SET status = 'cancelled', cancellation_reason = ?, cancelled_by = ? WHERE id = ?",
                    [$cancel_reason, $user_id, $order_id]
                );
                create_notification(
                    $order['customer_id'],
                    'Order Cancelled — Insufficient Stock',
                    "Your order #$order_id was cancelled. Reason: $cancel_reason",
                    'order_cancelled',
                    $order_id
                );
                json_response([
                    'success' => false,
                    'message'  => "Cannot confirm: $cancel_reason Order has been cancelled."
                ], 409);
            }
            db_update(
                "UPDATE inventory SET stock_count = stock_count - ? WHERE id = ?",
                [$oi['quantity'], $oi['inventory_id']]
            );
            // Mark out of stock if stock hits 0
            db_update(
                "UPDATE inventory SET status = ? WHERE id = ? AND stock_count = 0",
                [INV_OUT_OF_STOCK, $oi['inventory_id']]
            );
        }
    }
    
    // Create notification for customer
    $status_messages = [
        STATUS_CONFIRMED => 'Your order has been confirmed',
        STATUS_ASSIGNED => 'A rider has been assigned to your order',
        STATUS_ON_DELIVERY => 'Your order is on the way',
        STATUS_DELIVERED => 'Your order has been delivered',
        STATUS_READY_FOR_PICKUP => 'Your order is ready for pickup',
        STATUS_PICKED_UP => 'Your order has been picked up'
    ];
    
    if (isset($status_messages[$new_status])) {
        create_notification(
            $order['customer_id'],
            'Order Status Updated',
            $status_messages[$new_status] . " (Order #$order_id)",
            'order_' . $new_status,
            $order_id
        );
    }
    
    logger_info("Order status updated", [
        'order_id' => $order_id,
        'old_status' => $order['status'],
        'new_status' => $new_status
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Order status updated successfully'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
