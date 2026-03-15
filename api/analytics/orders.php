<?php
/**
 * ============================================================================
 * AZEU WATER STATION - ORDER ANALYTICS API
 * ============================================================================
 * 
 * Purpose: Get order analytics with time-based data
 * Method: GET
 * Role: STAFF, ADMIN
 * 
 * Query Parameters:
 * - period: "today" | "week" | "month" | "year" (default: "month")
 * 
 * Response:
 * {
 *   "success": true,
 *   "analytics": {
 *     "total_orders": 100,
 *     "order_trends": [...],
 *     "status_breakdown": {...},
 *     "popular_items": [...]
 *   }
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('analytics/orders');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$period = sanitize($_GET['period'] ?? 'month');

try {
    // Determine date range
    $date_condition = "";
    switch ($period) {
        case 'today':
            $date_condition = "DATE(order_date) = CURDATE()";
            break;
        case 'week':
            $date_condition = "order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'year':
            $date_condition = "order_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
        case 'month':
        default:
            $date_condition = "order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
    
    // Total orders in period
    $total = db_fetch("SELECT COUNT(*) as total FROM orders WHERE $date_condition");
    
    // Completed orders (delivered + picked_up + accepted)
    $completed = db_fetch(
        "SELECT COUNT(*) as total FROM orders 
         WHERE $date_condition AND status IN ('delivered', 'picked_up', 'accepted')"
    );
    
    // Cancelled orders
    $cancelled = db_fetch(
        "SELECT COUNT(*) as total FROM orders 
         WHERE $date_condition AND status = 'cancelled'"
    );
    
    // Pending orders
    $pending = db_fetch(
        "SELECT COUNT(*) as total FROM orders 
         WHERE $date_condition AND status = 'pending'"
    );
    
    // Active orders (in-progress: confirmed, assigned, on_delivery, ready_for_pickup, reassign_requested)
    $active = db_fetch(
        "SELECT COUNT(*) as total FROM orders 
         WHERE $date_condition AND status IN ('confirmed', 'assigned', 'on_delivery', 'ready_for_pickup', 'reassign_requested')"
    );
    
    // Status breakdown
    $status_breakdown = db_fetch_all(
        "SELECT status, COUNT(*) as count FROM orders WHERE $date_condition GROUP BY status"
    );
    
    $status_data = [];
    foreach ($status_breakdown as $row) {
        $status_data[$row['status']] = $row['count'];
    }
    
    // Order trends (daily)
    $trends = db_fetch_all(
        "SELECT DATE(order_date) as date, COUNT(*) as count 
         FROM orders WHERE $date_condition 
         GROUP BY DATE(order_date) 
         ORDER BY DATE(order_date) ASC"
    );
    
    // Popular items
    $popular_items = db_fetch_all(
        "SELECT oi.item_name, SUM(oi.quantity) as total_quantity, COUNT(DISTINCT oi.order_id) as order_count
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE $date_condition
         GROUP BY oi.item_name
         ORDER BY total_quantity DESC
         LIMIT 10"
    );
    
    // Payment type breakdown
    $payment_breakdown = db_fetch_all(
        "SELECT payment_type, COUNT(*) as count FROM orders WHERE $date_condition GROUP BY payment_type"
    );
    
    // Delivery type breakdown
    $delivery_breakdown = db_fetch_all(
        "SELECT delivery_type, COUNT(*) as count FROM orders WHERE $date_condition GROUP BY delivery_type"
    );
    
    json_response([
        'success' => true,
        'analytics' => [
            'total_orders' => $total['total'],
            'completed_orders' => intval($completed['total'] ?? 0),
            'cancelled_orders' => intval($cancelled['total'] ?? 0),
            'pending_orders' => intval($pending['total'] ?? 0),
            'active_orders' => intval($active['total'] ?? 0),
            'status_breakdown' => $status_data,
            'order_trends' => $trends,
            'popular_items' => $popular_items,
            'payment_breakdown' => $payment_breakdown,
            'delivery_breakdown' => $delivery_breakdown
        ]
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
