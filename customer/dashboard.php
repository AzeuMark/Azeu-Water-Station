<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER DASHBOARD
 * ============================================================================
 * 
 * Purpose: Main dashboard for customer role
 * Displays: Order summary, recent orders, quick actions
 * Role: CUSTOMER
 * 
 * Features:
 * - Order statistics cards
 * - Recent order list
 * - Quick action buttons (Place Order, View Addresses)
 * - Active order tracking
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Dashboard";
$page_css = "dashboard.css";
$page_js = "dashboard.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Dashboard</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>Dashboard</span>
        </p>
    </div>
    
    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 32px;">
        <div class="stat-card" onclick="window.location.href='orders.php'">
            <div class="stat-icon primary">
                <span class="material-icons">shopping_cart</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value" id="total-orders">0</div>
            </div>
        </div>
        
        <div class="stat-card" onclick="window.location.href='orders.php?status=pending'">
            <div class="stat-icon warning">
                <span class="material-icons">schedule</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value" id="pending-orders">0</div>
            </div>
        </div>
        
        <div class="stat-card" onclick="window.location.href='orders.php?status=active'">
            <div class="stat-icon info">
                <span class="material-icons">local_shipping</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Orders</div>
                <div class="stat-value" id="active-orders">0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <span class="material-icons">check_circle</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Completed Orders</div>
                <div class="stat-value" id="completed-orders">0</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="glass-card" style="margin-bottom: 32px;">
        <h3 style="margin-bottom: 20px;">Quick Actions</h3>
        <div class="quick-actions">
            <a href="place_order.php" class="quick-action-btn">
                <span class="material-icons">add_shopping_cart</span>
                <span>Place Order</span>
            </a>
            <a href="orders.php" class="quick-action-btn">
                <span class="material-icons">receipt_long</span>
                <span>My Orders</span>
            </a>
            <a href="addresses.php" class="quick-action-btn">
                <span class="material-icons">location_on</span>
                <span>Addresses</span>
            </a>
            <a href="settings.php" class="quick-action-btn">
                <span class="material-icons">settings</span>
                <span>Settings</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="glass-card">
        <div class="preview-header">
            <h3>Recent Orders</h3>
            <a href="orders.php">View All →</a>
        </div>
        
        <div class="data-table-wrapper">
            <table class="data-table" id="recent-orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="recent-orders-tbody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
