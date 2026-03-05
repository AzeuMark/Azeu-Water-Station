<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DELETE INVENTORY ITEM API
 * ============================================================================
 * 
 * Purpose: Delete inventory item
 * Method: POST
 * Role: ADMIN, SUPER_ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "item_id": 123,
 *   "csrf_token": "token"
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Item deleted successfully"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('inventory/delete');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check - Only Admin and Super Admin can delete
require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$item_id = intval($input['item_id'] ?? 0);

if ($item_id <= 0) {
    json_response(['success' => false, 'message' => 'Item ID is required'], 400);
}

try {
    // Verify item exists
    $item = db_fetch("SELECT * FROM inventory WHERE id = ?", [$item_id]);
    
    if (!$item) {
        json_response(['success' => false, 'message' => 'Item not found'], 404);
    }
    
    // Check if item is used in any orders
    $used_in_orders = db_fetch("SELECT COUNT(*) as count FROM order_items WHERE item_id = ?", [$item_id]);
    
    if ($used_in_orders && $used_in_orders['count'] > 0) {
        // Don't allow deletion, suggest marking as inactive instead
        json_response([
            'success' => false, 
            'message' => 'Cannot delete item that has been used in orders. Consider marking it as inactive instead.'
        ], 409);
    }
    
    // Delete the item
    db_delete("DELETE FROM inventory WHERE id = ?", [$item_id]);
    
    logger_info("Inventory item deleted", [
        'item_id' => $item_id,
        'item_name' => $item['item_name'],
        'deleted_by' => $_SESSION['user_id']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Item deleted successfully'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
