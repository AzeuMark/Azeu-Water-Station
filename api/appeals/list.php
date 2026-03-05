<?php
/**
 * ============================================================================
 * AZEU WATER STATION - LIST APPEALS API
 * ============================================================================
 * 
 * Purpose: Get list of cancellation appeals
 * Method: GET
 * Role: CUSTOMER (own), STAFF, ADMIN
 * 
 * Query Parameters:
 * - status: Filter by status (optional)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('appeals/list');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$status_filter = sanitize($_GET['status'] ?? '');
$role = $_SESSION['role'];

try {
    $where_clauses = [];
    $params = [];
    
    // Customers can only see their own appeals
    if ($role === ROLE_CUSTOMER) {
        $where_clauses[] = "a.customer_id = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $where_clauses[] = "a.status = ?";
        $params[] = $status_filter;
    }
    
    $where_sql = empty($where_clauses) ? '1=1' : implode(' AND ', $where_clauses);
    
    $query = "SELECT a.*, 
              u.full_name as customer_name,
              r.full_name as reviewer_name
              FROM cancellation_appeals a
              JOIN users u ON a.customer_id = u.id
              LEFT JOIN users r ON a.reviewed_by = r.id
              WHERE $where_sql
              ORDER BY a.created_at DESC";
    
    $appeals = db_fetch_all($query, $params);
    
    json_response([
        'success' => true,
        'appeals' => $appeals
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
