<?php
/**
 * ============================================================================
 * AZEU WATER STATION - TOGGLE RIDER AVAILABILITY API
 * ============================================================================
 * 
 * Purpose: Toggle rider availability status
 * Method: POST
 * Role: RIDER (own), STAFF, ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "rider_id": 5,
 *   "is_available": 1 | 0
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('riders/toggle_availability');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$input = json_decode(file_get_contents('php://input'), true);
$rider_id = intval($input['rider_id'] ?? 0);
$is_available = intval($input['is_available'] ?? 0);

if ($rider_id <= 0) {
    json_response(['success' => false, 'message' => 'Rider ID is required'], 400);
}

$role = $_SESSION['role'];

// Riders can only toggle their own availability
if ($role === ROLE_RIDER && $rider_id != $_SESSION['user_id']) {
    json_response(['success' => false, 'message' => 'Access denied'], 403);
}

try {
    db_update("UPDATE users SET is_available = ? WHERE id = ? AND role = 'rider'", [$is_available, $rider_id]);
    
    logger_info("Rider availability toggled", ['rider_id' => $rider_id, 'is_available' => $is_available]);
    
    json_response([
        'success' => true,
        'message' => 'Availability updated'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
