<?php
/**
 * ============================================================================
 * AZEU WATER STATION - REQUEST REASSIGNMENT API
 * ============================================================================
 * 
 * Purpose: Rider requests reassignment of their assigned order.
 *          Sets order status back to "confirmed" and stores the reason.
 * Method: POST
 * Role: RIDER
 * 
 * Request Body (JSON):
 * {
 *   "order_id": 123,
 *   "reason": "Cannot reach this area due to flooding"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/request_reassign');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Riders only
require_role([ROLE_RIDER]);

$input = json_decode(file_get_contents('php://input'), true);

$order_id = intval($input['order_id'] ?? 0);
$reason = sanitize($input['reason'] ?? '');

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}
if (empty($reason)) {
    json_response(['success' => false, 'message' => 'Reassignment reason is required'], 400);
}

try {
    $user_id = $_SESSION['user_id'];
    $rider_name = $_SESSION['full_name'] ?? 'Rider';
    
    // Get order and verify ownership
    $order = db_fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    if ($order['rider_id'] != $user_id) {
        json_response(['success' => false, 'message' => 'Not your assigned order'], 403);
    }
    
    // Must be assigned or on_delivery to request reassignment
    if (!in_array($order['status'], [STATUS_ASSIGNED, STATUS_ON_DELIVERY])) {
        json_response(['success' => false, 'message' => 'Cannot request reassignment for this order status'], 400);
    }
    
    // Store reassign reason in staff_comment (prefixed for identification)
    $reassign_comment = "[REASSIGN REQUEST by $rider_name] $reason";
    
    // Check if auto_reassign_rider is enabled
    $auto_reassign = get_setting('auto_reassign_rider');
    
    if ($auto_reassign == '1') {
        // Find the least-busy available rider (excluding the current one)
        $new_rider = db_fetch(
            "SELECT u.id, u.full_name,
             (SELECT COUNT(*) FROM orders WHERE rider_id = u.id AND status IN ('assigned', 'on_delivery')) as active_count
             FROM users u 
             WHERE u.role = 'rider' AND u.status = 'active' AND u.is_available = 1 AND u.id != ?
             ORDER BY active_count ASC LIMIT 1",
            [$user_id]
        );
        
        if ($new_rider) {
            // Auto-reassign to new rider
            db_update(
                "UPDATE orders SET status = ?, rider_id = ?, staff_comment = ? WHERE id = ?",
                [STATUS_ASSIGNED, $new_rider['id'], $reassign_comment, $order_id]
            );
            
            // Notify new rider
            create_notification(
                $new_rider['id'],
                'New Delivery Assigned',
                "You have been assigned to deliver Order #$order_id (reassigned)",
                'order_assigned',
                $order_id
            );
            
            logger_info("Order auto-reassigned", [
                'order_id' => $order_id,
                'old_rider' => $user_id,
                'new_rider' => $new_rider['id']
            ]);
            
            json_response([
                'success' => true,
                'message' => 'Order reassigned to ' . $new_rider['full_name']
            ]);
        }
    }
    
    // Default: set to reassign_requested, clear rider, store reason
    db_update(
        "UPDATE orders SET status = ?, rider_id = NULL, staff_comment = ? WHERE id = ?",
        [STATUS_REASSIGN_REQUESTED, $reassign_comment, $order_id]
    );
    
    // Notify staff/admins
    $staff_admins = db_fetch_all(
        "SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'"
    );
    foreach ($staff_admins as $admin) {
        create_notification(
            $admin['id'],
            'Rider Reassignment Requested',
            "Rider $rider_name requested reassignment for Order #$order_id. Reason: $reason",
            'order_reassign',
            $order_id
        );
    }
    
    logger_info("Rider requested reassignment", [
        'order_id' => $order_id,
        'rider_id' => $user_id,
        'reason' => $reason
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Reassignment requested. Order returned to confirmed status.'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
