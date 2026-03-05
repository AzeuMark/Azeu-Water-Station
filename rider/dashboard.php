<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DASHBOARD
 * ============================================================================
 * 
 * Purpose: Main dashboard for rider role
 * Role: RIDER
 * 
 * Features:
 * - Delivery statistics (pending, on delivery, completed)
 * - Today's deliveries
 * - Availability toggle
 * - Recent deliveries list
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Dashboard";
$page_css = "dashboard.css";
$page_js = "dashboard.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 class="content-title">Dashboard</h1>
                <p class="content-breadcrumb">
                    <span>Home</span>
                    <span class="breadcrumb-separator">/</span>
                    <span>Dashboard</span>
                </p>
            </div>
            
            <!-- Availability Toggle -->
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 20px; background: var(--surface-card); border-radius: var(--radius); border: 1px solid var(--border);">
                <span style="font-weight: 600;">Availability:</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="availability-toggle">
                    <span class="toggle-slider"></span>
                </label>
                <span id="availability-label" style="font-weight: 600; color: var(--success);">Available</span>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 32px;">
        <div class="stat-card">
            <div class="stat-icon warning">
                <span class="material-icons">assignment</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Deliveries</div>
                <div class="stat-value" id="pending-deliveries">0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <span class="material-icons">local_shipping</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">On Delivery</div>
                <div class="stat-value" id="on-delivery">0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <span class="material-icons">done_all</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Completed</div>
                <div class="stat-value" id="completed-deliveries">0</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon primary">
                <span class="material-icons">today</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Today's Deliveries</div>
                <div class="stat-value" id="today-deliveries">0</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="glass-card" style="margin-bottom: 32px;">
        <h3 style="margin-bottom: 20px;">Quick Actions</h3>
        <div class="quick-actions">
            <a href="assigned_deliveries.php" class="quick-action-btn">
                <span class="material-icons">assignment</span>
                <span>Assigned Deliveries</span>
            </a>
            <a href="deliveries.php" class="quick-action-btn">
                <span class="material-icons">local_shipping</span>
                <span>Active Deliveries</span>
            </a>
            <a href="delivery_history.php" class="quick-action-btn">
                <span class="material-icons">history</span>
                <span>Delivery History</span>
            </a>
            <a href="settings.php" class="quick-action-btn">
                <span class="material-icons">settings</span>
                <span>Settings</span>
            </a>
        </div>
    </div>
    
    <!-- Active Deliveries -->
    <div class="glass-card">
        <div class="preview-header">
            <h3>Active Deliveries</h3>
            <a href="deliveries.php">View All →</a>
        </div>
        
        <div class="data-table-wrapper">
            <table class="data-table" id="active-deliveries-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="active-deliveries-tbody">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
