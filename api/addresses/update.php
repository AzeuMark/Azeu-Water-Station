<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE ADDRESS API
 * ============================================================================
 * 
 * Purpose: Update a delivery address
 * Method: POST
 * Role: CUSTOMER
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('addresses/update');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_CUSTOMER]);

$input = json_decode(file_get_contents('php://input'), true);
$address_id = intval($input['address_id'] ?? 0);
$label = isset($input['label']) ? sanitize($input['label']) : null;
$full_address = isset($input['full_address']) ? sanitize($input['full_address']) : null;
$is_default = isset($input['is_default']) ? intval($input['is_default']) : null;

if ($address_id <= 0) {
    json_response(['success' => false, 'message' => 'Address ID is required'], 400);
}

try {
    $customer_id = $_SESSION['user_id'];
    
    // Verify ownership
    $address = db_fetch("SELECT * FROM customer_addresses WHERE id = ? AND customer_id = ?", [$address_id, $customer_id]);
    if (!$address) {
        json_response(['success' => false, 'message' => 'Address not found'], 404);
    }
    
    $update_fields = [];
    $update_params = [];
    
    if ($label !== null) {
        $update_fields[] = "label = ?";
        $update_params[] = $label;
    }
    
    if ($full_address !== null) {
        $update_fields[] = "full_address = ?";
        $update_params[] = $full_address;
    }
    
    if ($is_default !== null) {
        if ($is_default == 1) {
            db_update("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?", [$customer_id]);
        }
        $update_fields[] = "is_default = ?";
        $update_params[] = $is_default;
    }
    
    if (!empty($update_fields)) {
        $update_params[] = $address_id;
        $sql = "UPDATE customer_addresses SET " . implode(', ', $update_fields) . " WHERE id = ?";
        db_update($sql, $update_params);
    }
    
    json_response(['success' => true, 'message' => 'Address updated']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
