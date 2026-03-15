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
 * - Delivery statistics (pending, on delivery, completed, today)
 * - Availability toggle
 * - Quick actions
 * - Active deliveries preview
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Dashboard";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

// Fetch current availability status
$rider_data = db_fetch("SELECT is_available FROM users WHERE id = ?", [$_SESSION['user_id']]);
$is_available = $rider_data ? (int)$rider_data['is_available'] : 1;

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
            <p>Here are your deliveries for today. Ride safe!</p>
        </div>
        <div class="dash-welcome-availability">
            <span style="font-size: 0.8rem; font-weight: 600; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px;">Availability</span>
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px;">
                <label class="toggle-switch">
                    <input type="checkbox" id="availability-toggle">
                    <span class="toggle-slider"></span>
                </label>
                <span id="availability-label" style="font-weight: 700; color: #fff;">Available</span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="dash-stats-grid">
        <div class="dash-stat-card dash-stat-warning" onclick="window.location.href='assigned_deliveries.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">assignment</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="pending-deliveries">0</span>
                <span class="dash-stat-label">Pending Deliveries</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>

        <div class="dash-stat-card dash-stat-info" onclick="window.location.href='deliveries.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">local_shipping</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="on-delivery">0</span>
                <span class="dash-stat-label">On Delivery</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>

        <div class="dash-stat-card dash-stat-success" onclick="window.location.href='delivery_history.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">done_all</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="completed-deliveries">0</span>
                <span class="dash-stat-label">Completed</span>
            </div>
            <span class="material-icons dash-stat-arrow">arrow_forward</span>
        </div>

        <div class="dash-stat-card dash-stat-primary" onclick="window.location.href='delivery_history.php'">
            <div class="dash-stat-icon">
                <span class="material-icons">today</span>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-value" id="today-deliveries">0</span>
                <span class="dash-stat-label">Today's Deliveries</span>
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
        <div class="dash-actions-grid dash-actions-grid-4">
            <a href="assigned_deliveries.php" class="dash-action-card">
                <div class="dash-action-icon warning"><span class="material-icons">assignment</span></div>
                <span class="dash-action-label">Assigned Deliveries</span>
            </a>
            <a href="deliveries.php" class="dash-action-card">
                <div class="dash-action-icon info"><span class="material-icons">local_shipping</span></div>
                <span class="dash-action-label">Active Deliveries</span>
            </a>
            <a href="delivery_history.php" class="dash-action-card">
                <div class="dash-action-icon success"><span class="material-icons">history</span></div>
                <span class="dash-action-label">Delivery History</span>
            </a>
            <a href="settings.php" class="dash-action-card">
                <div class="dash-action-icon neutral"><span class="material-icons">settings</span></div>
                <span class="dash-action-label">Settings</span>
            </a>
        </div>
    </div>

    <!-- Active Deliveries Panel -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="material-icons">local_shipping</span>
                <h3>Active Deliveries</h3>
            </div>
            <a href="deliveries.php" class="dash-panel-link">View All <span class="material-icons">arrow_forward</span></a>
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
                                <th>Address</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="active-deliveries-tbody">
                            <tr><td colspan="5" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile/Tablet cards -->
            <div class="dash-panel-card-view" id="active-deliveries-cards">
                <div class="spinner" style="margin: 20px auto;"></div>
            </div>
        </div>
    </div>
</main>

<style>
/* ============================================================================
   RIDER DASHBOARD — Layout
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

.dash-welcome-availability {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 14px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
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

.dash-stat-primary::before  { background: var(--primary); }
.dash-stat-warning::before  { background: #FF9800; }
.dash-stat-info::before     { background: #29B6F6; }
.dash-stat-success::before  { background: #66BB6A; }

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

.dash-stat-icon .material-icons { font-size: 24px; }

.dash-stat-primary .dash-stat-icon  { background: rgba(21, 101, 192, 0.1); color: var(--primary); }
.dash-stat-warning .dash-stat-icon  { background: rgba(255, 152, 0, 0.1); color: #FF9800; }
.dash-stat-info .dash-stat-icon     { background: rgba(41, 182, 246, 0.1); color: #29B6F6; }
.dash-stat-success .dash-stat-icon  { background: rgba(102, 187, 106, 0.1); color: #66BB6A; }

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
.dash-actions-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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

.dash-action-icon .material-icons { font-size: 24px; }

.dash-action-icon.primary { background: rgba(21, 101, 192, 0.1); color: var(--primary); }
.dash-action-icon.info    { background: rgba(41, 182, 246, 0.1); color: #29B6F6; }
.dash-action-icon.success { background: rgba(102, 187, 106, 0.1); color: #66BB6A; }
.dash-action-icon.warning { background: rgba(255, 167, 38, 0.1); color: #FFA726; }
.dash-action-icon.neutral { background: rgba(120, 144, 156, 0.1); color: #78909C; }

.dash-action-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
    line-height: 1.3;
}

/* Bottom Panel */
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

.dash-panel-link:hover { gap: 8px; }

.dash-panel-link .material-icons { font-size: 16px; }

.dash-panel-body { padding: 0; }

.dash-panel-table-view { display: block; }
.dash-panel-card-view { display: none; }

/* Mini cards for mobile panel view */
.dash-mini-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.dash-mini-card:last-child { border-bottom: none; }
.dash-mini-card:hover { background: var(--hover, var(--surface)); }

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

@media (max-width: 1024px) {
    .dash-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dash-actions-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .dash-panel-table-view { display: none; }
    .dash-panel-card-view { display: block; }
}

@media (max-width: 768px) {
    .dash-welcome {
        padding: 22px 20px;
        gap: 16px;
    }

    .dash-welcome-text h2 { font-size: 1.25rem; }
    .dash-welcome-text p { font-size: 0.85rem; }

    .dash-stat-card { padding: 16px; }
    .dash-stat-value { font-size: 1.5rem; }
    .dash-stat-arrow { display: none; }

    .dash-actions-grid-4 {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .dash-action-card { padding: 16px 10px; }
    .dash-action-icon { width: 40px; height: 40px; }
    .dash-action-icon .material-icons { font-size: 22px; }
    .dash-action-label { font-size: 0.75rem; }
}

@media (max-width: 480px) {
    .dash-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .dash-stat-card { padding: 14px 12px; gap: 10px; }
    .dash-stat-icon { width: 40px; height: 40px; border-radius: 10px; }
    .dash-stat-icon .material-icons { font-size: 20px; }
    .dash-stat-value { font-size: 1.3rem; }
    .dash-stat-label { font-size: 0.7rem; }

    .dash-welcome { padding: 18px 16px; border-radius: 12px; flex-wrap: wrap; }
    .dash-welcome-text h2 { font-size: 1.1rem; }
    .dash-welcome-availability { width: 100%; flex-direction: row; justify-content: space-between; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setGreeting();
    loadDashboardStats();
    initAvailabilityToggle();
    loadActiveDeliveries();
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
            animateValue('pending-deliveries', stats.pending_deliveries || 0);
            animateValue('on-delivery', stats.on_delivery || 0);
            animateValue('completed-deliveries', stats.completed_deliveries || 0);
            animateValue('today-deliveries', stats.today_deliveries || 0);
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

async function initAvailabilityToggle() {
    const toggle = document.getElementById('availability-toggle');
    const label = document.getElementById('availability-label');

    try {
        const response = await fetch('../api/riders/statistics.php');
        const data = await response.json();
        const isAvailable = <?php echo $is_available ? 'true' : 'false'; ?>;
        toggle.checked = isAvailable;
        updateAvailabilityLabel(isAvailable);
    } catch (error) {
        console.error('Failed to load availability:', error);
    }

    toggle.addEventListener('change', async function() {
        const isAvailable = this.checked;

        try {
            const response = await fetch('../api/riders/toggle_availability.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    rider_id: <?php echo $_SESSION['user_id']; ?>,
                    is_available: isAvailable ? 1 : 0,
                    csrf_token: getCSRFToken()
                })
            });

            const data = await response.json();

            if (data.success) {
                updateAvailabilityLabel(isAvailable);
                showToast(isAvailable ? 'You are now available' : 'You are now unavailable', 'success');
            } else {
                this.checked = !isAvailable;
                showToast('Failed to update availability', 'error');
            }
        } catch (error) {
            this.checked = !isAvailable;
            showToast('An error occurred', 'error');
        }
    });
}

function updateAvailabilityLabel(isAvailable) {
    const label = document.getElementById('availability-label');
    label.textContent = isAvailable ? 'Available' : 'Unavailable';
    label.style.color = isAvailable ? '#a5d6a7' : '#ef9a9a';
}

async function loadActiveDeliveries() {
    try {
        const response = await fetch('../api/orders/list.php?limit=5');
        const data = await response.json();

        if (data.success && data.orders.length > 0) {
            renderActiveDeliveries(data.orders);
        } else {
            showEmptyDeliveries();
        }
    } catch (error) {
        console.error('Failed to load active deliveries:', error);
        showEmptyDeliveries();
    }
}

function renderActiveDeliveries(orders) {
    const tbody = document.getElementById('active-deliveries-tbody');
    const cardsContainer = document.getElementById('active-deliveries-cards');

    let html = '';
    let cardsHtml = '';

    orders.forEach(order => {
        const statusLabel = getStatusLabel(order.status);
        const address = order.delivery_address ? truncate(order.delivery_address, 40) : 'Pickup';

        html += `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name}</td>
                <td>${address}</td>
                <td><span class="badge badge-${order.status}">${statusLabel}</span></td>
                <td>
                    <button class="btn-icon" onclick="window.location.href='deliveries.php?id=${order.id}'" title="View Details">
                        <span class="material-icons">visibility</span>
                    </button>
                </td>
            </tr>
        `;

        cardsHtml += `
            <div class="dash-mini-card">
                <div class="dash-mini-card-left">
                    <span class="dash-mini-card-title">#${order.id} — ${order.customer_name}</span>
                    <span class="dash-mini-card-sub">${address}</span>
                </div>
                <div class="dash-mini-card-right">
                    <span class="badge badge-${order.status}">${statusLabel}</span>
                    <button class="btn-icon" onclick="window.location.href='deliveries.php?id=${order.id}'" title="View Details">
                        <span class="material-icons">visibility</span>
                    </button>
                </div>
            </div>
        `;
    });

    tbody.innerHTML = html;
    if (cardsContainer) cardsContainer.innerHTML = cardsHtml;
}

function showEmptyDeliveries() {
    const tbody = document.getElementById('active-deliveries-tbody');
    const cardsContainer = document.getElementById('active-deliveries-cards');

    tbody.innerHTML = `
        <tr>
            <td colspan="5">
                <div class="dash-empty-state">
                    <span class="material-icons">inbox</span>
                    <p>No active deliveries</p>
                </div>
            </td>
        </tr>
    `;
    if (cardsContainer) {
        cardsContainer.innerHTML = '<div class="dash-empty-state"><span class="material-icons">inbox</span><p>No active deliveries</p></div>';
    }
}

function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}

function getStatusLabel(status) {
    const labels = {
        'assigned': 'Assigned',
        'on_delivery': 'On Delivery',
        'delivered': 'Delivered',
        'accepted': 'Completed'
    };
    return labels[status] || status;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
