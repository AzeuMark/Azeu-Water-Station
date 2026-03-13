<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE INVENTORY ITEM API
 * ============================================================================
 * 
 * Purpose: Update inventory item details
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "item_id": 123,
 *   "item_name": "Updated Name",
 *   "price": 60.00,
 *   "status": "active" | "inactive",
 *   "item_icon": "uploads/items/new-icon.png" (optional)
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Item updated"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('inventory/update');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$item_id = intval($input['item_id'] ?? 0);
$item_name = sanitize($input['item_name'] ?? '');
$price = isset($input['price']) ? floatval($input['price']) : null;
$status = sanitize($input['status'] ?? '');
$item_icon = isset($input['item_icon']) ? sanitize($input['item_icon']) : null;
$stock_count = isset($input['stock_count']) ? intval($input['stock_count']) : null;

if ($item_id <= 0) {
    json_response(['success' => false, 'message' => 'Item ID is required'], 400);
}

try {
    // Verify item exists
    $item = db_fetch("SELECT * FROM inventory WHERE id = ?", [$item_id]);
    
    if (!$item) {
        json_response(['success' => false, 'message' => 'Item not found'], 404);
    }
    
    // Build update query
    $update_fields = [];
    $update_params = [];
    
    if (!empty($item_name)) {
        // Check if name is taken by another item
        $existing = db_fetch("SELECT id FROM inventory WHERE item_name = ? AND id != ?", [$item_name, $item_id]);
        if ($existing) {
            json_response(['success' => false, 'message' => 'Item name already in use'], 409);
        }
        
        $update_fields[] = "item_name = ?";
        $update_params[] = $item_name;
    }
    
    if ($price !== null) {
        if ($price <= 0) {
            json_response(['success' => false, 'message' => 'Price must be greater than 0'], 400);
        }
        
        $update_fields[] = "price = ?";
        $update_params[] = $price;
    }
    
    if (!empty($status)) {
        $valid_statuses = [INV_ACTIVE, INV_INACTIVE, INV_OUT_OF_STOCK];
        if (!in_array($status, $valid_statuses)) {
            json_response(['success' => false, 'message' => 'Invalid status'], 400);
        }
        
        $update_fields[] = "status = ?";
        $update_params[] = $status;
    }
    
    if ($item_icon !== null) {
        $update_fields[] = "item_icon = ?";
        $update_params[] = $item_icon;
    }
    
    // Handle stock_count: set stock and auto-derive status from it
    if ($stock_count !== null) {
        if ($stock_count < 0) {
            json_response(['success' => false, 'message' => 'Stock count cannot be negative'], 400);
        }
        // Remove any status field already queued (stock_count overrides manual status)
        $filtered_fields  = [];
        $filtered_params  = [];
        foreach ($update_fields as $i => $f) {
            if (strpos($f, 'status') === false) {
                $filtered_fields[] = $f;
                $filtered_params[] = $update_params[$i];
            }
        }
        $update_fields = $filtered_fields;
        $update_params = $filtered_params;

        $update_fields[] = "stock_count = ?";
        $update_params[] = $stock_count;
        $update_fields[] = "status = ?";
        $update_params[] = $stock_count > 0 ? INV_ACTIVE : INV_OUT_OF_STOCK;
    }
    
    if (empty($update_fields)) {
        json_response(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    // Add item_id to params
    $update_params[] = $item_id;
    
    // Update item
    $sql = "UPDATE inventory SET " . implode(', ', $update_fields) . " WHERE id = ?";
    db_update($sql, $update_params);
    
    logger_info("Inventory item updated", [
        'item_id' => $item_id,
        'updated_by' => $_SESSION['user_id'],
        'fields' => array_keys($input)
    ]);
    
    // Auto-cancel pending orders if item is now out of stock
    if ($stock_count !== null && $stock_count == 0) {
        $pending_order_ids = db_fetch_all(
            "SELECT DISTINCT oi.order_id FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             WHERE oi.inventory_id = ? AND o.status = 'pending'",
            [$item_id]
        );
        
        foreach ($pending_order_ids as $row) {
            $oid = $row['order_id'];
            $cancel_reason = "Item \"{$item['item_name']}\" is out of stock.";
            
            db_update(
                "UPDATE orders SET status = 'cancelled', cancellation_reason = ?, cancelled_by = ? WHERE id = ?",
                [$cancel_reason, $_SESSION['user_id'], $oid]
            );
            
            // Fetch customer_id to notify
            $affected_order = db_fetch("SELECT customer_id FROM orders WHERE id = ?", [$oid]);
            if ($affected_order) {
                create_notification(
                    $affected_order['customer_id'],
                    'Order Cancelled — Out of Stock',
                    "Your order #$oid was automatically cancelled. Reason: $cancel_reason",
                    'order_cancelled',
                    $oid
                );
            }
            
            logger_warning("Order auto-cancelled due to out of stock", [
                'order_id' => $oid,
                'item_id'  => $item_id
            ]);
        }
    }
    
    json_response([
        'success' => true,
        'message' => 'Item updated successfully'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
