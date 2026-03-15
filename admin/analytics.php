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
        <h1 class="content-title">Analytics</h1>
    </div>
    
    <!-- Desktop Period Filter -->
    <div class="glass-card analytics-filter-desktop" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                <span class="material-icons" style="font-size: 20px;">date_range</span>
                Period:
            </div>
            <button class="filter-btn active" data-period="month">This Month</button>
            <button class="filter-btn" data-period="week">This Week</button>
            <button class="filter-btn" data-period="year">This Year</button>
        </div>
    </div>
    
    <!-- Mobile Period Filter -->
    <div class="glass-card analytics-filter-mobile" style="margin-bottom: 24px;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-period-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">date_range</span>
                    <span class="selected-text">This Month</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-period-options">
                    <div class="custom-select-option selected" data-period="month">This Month</div>
                    <div class="custom-select-option" data-period="week">This Week</div>
                    <div class="custom-select-option" data-period="year">This Year</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Stats -->
    <div class="analytics-stats-grid">
        <div class="analytics-stat-card analytics-stat-green">
            <div class="analytics-stat-icon"><span class="material-icons">payments</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="total-revenue">₱0</span>
                <span class="analytics-stat-label">Total Revenue</span>
            </div>
        </div>
        <div class="analytics-stat-card analytics-stat-blue">
            <div class="analytics-stat-icon"><span class="material-icons">shopping_cart</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="total-orders">0</span>
                <span class="analytics-stat-label">Total Orders</span>
            </div>
        </div>
        <div class="analytics-stat-card analytics-stat-cyan">
            <div class="analytics-stat-icon"><span class="material-icons">trending_up</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="avg-order-value">₱0</span>
                <span class="analytics-stat-label">Avg Order Value</span>
            </div>
        </div>
        <div class="analytics-stat-card analytics-stat-orange">
            <div class="analytics-stat-icon"><span class="material-icons">local_shipping</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="delivery-fees">₱0</span>
                <span class="analytics-stat-label">Delivery Fees</span>
            </div>
        </div>
    </div>

    <!-- Order Stats -->
    <div class="analytics-stats-grid">
        <div class="analytics-stat-card analytics-stat-teal">
            <div class="analytics-stat-icon"><span class="material-icons">check_circle</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="completed-orders">0</span>
                <span class="analytics-stat-label">Completed Orders</span>
            </div>
        </div>
        <div class="analytics-stat-card analytics-stat-purple">
            <div class="analytics-stat-icon"><span class="material-icons">sync</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="active-orders">0</span>
                <span class="analytics-stat-label">Active Orders</span>
            </div>
        </div>
        <div class="analytics-stat-card analytics-stat-amber">
            <div class="analytics-stat-icon"><span class="material-icons">hourglass_empty</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="pending-orders">0</span>
                <span class="analytics-stat-label">Pending Orders</span>
            </div>
        </div>
        <div class="analytics-stat-card analytics-stat-red">
            <div class="analytics-stat-icon"><span class="material-icons">cancel</span></div>
            <div class="analytics-stat-body">
                <span class="analytics-stat-value" id="cancelled-orders">0</span>
                <span class="analytics-stat-label">Cancelled Orders</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="analytics-charts-grid">
        <div class="analytics-panel">
            <div class="analytics-panel-header">
                <div class="analytics-panel-title">
                    <span class="material-icons">show_chart</span>
                    <h3>Revenue Trend</h3>
                </div>
            </div>
            <div class="analytics-panel-body">
                <canvas id="revenue-chart"></canvas>
            </div>
        </div>
        <div class="analytics-panel">
            <div class="analytics-panel-header">
                <div class="analytics-panel-title">
                    <span class="material-icons">donut_large</span>
                    <h3>Order Status</h3>
                </div>
            </div>
            <div class="analytics-panel-body analytics-chart-doughnut">
                <canvas id="status-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products & Customers -->
    <div class="analytics-lists-grid">
        <div class="analytics-panel">
            <div class="analytics-panel-header">
                <div class="analytics-panel-title">
                    <span class="material-icons">inventory_2</span>
                    <h3>Top Products</h3>
                </div>
            </div>
            <div class="analytics-panel-body analytics-panel-body-list" id="top-products">
                <div style="text-align: center; padding: 20px;"><div class="spinner"></div></div>
            </div>
        </div>
        <div class="analytics-panel">
            <div class="analytics-panel-header">
                <div class="analytics-panel-title">
                    <span class="material-icons">groups</span>
                    <h3>Top Customers</h3>
                </div>
            </div>
            <div class="analytics-panel-body analytics-panel-body-list" id="top-customers">
                <div style="text-align: center; padding: 20px;"><div class="spinner"></div></div>
            </div>
        </div>
    </div>
</main>

<style>
/* ============================================================================
   ANALYTICS — Revamped Layout
   ============================================================================ */

/* Period Filter — Desktop / Mobile toggle */
.analytics-filter-desktop {
    display: block;
}

.analytics-filter-mobile {
    display: none;
    position: relative;
    z-index: 100;
}

/* Custom Select (reuse pattern from appeals/orders) */
.custom-select-wrapper { position: relative; width: 100%; }

.custom-select-trigger {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; background: var(--surface); border: 2px solid var(--border);
    border-radius: 10px; cursor: pointer; transition: all 0.3s ease;
    font-weight: 500; color: var(--text-primary);
}
.custom-select-trigger:hover { border-color: var(--primary); }
.custom-select-trigger.active { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1); }
.custom-select-trigger .arrow { transition: transform 0.3s ease; }
.custom-select-trigger.active .arrow { transform: rotate(180deg); }

.custom-select-options {
    position: absolute; top: calc(100% + 1px); left: 0; right: 0;
    background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.05);
    max-height: 190px; overflow-y: auto; z-index: 1001;
    opacity: 0; visibility: hidden; transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.custom-select-options.active { opacity: 1; visibility: visible; transform: translateY(0); }

.custom-select-option {
    padding: 12px 16px; cursor: pointer; transition: background 0.2s; color: var(--text-primary);
}
.custom-select-option:hover { background: var(--hover); }
.custom-select-option.selected { background: var(--primary); color: white; font-weight: 600; }

/* Stat Cards Grid */
.analytics-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.analytics-stat-card {
    background: var(--surface-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.analytics-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    border-radius: 4px 0 0 4px;
}

.analytics-stat-green::before  { background: #66BB6A; }
.analytics-stat-blue::before   { background: var(--primary); }
.analytics-stat-cyan::before   { background: #29B6F6; }
.analytics-stat-orange::before { background: #FFA726; }
.analytics-stat-teal::before   { background: #26A69A; }
.analytics-stat-purple::before { background: #7E57C2; }
.analytics-stat-amber::before  { background: #FFCA28; }
.analytics-stat-red::before    { background: #EF5350; }

.analytics-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-color: transparent;
}

.analytics-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.analytics-stat-icon .material-icons { font-size: 24px; }

.analytics-stat-green .analytics-stat-icon  { background: rgba(102, 187, 106, 0.1); color: #66BB6A; }
.analytics-stat-blue .analytics-stat-icon   { background: rgba(21, 101, 192, 0.1); color: var(--primary); }
.analytics-stat-cyan .analytics-stat-icon   { background: rgba(41, 182, 246, 0.1); color: #29B6F6; }
.analytics-stat-orange .analytics-stat-icon { background: rgba(255, 167, 38, 0.1); color: #FFA726; }
.analytics-stat-teal .analytics-stat-icon   { background: rgba(38, 166, 154, 0.1); color: #26A69A; }
.analytics-stat-purple .analytics-stat-icon { background: rgba(126, 87, 194, 0.1); color: #7E57C2; }
.analytics-stat-amber .analytics-stat-icon  { background: rgba(255, 202, 40, 0.1); color: #FFCA28; }
.analytics-stat-red .analytics-stat-icon    { background: rgba(239, 83, 80, 0.1); color: #EF5350; }

.analytics-stat-body { flex: 1; min-width: 0; }

.analytics-stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.analytics-stat-label {
    display: block;
    font-size: 0.78rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 3px;
    font-weight: 500;
}

/* Panels (charts + lists) */
.analytics-charts-grid,
.analytics-lists-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.analytics-panel {
    background: var(--surface-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}

.analytics-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.analytics-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.analytics-panel-title .material-icons {
    font-size: 22px;
    color: var(--primary);
}

.analytics-panel-title h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
}

.analytics-panel-body {
    padding: 20px;
}

.analytics-panel-body-list {
    padding: 0;
}

.analytics-chart-doughnut {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 280px;
}

/* Rank List Items */
.analytics-rank-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.analytics-rank-item:last-child {
    border-bottom: none;
}

.analytics-rank-item:hover {
    background: var(--hover, var(--surface));
}

.analytics-rank-number {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
    background: var(--surface);
    color: var(--text-muted);
    border: 1px solid var(--border);
}

.analytics-rank-item:nth-child(1) .analytics-rank-number {
    background: linear-gradient(135deg, #FFD700, #FFA000);
    color: #fff;
    border: none;
}

.analytics-rank-item:nth-child(2) .analytics-rank-number {
    background: linear-gradient(135deg, #B0BEC5, #78909C);
    color: #fff;
    border: none;
}

.analytics-rank-item:nth-child(3) .analytics-rank-number {
    background: linear-gradient(135deg, #BCAAA4, #8D6E63);
    color: #fff;
    border: none;
}

.analytics-rank-info {
    flex: 1;
    min-width: 0;
}

.analytics-rank-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.analytics-rank-sub {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-top: 1px;
}

.analytics-rank-value {
    font-weight: 700;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.analytics-rank-value.revenue { color: var(--primary); }
.analytics-rank-value.spent   { color: #66BB6A; }

/* ============================================================================
   RESPONSIVE
   ============================================================================ */

@media (max-width: 1024px) {
    .analytics-filter-desktop { display: none; }
    .analytics-filter-mobile  { display: block !important; }
    
    .analytics-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .analytics-charts-grid,
    .analytics-lists-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .analytics-stat-card { padding: 16px; }
    .analytics-stat-value { font-size: 1.3rem; }
    
    .analytics-panel-body { padding: 16px; }
    .analytics-chart-doughnut { min-height: 240px; padding: 16px; }
}

@media (max-width: 480px) {
    .analytics-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .analytics-stat-card {
        padding: 14px 12px;
        gap: 10px;
    }
    
    .analytics-stat-icon {
        width: 40px; height: 40px; border-radius: 10px;
    }
    
    .analytics-stat-icon .material-icons { font-size: 20px; }
    .analytics-stat-value { font-size: 1.1rem; }
    .analytics-stat-label { font-size: 0.7rem; }
    
    .analytics-rank-item { padding: 12px 16px; gap: 10px; }
    .analytics-rank-name { font-size: 0.85rem; }
    .analytics-rank-value { font-size: 0.85rem; }
}
</style>

<script>
// Mobile Period Filter Dropdown
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('mobile-period-trigger');
    const options = document.getElementById('mobile-period-options');
    const selectedText = trigger?.querySelector('.selected-text');
    
    if (trigger && options) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            trigger.classList.toggle('active');
            options.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !options.contains(e.target)) {
                trigger.classList.remove('active');
                options.classList.remove('active');
            }
        });
        
        options.addEventListener('click', function(e) {
            const option = e.target.closest('.custom-select-option');
            if (!option) return;
            
            const period = option.dataset.period;
            const text = option.textContent.trim();
            
            options.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');
            selectedText.textContent = text;
            
            trigger.classList.remove('active');
            options.classList.remove('active');
            
            const desktopBtn = document.querySelector(`.filter-btn[data-period="${period}"]`);
            if (desktopBtn) desktopBtn.click();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
