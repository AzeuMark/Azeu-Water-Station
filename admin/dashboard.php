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
    
    <!-- Welcome Banner -->
    <div class="dash-welcome">
        <div class="dash-welcome-text">
            <h2 id="greeting-text">Welcome back!</h2>
            <p>Here's what's happening with your water station today.</p>
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
        
        <div class="dash-stat-card dash-stat-info" onclick="window.location.href='pending_accounts.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">person_add</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="pending-accounts">0</span>
                <span class="dash-stat-label">Pending Accounts</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>
        
        <div class="dash-stat-card dash-stat-danger" onclick="window.location.href='inventory.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">inventory</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="low-stock">0</span>
                <span class="dash-stat-label">Low Stock Items</span>
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
            <a href="orders.php" class="dash-action-card">
                <div class="dash-action-icon primary"><span class="material-icons">receipt_long</span></div>
                <span class="dash-action-label">Manage Orders</span>
            </a>
            <a href="accounts.php" class="dash-action-card">
                <div class="dash-action-icon info"><span class="material-icons">people</span></div>
                <span class="dash-action-label">Manage Accounts</span>
            </a>
            <a href="inventory.php" class="dash-action-card">
                <div class="dash-action-icon success"><span class="material-icons">inventory_2</span></div>
                <span class="dash-action-label">Manage Inventory</span>
            </a>
            <a href="riders.php" class="dash-action-card">
                <div class="dash-action-icon warning"><span class="material-icons">directions_bike</span></div>
                <span class="dash-action-label">Manage Riders</span>
            </a>
            <a href="appeals.php" class="dash-action-card">
                <div class="dash-action-icon danger"><span class="material-icons">gavel</span></div>
                <span class="dash-action-label">Review Appeals</span>
            </a>
            <a href="settings.php" class="dash-action-card">
                <div class="dash-action-icon neutral"><span class="material-icons">settings</span></div>
                <span class="dash-action-label">Settings</span>
            </a>
        </div>
    </div>
    
    <!-- Bottom Grid: Recent Orders + Pending Accounts -->
    <div class="dash-bottom-grid">
        <!-- Recent Orders -->
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
                <!-- Mobile/Tablet cards -->
                <div class="dash-panel-card-view" id="recent-orders-cards">
                    <div class="spinner" style="margin: 20px auto;"></div>
                </div>
            </div>
        </div>
        
        <!-- Pending Accounts -->
        <div class="dash-panel">
            <div class="dash-panel-header">
                <div class="dash-panel-title">
                    <span class="material-icons">person_add</span>
                    <h3>Pending Accounts</h3>
                </div>
                <a href="pending_accounts.php" class="dash-panel-link">View All <span class="material-icons">arrow_forward</span></a>
            </div>
            <div class="dash-panel-body">
                <!-- Desktop table -->
                <div class="dash-panel-table-view">
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
                <!-- Mobile/Tablet cards -->
                <div class="dash-panel-card-view" id="pending-accounts-cards">
                    <div class="spinner" style="margin: 20px auto;"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* ============================================================================
   DASHBOARD — Revamped Layout
   ============================================================================ */

/* Welcome Banner */
.dash-welcome {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, var(--primary) 0%, #1976D2 50%, #1E88E5 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 28px;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.dash-welcome::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 50%;
}

.dash-welcome-text h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.dash-welcome-text p {
    margin: 0;
    opacity: 0.85;
    font-size: 0.95rem;
}

.dash-welcome-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dash-welcome-icon .material-icons {
    font-size: 30px;
}

/* Statistics Cards */
.dash-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.dash-stat-card {
    background: var(--surface-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.dash-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    border-radius: 4px 0 0 4px;
}

.dash-stat-primary::before { background: var(--primary); }
.dash-stat-warning::before { background: #FF9800; }
.dash-stat-info::before    { background: #29B6F6; }
.dash-stat-danger::before  { background: #EF5350; }

.dash-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: transparent;
}

.dash-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dash-stat-icon .material-icons {
    font-size: 24px;
}

.dash-stat-primary .dash-stat-icon { background: rgba(21, 101, 192, 0.1); color: var(--primary); }
.dash-stat-warning .dash-stat-icon { background: rgba(255, 152, 0, 0.1); color: #FF9800; }
.dash-stat-info .dash-stat-icon    { background: rgba(41, 182, 246, 0.1); color: #29B6F6; }
.dash-stat-danger .dash-stat-icon  { background: rgba(239, 83, 80, 0.1); color: #EF5350; }

.dash-stat-info:not(.dash-stat-icon) {
    flex: 1;
    min-width: 0;
}

.dash-stat-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.1;
}

.dash-stat-label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
    font-weight: 500;
}

.dash-stat-arrow {
    font-size: 18px;
    color: var(--text-muted);
    opacity: 0;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.dash-stat-card:hover .dash-stat-arrow {
    opacity: 1;
    transform: translateX(2px);
}

/* Section Headers */
.dash-section {
    margin-bottom: 28px;
}

.dash-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.dash-section-header .material-icons {
    font-size: 22px;
    color: var(--primary);
}

.dash-section-header h3 {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
}

/* Quick Actions Grid */
.dash-actions-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
}

.dash-action-card {
    background: var(--surface-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.dash-action-card:hover {
    border-color: var(--primary);
    background: var(--surface);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.dash-action-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.dash-action-card:hover .dash-action-icon {
    transform: scale(1.1);
}

.dash-action-icon .material-icons {
    font-size: 24px;
}

.dash-action-icon.primary { background: rgba(21, 101, 192, 0.1); color: var(--primary); }
.dash-action-icon.info    { background: rgba(41, 182, 246, 0.1); color: #29B6F6; }
.dash-action-icon.success { background: rgba(102, 187, 106, 0.1); color: #66BB6A; }
.dash-action-icon.warning { background: rgba(255, 167, 38, 0.1); color: #FFA726; }
.dash-action-icon.danger  { background: rgba(239, 83, 80, 0.1); color: #EF5350; }
.dash-action-icon.neutral { background: rgba(120, 144, 156, 0.1); color: #78909C; }

.dash-action-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
    line-height: 1.3;
}

/* Bottom Grid Panels */
.dash-bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.dash-panel {
    background: var(--surface-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}

.dash-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.dash-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.dash-panel-title .material-icons {
    font-size: 22px;
    color: var(--primary);
}

.dash-panel-title h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
}

.dash-panel-link {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--primary);
    text-decoration: none;
    transition: gap 0.2s ease;
}

.dash-panel-link:hover {
    gap: 8px;
}

.dash-panel-link .material-icons {
    font-size: 16px;
}

.dash-panel-body {
    padding: 0;
}

/* Panel table/card view switching */
.dash-panel-table-view {
    display: block;
}

.dash-panel-card-view {
    display: none;
}

/* Mini card items for mobile panel view */
.dash-mini-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.dash-mini-card:last-child {
    border-bottom: none;
}

.dash-mini-card:hover {
    background: var(--hover, var(--surface));
}

.dash-mini-card-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    flex: 1;
}

.dash-mini-card-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dash-mini-card-sub {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.dash-mini-card-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.dash-empty-state {
    text-align: center;
    padding: 32px 20px;
    color: var(--text-muted);
}

.dash-empty-state .material-icons {
    font-size: 40px;
    opacity: 0.3;
    margin-bottom: 8px;
    display: block;
}

.dash-empty-state p {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 500;
}

/* ============================================================================
   RESPONSIVE BREAKPOINTS
   ============================================================================ */

/* Tablet landscape */
@media (max-width: 1024px) {
    .dash-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .dash-actions-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .dash-bottom-grid {
        grid-template-columns: 1fr;
    }
    
    .dash-panel-table-view {
        display: none;
    }
    
    .dash-panel-card-view {
        display: block;
    }
}

/* Tablet portrait */
@media (max-width: 768px) {
    .dash-welcome {
        padding: 22px 20px;
    }
    
    .dash-welcome-text h2 {
        font-size: 1.25rem;
    }
    
    .dash-welcome-text p {
        font-size: 0.85rem;
    }
    
    .dash-welcome-icon {
        width: 48px;
        height: 48px;
    }
    
    .dash-welcome-icon .material-icons {
        font-size: 26px;
    }
    
    .dash-stat-card {
        padding: 16px;
    }
    
    .dash-stat-value {
        font-size: 1.5rem;
    }
    
    .dash-stat-arrow {
        display: none;
    }
    
    .dash-actions-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .dash-action-card {
        padding: 16px 10px;
    }
    
    .dash-action-icon {
        width: 40px;
        height: 40px;
    }
    
    .dash-action-icon .material-icons {
        font-size: 22px;
    }
    
    .dash-action-label {
        font-size: 0.75rem;
    }
}

/* Mobile */
@media (max-width: 480px) {
    .dash-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .dash-stat-card {
        padding: 14px 12px;
        gap: 10px;
    }
    
    .dash-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }
    
    .dash-stat-icon .material-icons {
        font-size: 20px;
    }
    
    .dash-stat-value {
        font-size: 1.3rem;
    }
    
    .dash-stat-label {
        font-size: 0.7rem;
    }
    
    .dash-actions-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    
    .dash-action-card {
        padding: 14px 8px;
        gap: 8px;
    }
    
    .dash-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
    }
    
    .dash-action-icon .material-icons {
        font-size: 20px;
    }
    
    .dash-action-label {
        font-size: 0.72rem;
    }
    
    .dash-welcome {
        padding: 18px 16px;
        border-radius: 12px;
    }
    
    .dash-welcome-text h2 {
        font-size: 1.1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setGreeting();
    loadDashboardStats();
    loadRecentOrders();
    loadPendingAccounts();
});

function setGreeting() {
    const hour = new Date().getHours();
    let greeting = 'Good evening!';
    if (hour < 12) greeting = 'Good morning!';
    else if (hour < 18) greeting = 'Good afternoon!';
    document.getElementById('greeting-text').textContent = greeting;
}

async function loadDashboardStats() {
    try {
        const response = await fetch('../api/analytics/dashboard.php');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            animateValue('total-orders', stats.total_orders || 0);
            animateValue('pending-orders', stats.pending_orders || 0);
            animateValue('pending-accounts', stats.pending_accounts || 0);
            animateValue('low-stock', stats.out_of_stock || 0);
        }
    } catch (error) {
        console.error('Failed to load stats:', error);
    }
}

function animateValue(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    target = parseInt(target) || 0;
    if (target === 0) { el.textContent = '0'; return; }
    
    let current = 0;
    const duration = 600;
    const step = Math.max(1, Math.ceil(target / (duration / 16)));
    const timer = setInterval(() => {
        current += step;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = current.toLocaleString();
    }, 16);
}

async function loadRecentOrders() {
    try {
        const response = await fetch('../api/orders/list.php?limit=5');
        const data = await response.json();
        
        const tbody = document.getElementById('recent-orders');
        const cardsContainer = document.getElementById('recent-orders-cards');
        
        if (data.success && data.orders.length > 0) {
            let html = '';
            let cardsHtml = '';
            data.orders.forEach(order => {
                html += `
                    <tr>
                        <td><strong>#${order.id}</strong></td>
                        <td>${order.customer_name}</td>
                        <td><span class="badge badge-${order.status}">${order.status.replace(/_/g, ' ')}</span></td>
                        <td>${formatCurrency(order.total_amount)}</td>
                    </tr>
                `;
                cardsHtml += `
                    <div class="dash-mini-card">
                        <div class="dash-mini-card-left">
                            <span class="dash-mini-card-title">#${order.id} — ${order.customer_name}</span>
                            <span class="dash-mini-card-sub">${formatCurrency(order.total_amount)}</span>
                        </div>
                        <div class="dash-mini-card-right">
                            <span class="badge badge-${order.status}">${order.status.replace(/_/g, ' ')}</span>
                        </div>
                    </div>
                `;
            });
            tbody.innerHTML = html;
            if (cardsContainer) cardsContainer.innerHTML = cardsHtml;
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No recent orders</td></tr>';
            if (cardsContainer) cardsContainer.innerHTML = '<div class="dash-empty-state"><span class="material-icons">receipt_long</span><p>No recent orders</p></div>';
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
        const cardsContainer = document.getElementById('pending-accounts-cards');
        
        if (data.success && data.accounts.length > 0) {
            let html = '';
            let cardsHtml = '';
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
                cardsHtml += `
                    <div class="dash-mini-card">
                        <div class="dash-mini-card-left">
                            <span class="dash-mini-card-title">${acc.full_name}</span>
                            <span class="dash-mini-card-sub">@${acc.username}</span>
                        </div>
                        <div class="dash-mini-card-right">
                            <button class="btn-icon" onclick="approveAccount(${acc.id})" title="Approve">
                                <span class="material-icons">check_circle</span>
                            </button>
                        </div>
                    </div>
                `;
            });
            tbody.innerHTML = html;
            if (cardsContainer) cardsContainer.innerHTML = cardsHtml;
        } else {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No pending accounts</td></tr>';
            if (cardsContainer) cardsContainer.innerHTML = '<div class="dash-empty-state"><span class="material-icons">person_add</span><p>No pending accounts</p></div>';
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
