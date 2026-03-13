<?php
/**
 * ============================================================================
 * AZEU WATER STATION - LIST ACCOUNTS API
 * ============================================================================
 * 
 * Purpose: Get list of user accounts with filtering
 * Method: GET
 * Role: STAFF, ADMIN
 * 
 * Query Parameters:
 * - role: Filter by role (optional)
 * - status: Filter by status (optional)
 * - search: Search by name/username/email (optional)
 * - page: Page number (default: 1)
 * - limit: Items per page (default: 50)
 * 
 * Response:
 * {
 *   "success": true,
 *   "accounts": [...],
 *   "total": 100,
 *   "page": 1,
 *   "total_pages": 2
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('accounts/list');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$role_filter = sanitize($_GET['role'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(100, max(1, intval($_GET['limit'] ?? ITEMS_PER_PAGE)));
$offset = ($page - 1) * $limit;

try {
    $where_clauses = ["deleted_at IS NULL"];
    $params = [];
    
    // Role filter
    if (!empty($role_filter)) {
        $where_clauses[] = "role = ?";
        $params[] = $role_filter;
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $where_clauses[] = "status = ?";
        $params[] = $status_filter;
    }
    
    // Search filter
    if (!empty($search)) {
        $where_clauses[] = "(full_name LIKE ? OR username LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_sql = implode(' AND ', $where_clauses);
    
    // Count total
    $count_query = "SELECT COUNT(*) as total FROM users WHERE $where_sql";
    $total_result = db_fetch($count_query, $params);
    $total = $total_result['total'];
    
    // Get accounts
    $query = "SELECT id, username, full_name, email, phone, role, status, flag_reason,
              is_available, cancellation_count, created_at, updated_at
              FROM users 
              WHERE $where_sql
              ORDER BY created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $accounts = db_fetch_all($query, $params);
    
    $total_pages = ceil($total / $limit);
    
    logger_debug("Accounts list retrieved", ['count' => count($accounts), 'total' => $total]);
    
    json_response([
        'success' => true,
        'accounts' => $accounts,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => $total_pages
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
