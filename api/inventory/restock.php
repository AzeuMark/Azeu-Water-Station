<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RESTOCK INVENTORY ITEM API
 * ============================================================================
 * 
 * Purpose: Update stock count for an inventory item
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "item_id": 123,
 *   "stock_count": 50,
 *   "mode": "add" | "set"
 * }
 * 
 * Notes:
 * - mode "add": Adds to existing stock
 * - mode "set": Sets stock to exact value
 * - Low stock threshold check and notification
 * 
 * Response:
 * {
 *   "success": true,
 *   "new_stock": 150
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('inventory/restock');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$item_id = intval($input['item_id'] ?? 0);
$stock_count = intval($input['stock_count'] ?? 0);
$mode = sanitize($input['mode'] ?? 'add');

if ($item_id <= 0) {
    json_response(['success' => false, 'message' => 'Item ID is required'], 400);
}

if ($stock_count < 0) {
    json_response(['success' => false, 'message' => 'Stock count cannot be negative'], 400);
}

if (!in_array($mode, ['add', 'set'])) {
    json_response(['success' => false, 'message' => 'Invalid mode. Use "add" or "set"'], 400);
}

try {
    // Get item
    $item = db_fetch("SELECT * FROM inventory WHERE id = ?", [$item_id]);
    
    if (!$item) {
        json_response(['success' => false, 'message' => 'Item not found'], 404);
    }
    
    // Calculate new stock
    $new_stock = ($mode === 'add') ? ($item['stock_count'] + $stock_count) : $stock_count;
    
    if ($new_stock < 0) {
        json_response(['success' => false, 'message' => 'Resulting stock cannot be negative'], 400);
    }
    
    // Determine new status
    $new_status = $new_stock > 0 ? INV_ACTIVE : INV_OUT_OF_STOCK;
    
    // Update stock
    db_update(
        "UPDATE inventory SET stock_count = ?, status = ?, last_restocked_at = NOW() WHERE id = ?",
        [$new_stock, $new_status, $item_id]
    );
    
    // Check low stock threshold
    $low_stock_threshold = intval(get_setting('low_stock_threshold') ?? 10);
    
    if ($new_stock <= $low_stock_threshold && $new_stock > 0) {
        // Notify admins about low stock
        $admins = db_fetch_all(
            "SELECT id FROM users WHERE role IN ('admin', 'super_admin') AND status = 'active'"
        );
        
        foreach ($admins as $admin) {
            create_notification(
                $admin['id'],
                'Low Stock Alert',
                "{$item['item_name']} is running low (Stock: $new_stock)",
                'low_stock',
                $item_id
            );
        }
        
        logger_warning("Low stock alert", [
            'item_id' => $item_id,
            'item_name' => $item['item_name'],
            'stock' => $new_stock
        ]);
    }
    
    logger_info("Inventory restocked", [
        'item_id' => $item_id,
        'mode' => $mode,
        'old_stock' => $item['stock_count'],
        'new_stock' => $new_stock,
        'updated_by' => $_SESSION['user_id']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Stock updated successfully',
        'new_stock' => $new_stock,
        'status' => $new_status
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
