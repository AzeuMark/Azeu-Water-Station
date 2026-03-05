<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CREATE ADDRESS API
 * ============================================================================
 * 
 * Purpose: Create a new delivery address for customer
 * Method: POST
 * Role: CUSTOMER
 * 
 * Request Body (JSON):
 * {
 *   "label": "Home",
 *   "full_address": "123 Main St, City",
 *   "is_default": 1 | 0
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('addresses/create');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_CUSTOMER]);

$input = json_decode(file_get_contents('php://input'), true);
$label = sanitize($input['label'] ?? 'Home');
$full_address = sanitize($input['full_address'] ?? '');
$is_default = intval($input['is_default'] ?? 0);

if (empty($full_address)) {
    json_response(['success' => false, 'message' => 'Address is required'], 400);
}

try {
    $customer_id = $_SESSION['user_id'];
    
    // If setting as default, unset other defaults
    if ($is_default == 1) {
        db_update("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?", [$customer_id]);
    }
    
    $address_id = db_insert(
        "INSERT INTO customer_addresses (customer_id, label, full_address, is_default) VALUES (?, ?, ?, ?)",
        [$customer_id, $label, $full_address, $is_default]
    );
    
    logger_info("Address created", ['address_id' => $address_id, 'customer_id' => $customer_id]);
    
    json_response([
        'success' => true,
        'address_id' => $address_id
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
