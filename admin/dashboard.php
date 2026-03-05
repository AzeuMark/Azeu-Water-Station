<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF DASHBOARD
 * ============================================================================
 * 
 * Purpose: Main dashboard for staff role
 * Role: STAFF
 * 
 * Features:
 * - System-wide statistics
 * - Pending orders, pending accounts, low stock alerts
 * - Quick actions for common tasks
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Dashboard";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

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
        
        <div class="stat-card" onclick="window.location.href='pending_accounts.php'">
            <div class="stat-icon info">
                <span class="material-icons">person_add</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Accounts</div>
                <div class="stat-value" id="pending-accounts">0</div>
            </div>
        </div>
        
        <div class="stat-card" onclick="window.location.href='inventory.php'">
            <div class="stat-icon danger">
                <span class="material-icons">inventory</span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Low Stock Items</div>
                <div class="stat-value" id="low-stock">0</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="glass-card" style="margin-bottom: 32px;">
        <h3 style="margin-bottom: 20px;">Quick Actions</h3>
        <div class="quick-actions">
            <a href="orders.php" class="quick-action-btn">
                <span class="material-icons">receipt_long</span>
                <span>Manage Orders</span>
            </a>
            <a href="accounts.php" class="quick-action-btn">
                <span class="material-icons">people</span>
                <span>Manage Accounts</span>
            </a>
            <a href="inventory.php" class="quick-action-btn">
                <span class="material-icons">inventory_2</span>
                <span>Manage Inventory</span>
            </a>
            <a href="riders.php" class="quick-action-btn">
                <span class="material-icons">directions_bike</span>
                <span>Manage Riders</span>
            </a>
            <a href="appeals.php" class="quick-action-btn">
                <span class="material-icons">gavel</span>
                <span>Review Appeals</span>
            </a>
            <a href="settings.php" class="quick-action-btn">
                <span class="material-icons">settings</span>
                <span>Settings</span>
            </a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Recent Orders -->
        <div class="glass-card">
            <div class="preview-header">
                <h3>Recent Orders</h3>
                <a href="orders.php">View All →</a>
            </div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders">
                        <tr><td colspan="4" style="text-align: center; padding: 20px;"><div class="spinner"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pending Accounts -->
        <div class="glass-card">
            <div class="preview-header">
                <h3>Pending Accounts</h3>
                <a href="pending_accounts.php">View All →</a>
            </div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pending-accounts-tbody">
                        <tr><td colspan="3" style="text-align: center; padding: 20px;"><div class="spinner"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentOrders();
    loadPendingAccounts();
});

async function loadDashboardStats() {
    try {
        const response = await fetch('../api/analytics/dashboard.php');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            document.getElementById('total-orders').textContent = stats.total_orders || 0;
            document.getElementById('pending-orders').textContent = stats.pending_orders || 0;
            document.getElementById('pending-accounts').textContent = stats.pending_accounts || 0;
            document.getElementById('low-stock').textContent = stats.out_of_stock || 0;
        }
    } catch (error) {
        console.error('Failed to load stats:', error);
    }
}

async function loadRecentOrders() {
    try {
        const response = await fetch('../api/orders/list.php?limit=5');
        const data = await response.json();
        
        const tbody = document.getElementById('recent-orders');
        
        if (data.success && data.orders.length > 0) {
            let html = '';
            data.orders.forEach(order => {
                html += `
                    <tr>
                        <td><strong>#${order.id}</strong></td>
                        <td>${order.customer_name}</td>
                        <td><span class="badge badge-${order.status.replace('_', '-')}">${order.status}</span></td>
                        <td>${formatCurrency(order.total_amount)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No recent orders</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
    }
}

async function loadPendingAccounts() {
    try {
        const response = await fetch('../api/accounts/list.php?status=pending&limit=5');
        const data = await response.json();
        
        const tbody = document.getElementById('pending-accounts-tbody');
        
        if (data.success && data.accounts.length > 0) {
            let html = '';
            data.accounts.forEach(acc => {
                html += `
                    <tr>
                        <td>${acc.full_name}</td>
                        <td>${acc.username}</td>
                        <td>
                            <button class="btn-icon" onclick="approveAccount(${acc.id})" title="Approve">
                                <span class="material-icons">check_circle</span>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No pending accounts</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load accounts:', error);
    }
}

async function approveAccount(userId) {
    if (!confirm('Approve this account?')) return;
    
    try {
        const response = await fetch('../api/accounts/approve.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account approved', 'success');
            loadPendingAccounts();
            loadDashboardStats();
        } else {
            showToast(data.message || 'Failed to approve', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
