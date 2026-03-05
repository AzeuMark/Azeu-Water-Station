<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DASHBOARD ANALYTICS API
 * ============================================================================
 * 
 * Purpose: Get dashboard statistics for all roles
 * Method: GET
 * Role: ALL AUTHENTICATED USERS
 * 
 * Returns role-specific statistics:
 * - Customer: order counts, total spent
 * - Rider: delivery counts, today's deliveries
 * - Staff/Admin: system-wide statistics
 * 
 * Response:
 * {
 *   "success": true,
 *   "stats": {
 *     "total_orders": 100,
 *     "pending_orders": 10,
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

log_api_entry('analytics/dashboard');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

try {
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    $stats = [];
    
    if ($role === ROLE_CUSTOMER) {
        // Customer statistics
        $order_stats = db_fetch(
            "SELECT 
             COUNT(*) as total_orders,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
             SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
             SUM(CASE WHEN status IN ('assigned', 'on_delivery') THEN 1 ELSE 0 END) as active_orders,
             SUM(CASE WHEN status IN ('delivered', 'picked_up', 'accepted') THEN 1 ELSE 0 END) as completed_orders,
             SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
             SUM(CASE WHEN status = 'accepted' THEN total_amount ELSE 0 END) as total_spent
             FROM orders WHERE customer_id = ?",
            [$user_id]
        );
        
        $user_info = db_fetch("SELECT cancellation_count FROM users WHERE id = ?", [$user_id]);
        $address_count = db_fetch("SELECT COUNT(*) as count FROM customer_addresses WHERE customer_id = ?", [$user_id]);
        
        $stats = array_merge($order_stats, [
            'cancellation_count' => $user_info['cancellation_count'],
            'address_count' => $address_count['count']
        ]);
        
    } elseif ($role === ROLE_RIDER) {
        // Rider statistics
        $delivery_stats = db_fetch(
            "SELECT 
             COUNT(*) as total_deliveries,
             SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as pending_deliveries,
             SUM(CASE WHEN status = 'on_delivery' THEN 1 ELSE 0 END) as on_delivery,
             SUM(CASE WHEN status IN ('delivered', 'accepted') THEN 1 ELSE 0 END) as completed_deliveries,
             SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) as today_deliveries
             FROM orders WHERE rider_id = ?",
            [$user_id]
        );
        
        $stats = $delivery_stats;
        
    } else {
        // Staff/Admin statistics
        $order_stats = db_fetch(
            "SELECT 
             COUNT(*) as total_orders,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
             SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
             SUM(CASE WHEN status IN ('assigned', 'on_delivery') THEN 1 ELSE 0 END) as active_orders,
             SUM(CASE WHEN status IN ('delivered', 'picked_up', 'accepted') THEN 1 ELSE 0 END) as completed_orders,
             SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
             SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) as today_orders,
             SUM(CASE WHEN status = 'accepted' THEN total_amount ELSE 0 END) as total_revenue
             FROM orders"
        );
        
        $user_stats = db_fetch(
            "SELECT 
             COUNT(*) as total_users,
             SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
             SUM(CASE WHEN role = 'rider' THEN 1 ELSE 0 END) as total_riders,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_accounts,
             SUM(CASE WHEN status = 'flagged' THEN 1 ELSE 0 END) as flagged_accounts
             FROM users WHERE deleted_at IS NULL"
        );
        
        $inventory_stats = db_fetch(
            "SELECT 
             COUNT(*) as total_items,
             SUM(CASE WHEN status = 'active' AND stock_count > 0 THEN 1 ELSE 0 END) as available_items,
             SUM(CASE WHEN stock_count = 0 THEN 1 ELSE 0 END) as out_of_stock
             FROM inventory"
        );
        
        $appeal_stats = db_fetch(
            "SELECT COUNT(*) as pending_appeals FROM cancellation_appeals WHERE status = 'pending'"
        );
        
        $stats = array_merge($order_stats, $user_stats, $inventory_stats, $appeal_stats);
    }
    
    json_response([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
