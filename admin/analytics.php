<?php
/**
 * ============================================================================
 * AZEU WATER STATION - ANALYTICS PAGE
 * ============================================================================
 * 
 * Purpose: System analytics and reporting
 * Role: ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Analytics";
$page_css = "main.css";
$page_js = "analytics.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="content-title">Analytics</h1>
            <div class="filter-bar">
                <button class="filter-btn active" data-period="month">Month</button>
                <button class="filter-btn" data-period="week">Week</button>
                <button class="filter-btn" data-period="year">Year</button>
            </div>
        </div>
    </div>
    
    <!-- Revenue Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 32px;">
        <div class="stat-card">
            <div class="stat-icon success">
                <span class="material-icons">payments</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value" id="total-revenue">₱0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon primary">
                <span class="material-icons">shopping_cart</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value" id="total-orders">0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <span class="material-icons">trending_up</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Avg Order Value</div>
                <div class="stat-value" id="avg-order-value">₱0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <span class="material-icons">local_shipping</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Delivery Fees</div>
                <div class="stat-value" id="delivery-fees">₱0</div>
            </div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
        <!-- Revenue Trend Chart -->
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Revenue Trend</h3>
            <canvas id="revenue-chart"></canvas>
        </div>
        
        <!-- Order Status Breakdown -->
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Order Status</h3>
            <canvas id="status-chart"></canvas>
        </div>
    </div>
    
    <!-- Top Products & Customers -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Top Products</h3>
            <div id="top-products">
                <div style="text-align: center; padding: 20px;"><div class="spinner"></div></div>
            </div>
        </div>
        
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Top Customers</h3>
            <div id="top-customers">
                <div style="text-align: center; padding: 20px;"><div class="spinner"></div></div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
