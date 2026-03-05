<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CREATE INVENTORY ITEM API
 * ============================================================================
 * 
 * Purpose: Create a new inventory item
 * Method: POST
 * Role: STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "item_name": "30L Water Refill",
 *   "price": 50.00,
 *   "stock_count": 100,
 *   "item_icon": "uploads/items/icon.png" (optional)
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "item_id": 123
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('inventory/create');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$item_name = sanitize($input['item_name'] ?? '');
$price = floatval($input['price'] ?? 0);
$stock_count = intval($input['stock_count'] ?? 0);
$item_icon = sanitize($input['item_icon'] ?? '');

if (empty($item_name)) {
    json_response(['success' => false, 'message' => 'Item name is required'], 400);
}

if ($price <= 0) {
    json_response(['success' => false, 'message' => 'Price must be greater than 0'], 400);
}

if ($stock_count < 0) {
    json_response(['success' => false, 'message' => 'Stock count cannot be negative'], 400);
}

try {
    // Check if item name already exists
    $existing = db_fetch("SELECT id FROM inventory WHERE item_name = ?", [$item_name]);
    if ($existing) {
        json_response(['success' => false, 'message' => 'Item with this name already exists'], 409);
    }
    
    // Determine status based on stock
    $status = $stock_count > 0 ? INV_ACTIVE : INV_OUT_OF_STOCK;
    
    // Create item
    $item_id = db_insert(
        "INSERT INTO inventory (item_name, item_icon, stock_count, price, status, last_restocked_at) 
         VALUES (?, ?, ?, ?, ?, NOW())",
        [$item_name, $item_icon, $stock_count, $price, $status]
    );
    
    if (!$item_id) {
        json_response(['success' => false, 'message' => 'Failed to create item'], 500);
    }
    
    logger_info("Inventory item created", [
        'item_id' => $item_id,
        'item_name' => $item_name,
        'created_by' => $_SESSION['user_id']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Item created successfully',
        'item_id' => $item_id
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
