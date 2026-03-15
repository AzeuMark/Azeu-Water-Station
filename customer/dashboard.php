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
 * - Welcome banner with dynamic greeting
 * - Order statistics cards with animated counters
 * - Quick action buttons (Place Order, My Orders, Addresses, Settings)
 * - Recent orders panel with table & mobile card views
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
    
    <!-- Welcome Banner -->
    <div class="dash-welcome">
        <div class="dash-welcome-text">
            <h2 id="greeting-text">Welcome back!</h2>
            <p>Here's an overview of your orders and account activity.</p>
        </div>
        <div class="dash-welcome-icon">
            <span class="material-icons">water_drop</span>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="dash-stats-grid">
        <div class="dash-stat-card dash-stat-primary" onclick="window.location.href='orders.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">shopping_cart</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="total-orders">0</span>
                <span class="dash-stat-label">Total Orders</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>
        
        <div class="dash-stat-card dash-stat-warning" onclick="window.location.href='orders.php?status=pending'">
            <div class="dash-stat-icon">
                <span class="material-icons">schedule</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="pending-orders">0</span>
                <span class="dash-stat-label">Pending Orders</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>
        
        <div class="dash-stat-card dash-stat-info" onclick="window.location.href='orders.php?status=active'">
            <div class="dash-stat-icon">
                <span class="material-icons">local_shipping</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="active-orders">0</span>
                <span class="dash-stat-label">Active Orders</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>
        
        <div class="dash-stat-card dash-stat-success" onclick="window.location.href='orders.php?status=accepted'">
            <div class="dash-stat-icon">
                <span class="material-icons">check_circle</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="completed-orders">0</span>
                <span class="dash-stat-label">Completed Orders</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="dash-section">
        <div class="dash-section-header">
            <span class="material-icons">bolt</span>
            <h3>Quick Actions</h3>
        </div>
        <div class="dash-actions-grid">
            <a href="place_order.php" class="dash-action-card">
                <div class="dash-action-icon primary"><span class="material-icons">add_shopping_cart</span></div>
                <span class="dash-action-label">Place Order</span>
            </a>
            <a href="orders.php" class="dash-action-card">
                <div class="dash-action-icon info"><span class="material-icons">receipt_long</span></div>
                <span class="dash-action-label">My Orders</span>
            </a>
            <a href="addresses.php" class="dash-action-card">
                <div class="dash-action-icon warning"><span class="material-icons">location_on</span></div>
                <span class="dash-action-label">Addresses</span>
            </a>
            <a href="settings.php" class="dash-action-card">
                <div class="dash-action-icon neutral"><span class="material-icons">settings</span></div>
                <span class="dash-action-label">Settings</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Orders Panel -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="material-icons">receipt_long</span>
                <h3>Recent Orders</h3>
            </div>
            <a href="orders.php" class="dash-panel-link">View All <span class="material-icons">arrow_forward</span></a>
        </div>
        <div class="dash-panel-body">
            <!-- Desktop table -->
            <div class="dash-panel-table-view">
                <div class="data-table-wrapper">
                    <table class="data-table" id="recent-orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="recent-orders-tbody">
                            <tr><td colspan="6" style="text-align: center; padding: 20px;"><div class="spinner"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile/Tablet cards -->
            <div class="dash-panel-card-view" id="recent-orders-cards">
                <div class="spinner" style="margin: 20px auto;"></div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
