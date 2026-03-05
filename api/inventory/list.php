<?php
/**
 * ============================================================================
 * AZEU WATER STATION - LIST INVENTORY ITEMS API
 * ============================================================================
 * 
 * Purpose: Get list of inventory items
 * Method: GET
 * Role: ALL AUTHENTICATED USERS
 * 
 * Query Parameters:
 * - status: Filter by status (optional)
 * - available_only: Only show items in stock (optional, default: false)
 * 
 * Response:
 * {
 *   "success": true,
 *   "items": [
 *     {
 *       "id": 1,
 *       "item_name": "30L Water Refill",
 *       "price": 50.00,
 *       "stock_count": 100,
 *       "status": "active"
 *     }
 *   ]
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('inventory/list');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_login();

$status_filter = sanitize($_GET['status'] ?? '');
$available_only = isset($_GET['available_only']) && $_GET['available_only'] === 'true';

try {
    $where_clauses = [];
    $params = [];
    
    // Status filter
    if (!empty($status_filter)) {
        $where_clauses[] = "status = ?";
        $params[] = $status_filter;
    }
    
    // Available only (stock > 0 and active)
    if ($available_only) {
        $where_clauses[] = "stock_count > 0";
        $where_clauses[] = "status = ?";
        $params[] = INV_ACTIVE;
    }
    
    $where_sql = empty($where_clauses) ? '1=1' : implode(' AND ', $where_clauses);
    
    // Get items
    $query = "SELECT id, item_name, item_icon, stock_count, price, status, 
              last_restocked_at, created_at, updated_at
              FROM inventory 
              WHERE $where_sql
              ORDER BY item_name ASC";
    
    $items = db_fetch_all($query, $params);
    
    logger_debug("Inventory list retrieved", ['count' => count($items)]);
    
    json_response([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
