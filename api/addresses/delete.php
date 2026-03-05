<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DELETE ADDRESS API
 * ============================================================================
 * 
 * Purpose: Delete a delivery address
 * Method: POST
 * Role: CUSTOMER
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('addresses/delete');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_CUSTOMER]);

$input = json_decode(file_get_contents('php://input'), true);
$address_id = intval($input['address_id'] ?? 0);

if ($address_id <= 0) {
    json_response(['success' => false, 'message' => 'Address ID is required'], 400);
}

try {
    $customer_id = $_SESSION['user_id'];
    
    db_delete("DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?", [$address_id, $customer_id]);
    
    json_response(['success' => true, 'message' => 'Address deleted']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
