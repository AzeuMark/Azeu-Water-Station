<?php
/**
 * ============================================================================
 * AZEU WATER STATION - LIST CUSTOMER ADDRESSES API
 * ============================================================================
 * 
 * Purpose: Get list of customer's delivery addresses
 * Method: GET
 * Role: CUSTOMER (own), STAFF, ADMIN
 * 
 * Query Parameters:
 * - customer_id: Customer ID (required for staff/admin, ignored for customer)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('addresses/list');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$customer_id = intval($_GET['customer_id'] ?? 0);
$role = $_SESSION['role'];

// Customers can only view their own addresses
if ($role === ROLE_CUSTOMER) {
    $customer_id = $_SESSION['user_id'];
} elseif ($customer_id <= 0) {
    json_response(['success' => false, 'message' => 'Customer ID is required'], 400);
}

try {
    $addresses = db_fetch_all(
        "SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC",
        [$customer_id]
    );
    
    json_response([
        'success' => true,
        'addresses' => $addresses
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
