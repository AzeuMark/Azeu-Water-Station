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
    STATUS_PENDING, STATUS_CONFIRMED, STATUS_ASSIGNED, STATUS_ON_DELIVERY,
    STATUS_DELIVERED, STATUS_READY_FOR_PICKUP, STATUS_PICKED_UP, STATUS_REASSIGNING
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
        // Staff/Admin can change to: confirmed, ready_for_pickup
        $allowed = in_array($new_status, [STATUS_CONFIRMED, STATUS_READY_FOR_PICKUP]);
    }
    
    if ($role === ROLE_RIDER) {
        // Riders can only update their assigned orders
        if ($order['rider_id'] != $user_id) {
            json_response(['success' => false, 'message' => 'Not your assigned order'], 403);
        }
        
        // Rider can change to: on_delivery, delivered, reassigning
        $allowed = in_array($new_status, [STATUS_ON_DELIVERY, STATUS_DELIVERED, STATUS_REASSIGNING]);
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
    
    // If reassigning, clear the rider and set status back to confirmed
    if ($new_status === STATUS_REASSIGNING) {
        $update_fields[] = "rider_id = NULL";
        
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
                $update_fields = ["status = ?", "rider_id = ?"];
                $update_params = [STATUS_ASSIGNED, $new_rider['id']];
                
                if (!empty($staff_comment)) {
                    $update_fields[] = "staff_comment = ?";
                    $update_params[] = $staff_comment;
                }
                
                $update_params[] = $order_id;
                $sql = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
                db_update($sql, $update_params);
                
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
            } else {
                // No available rider, set to confirmed (awaiting manual assignment)
                $update_fields = ["status = ?", "rider_id = NULL"];
                $update_params = [STATUS_CONFIRMED];
                
                if (!empty($staff_comment)) {
                    $update_fields[] = "staff_comment = ?";
                    $update_params[] = $staff_comment;
                }
                
                $update_params[] = $order_id;
                $sql = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
                db_update($sql, $update_params);
                
                json_response([
                    'success' => true,
                    'message' => 'Reassignment requested. No available rider — awaiting manual assignment.'
                ]);
            }
        } else {
            // Auto-reassign disabled — set to confirmed for manual re-assignment
            $update_fields = ["status = ?", "rider_id = NULL"];
            $update_params = [STATUS_CONFIRMED];
            
            if (!empty($staff_comment)) {
                $update_fields[] = "staff_comment = ?";
                $update_params[] = $staff_comment;
            }
            
            $update_params[] = $order_id;
            $sql = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
            db_update($sql, $update_params);
            
            // Notify staff
            $staff_admins = db_fetch_all(
                "SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'"
            );
            foreach ($staff_admins as $admin) {
                create_notification(
                    $admin['id'],
                    'Rider Reassignment Requested',
                    "Rider requested reassignment for Order #$order_id. Please assign a new rider.",
                    'order_reassigning',
                    $order_id
                );
            }
            
            json_response([
                'success' => true,
                'message' => 'Reassignment requested. Staff will assign a new rider.'
            ]);
        }
    }
    
    $update_params[] = $order_id;
    
    $sql = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
    db_update($sql, $update_params);
    
    // Create notification for customer
    $status_messages = [
        STATUS_CONFIRMED => 'Your order has been confirmed',
        STATUS_ASSIGNED => 'A rider has been assigned to your order',
        STATUS_ON_DELIVERY => 'Your order is on the way',
        STATUS_DELIVERED => 'Your order has been delivered',
        STATUS_READY_FOR_PICKUP => 'Your order is ready for pickup'
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
