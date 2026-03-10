<?php
/**
 * ============================================================================
 * AZEU WATER STATION - LIST ORDERS API
 * ============================================================================
 * 
 * Purpose: Get list of orders with filtering and pagination
 * Method: GET
 * Role: CUSTOMER, RIDER, STAFF, ADMIN
 * 
 * Query Parameters:
 * - status: Filter by status (optional)
 * - page: Page number (default: 1)
 * - limit: Items per page (default: 20)
 * 
 * Response:
 * {
 *   "success": true,
 *   "orders": [...],
 *   "total": 50,
 *   "page": 1,
 *   "total_pages": 3
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/list');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_login();

$status = sanitize($_GET['status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(100, max(1, intval($_GET['limit'] ?? ORDER_ITEMS_PER_PAGE)));
$offset = ($page - 1) * $limit;

try {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Build query based on role
    $where_clauses = [];
    $params = [];
    
    // Customer: only their orders
    if ($role === ROLE_CUSTOMER) {
        $where_clauses[] = "o.customer_id = ?";
        $params[] = $user_id;
    }
    
    // Rider: only assigned orders
    if ($role === ROLE_RIDER) {
        $where_clauses[] = "o.rider_id = ?";
        $params[] = $user_id;
    }
    
    // Status filter
    if (!empty($status)) {
        $where_clauses[] = "o.status = ?";
        $params[] = $status;
    }
    
    $where_sql = empty($where_clauses) ? '1=1' : implode(' AND ', $where_clauses);
    
    // Count total
    $count_query = "SELECT COUNT(*) as total FROM orders o WHERE $where_sql";
    $total_result = db_fetch($count_query, $params);
    $total = $total_result['total'];
    
    // Get orders
    $query = "SELECT o.*, 
              u.full_name as customer_name,
              u.phone as customer_phone,
              r.full_name as rider_name,
              (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
              FROM orders o
              JOIN users u ON o.customer_id = u.id
              LEFT JOIN users r ON o.rider_id = r.id
              WHERE $where_sql
              ORDER BY o.created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $orders = db_fetch_all($query, $params);
    
    $total_pages = ceil($total / $limit);
    
    logger_debug("Orders list retrieved", ['count' => count($orders), 'total' => $total]);
    
    json_response([
        'success' => true,
        'orders' => $orders,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => $total_pages
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
