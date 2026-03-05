<?php
/**
 * ============================================================================
 * AZEU WATER STATION - GET INVENTORY ITEM API
 * ============================================================================
 * 
 * Purpose: Get detailed information about an inventory item
 * Method: GET
 * Role: ALL AUTHENTICATED USERS
 * 
 * Query Parameters:
 * - id: Item ID (required)
 * 
 * Response:
 * {
 *   "success": true,
 *   "item": {
 *     "id": 1,
 *     "item_name": "30L Water Refill",
 *     "item_icon": "path/to/icon.png",
 *     "price": 50.00,
 *     "stock_count": 100,
 *     "status": "active",
 *     ...
 *   }
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('inventory/get');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_login();

$item_id = intval($_GET['id'] ?? 0);

if ($item_id <= 0) {
    json_response(['success' => false, 'message' => 'Item ID is required'], 400);
}

try {
    // Get item
    $item = db_fetch("SELECT * FROM inventory WHERE id = ?", [$item_id]);
    
    if (!$item) {
        json_response(['success' => false, 'message' => 'Item not found'], 404);
    }
    
    logger_debug("Inventory item retrieved", ['item_id' => $item_id]);
    
    json_response([
        'success' => true,
        'item' => $item
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
