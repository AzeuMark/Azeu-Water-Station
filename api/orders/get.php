<?php
/**
 * ============================================================================
 * AZEU WATER STATION - GET ORDER DETAILS API
 * ============================================================================
 * 
 * Purpose: Get detailed information about a specific order
 * Method: GET
 * Role: CUSTOMER, RIDER, STAFF, ADMIN
 * 
 * Query Parameters:
 * - id: Order ID (required)
 * 
 * Response:
 * {
 *   "success": true,
 *   "order": {
 *     "id": 123,
 *     "customer_name": "John Doe",
 *     "status": "pending",
 *     "total_amount": 150.00,
 *     ...
 *   },
 *   "items": [...]
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/get');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_login();

$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}

try {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Build query based on role
    $query = "SELECT o.*, 
              u.full_name as customer_name, 
              u.email as customer_email, 
              u.phone as customer_phone,
              r.full_name as rider_name,
              r.phone as rider_phone
              FROM orders o
              JOIN users u ON o.customer_id = u.id
              LEFT JOIN users r ON o.rider_id = r.id
              WHERE o.id = ?";
    
    $params = [$order_id];
    
    // Customers can only see their own orders
    if ($role === ROLE_CUSTOMER) {
        $query .= " AND o.customer_id = ?";
        $params[] = $user_id;
    }
    
    // Riders can only see assigned orders
    if ($role === ROLE_RIDER) {
        $query .= " AND o.rider_id = ?";
        $params[] = $user_id;
    }
    
    $order = db_fetch($query, $params);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Get order items
    $items = db_fetch_all("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);
    
    logger_debug("Order details retrieved", ['order_id' => $order_id]);
    
    json_response([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
