<?php
/**
 * ============================================================================
 * AZEU WATER STATION - GET RECEIPT TOKEN API
 * ============================================================================
 * 
 * Purpose: Get receipt token for viewing public receipt
 * Method: GET
 * Role: CUSTOMER, STAFF, ADMIN
 * 
 * Query Parameters:
 * - order_id: Order ID (required)
 * 
 * Response:
 * {
 *   "success": true,
 *   "receipt_token": "abc123...",
 *   "receipt_url": "https://..."
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/get_receipt');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_login();

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    json_response(['success' => false, 'message' => 'Order ID is required'], 400);
}

try {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Build query based on role
    $query = "SELECT id, receipt_token, customer_id FROM orders WHERE id = ?";
    $params = [$order_id];
    
    // Customers can only get their own receipts
    if ($role === ROLE_CUSTOMER) {
        $query .= " AND customer_id = ?";
        $params[] = $user_id;
    }
    
    $order = db_fetch($query, $params);
    
    if (!$order) {
        json_response(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Generate receipt URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = dirname(dirname($_SERVER['SCRIPT_NAME']));
    $receipt_url = "$protocol://$host$base_path/receipt.php?token=" . $order['receipt_token'];
    
    logger_debug("Receipt token retrieved", ['order_id' => $order_id]);
    
    json_response([
        'success' => true,
        'receipt_token' => $order['receipt_token'],
        'receipt_url' => $receipt_url
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
