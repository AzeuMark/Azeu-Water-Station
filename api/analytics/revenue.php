<?php
/**
 * ============================================================================
 * AZEU WATER STATION - REVENUE ANALYTICS API
 * ============================================================================
 * 
 * Purpose: Get revenue and financial analytics
 * Method: GET
 * Role: ADMIN
 * 
 * Query Parameters:
 * - period: "today" | "week" | "month" | "year" (default: "month")
 * 
 * Response:
 * {
 *   "success": true,
 *   "analytics": {
 *     "total_revenue": 50000.00,
 *     "revenue_trends": [...],
 *     "average_order_value": 250.00,
 *     ...
 *   }
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('analytics/revenue');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

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
    
    // Fulfilled statuses: delivered, picked_up, and customer-confirmed (accepted)
    $fulfilled_condition = "status IN ('delivered', 'picked_up', 'accepted')";
    
    // Revenue statistics (fulfilled orders)
    $revenue_stats = db_fetch(
        "SELECT 
         SUM(total_amount) as total_revenue,
         SUM(subtotal) as total_product_revenue,
         SUM(delivery_fee) as total_delivery_fees,
         AVG(total_amount) as average_order_value,
         COUNT(*) as completed_orders
         FROM orders 
         WHERE $date_condition AND $fulfilled_condition"
    );
    
    // Revenue trends (daily)
    $revenue_trends = db_fetch_all(
        "SELECT 
         DATE(order_date) as date, 
         SUM(total_amount) as revenue,
         COUNT(*) as orders
         FROM orders 
         WHERE $date_condition AND $fulfilled_condition
         GROUP BY DATE(order_date) 
         ORDER BY DATE(order_date) ASC"
    );
    
    // Top revenue items
    $top_items = db_fetch_all(
        "SELECT 
         oi.item_name,
         SUM(oi.subtotal) as total_revenue,
         SUM(oi.quantity) as total_quantity
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE $date_condition AND o.$fulfilled_condition
         GROUP BY oi.item_name
         ORDER BY total_revenue DESC
         LIMIT 10"
    );
    
    // Top customers by spending
    $top_customers = db_fetch_all(
        "SELECT 
         u.full_name,
         u.email,
         SUM(o.total_amount) as total_spent,
         COUNT(o.id) as order_count
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         WHERE $date_condition AND o.$fulfilled_condition
         GROUP BY u.id
         ORDER BY total_spent DESC
         LIMIT 10"
    );
    
    // Revenue by payment type
    $payment_revenue = db_fetch_all(
        "SELECT 
         payment_type,
         SUM(total_amount) as revenue,
         COUNT(*) as orders
         FROM orders 
         WHERE $date_condition AND $fulfilled_condition
         GROUP BY payment_type"
    );
    
    // Revenue by delivery type
    $delivery_revenue = db_fetch_all(
        "SELECT 
         delivery_type,
         SUM(total_amount) as revenue,
         COUNT(*) as orders
         FROM orders 
         WHERE $date_condition AND $fulfilled_condition
         GROUP BY delivery_type"
    );
    
    json_response([
        'success' => true,
        'analytics' => [
            'total_revenue' => floatval($revenue_stats['total_revenue'] ?? 0),
            'total_product_revenue' => floatval($revenue_stats['total_product_revenue'] ?? 0),
            'total_delivery_fees' => floatval($revenue_stats['total_delivery_fees'] ?? 0),
            'average_order_value' => floatval($revenue_stats['average_order_value'] ?? 0),
            'completed_orders' => intval($revenue_stats['completed_orders'] ?? 0),
            'revenue_trends' => $revenue_trends,
            'top_items' => $top_items,
            'top_customers' => $top_customers,
            'payment_revenue' => $payment_revenue,
            'delivery_revenue' => $delivery_revenue
        ]
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
