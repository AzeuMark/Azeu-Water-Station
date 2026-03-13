<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CANCEL ORDER API
 * ============================================================================
 * 
 * Purpose: Cancel an order (customer/staff/admin)
 * Method: POST
 * Role: CUSTOMER, STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "order_id": 123,
 *   "reason": "Cancellation reason"
 * }
 * 
 * Notes:
 * - Customers can only cancel PENDING orders
 * - Staff/Admin can cancel any order before DELIVERED
 * - Cancellation increments customer's cancellation count
 * - Stock is restored when order is cancelled
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Order cancelled"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/cancel');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_CUSTOMER, ROLE_RIDER, ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$order_id = intval($input['order_id'] ?? 0);
$reason = sanitize($input['reason'] ?? '');

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}

if (empty($reason)) {
    json_response(['success' => false, 'message' => 'Cancellation reason is required'], 400);
}

try {
    $pdo->beginTransaction();
    
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    
    // Get order
    $order = db_fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
    
    if (!$order) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Check permissions
    if ($role === ROLE_CUSTOMER) {
        // Customer can only cancel their own pending orders
        if ($order['customer_id'] != $user_id) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Not your order'], 403);
        }
        
        if ($order['status'] !== STATUS_PENDING) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Only pending orders can be cancelled'], 403);
        }
    }
    
    if ($role === ROLE_RIDER) {
        // Rider can only cancel orders assigned to them at assigned or on_delivery status
        if ($order['rider_id'] != $user_id) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Not your assigned order'], 403);
        }
        if (!in_array($order['status'], [STATUS_ASSIGNED, STATUS_ON_DELIVERY])) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'You can only cancel assigned or on-delivery orders'], 403);
        }
    }
    
    // Update order status
    db_update(
        "UPDATE orders SET status = ?, cancellation_reason = ?, cancelled_by = ? WHERE id = ?",
        [STATUS_CANCELLED, $reason, $user_id, $order_id]
    );
    
    // Only restore stock if the order was confirmed (pending orders never had stock deducted)
    if ($order['status'] !== STATUS_PENDING) {
        $order_items = db_fetch_all("SELECT inventory_id, quantity FROM order_items WHERE order_id = ?", [$order_id]);
        foreach ($order_items as $item) {
            db_update(
                "UPDATE inventory SET stock_count = stock_count + ? WHERE id = ?",
                [$item['quantity'], $item['inventory_id']]
            );
            // Mark back to active if it was out of stock
            db_update(
                "UPDATE inventory SET status = ? WHERE id = ? AND status = ?",
                [INV_ACTIVE, $item['inventory_id'], INV_OUT_OF_STOCK]
            );
        }
    }
    
    // Increment customer's cancellation count if cancelled by customer
    if ($role === ROLE_CUSTOMER) {
        db_update(
            "UPDATE users SET cancellation_count = cancellation_count + 1 WHERE id = ?",
            [$order['customer_id']]
        );
        
        // Check if customer reached max cancellations
        $customer = db_fetch("SELECT cancellation_count FROM users WHERE id = ?", [$order['customer_id']]);
        $max_cancellation = intval(get_setting('max_cancellation') ?? 5);
        
        if ($customer['cancellation_count'] >= $max_cancellation) {
            // Flag customer account
            db_update("UPDATE users SET status = ? WHERE id = ?", [ACCOUNT_FLAGGED, $order['customer_id']]);
            
            create_notification(
                $order['customer_id'],
                'Account Flagged',
                'Your account has been flagged due to excessive cancellations. Please submit an appeal.',
                'account_flagged',
                null
            );
        }
    }
    
    // Notify customer if cancelled by staff
    if ($role !== ROLE_CUSTOMER) {
        create_notification(
            $order['customer_id'],
            'Order Cancelled',
            "Your order #$order_id has been cancelled. Reason: $reason",
            'order_cancelled',
            $order_id
        );
    }
    
    // Notify staff if cancelled by customer or rider
    if ($role === ROLE_CUSTOMER || $role === ROLE_RIDER) {
        $staff_admins = db_fetch_all(
            "SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'"
        );
        $cancellerLabel = $role === ROLE_RIDER ? 'Rider' : 'Customer';
        foreach ($staff_admins as $admin) {
            create_notification(
                $admin['id'],
                "Order Cancelled by $cancellerLabel",
                "Order #$order_id has been cancelled by " . $_SESSION['full_name'] . ". Reason: $reason",
                'order_cancelled',
                $order_id
            );
        }
    }
    
    $pdo->commit();
    
    logger_info("Order cancelled", [
        'order_id' => $order_id,
        'cancelled_by_role' => $role,
        'reason' => $reason
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Order cancelled successfully'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
