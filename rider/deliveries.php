<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DELIVERIES PAGE
 * ============================================================================
 * 
 * Purpose: Manage active deliveries (on delivery status)
 * Role: RIDER
 * 
 * Features:
 * - View current delivery details
 * - Update delivery status (on_delivery → delivered)
 * - View customer contact info
 * - View delivery address
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "My Deliveries";
$page_css = "deliveries.css";
$page_js = "deliveries.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<style>
/* Delivery card styles */
.delivery-card {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    background: var(--surface-card);
    transition: var(--transition);
    display: flex;
    gap: 14px;
    align-items: flex-start;
}
.delivery-card:hover {
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}
.delivery-card.sortable-ghost { opacity: 0.4; }
.delivery-card.sortable-drag  { box-shadow: var(--shadow-lg); opacity: 1; }
.drag-handle {
    cursor: move;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    padding-top: 2px;
    flex-shrink: 0;
}
.drag-handle:hover { color: var(--primary); }
.delivery-card-inner { flex: 1; min-width: 0; }
.delivery-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}
.delivery-card-body {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}
.delivery-info-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.delivery-info-item .material-icons {
    color: var(--primary);
    font-size: 20px;
    margin-top: 2px;
    flex-shrink: 0;
}
.delivery-info-label {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-bottom: 2px;
}
.delivery-info-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
}
.delivery-card-footer {
    display: flex;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}
/* Desktop/Mobile filter bar visibility */
@media (max-width: 768px) {
    .filter-bar-desktop { display: none !important; }
    .filter-bar-mobile  { display: block !important; }
    .delivery-card-body { grid-template-columns: 1fr 1fr; }
    .delivery-card-footer .btn { flex: 1; justify-content: center; }
}
@media (min-width: 769px) {
    .filter-bar-desktop { display: block !important; }
    .filter-bar-mobile  { display: none !important; }
}
.pagination-controls-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    border-top: 1px solid var(--border);
}
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}
.page-info {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    padding: 0 8px;
    min-width: 100px;
    text-align: center;
}
/* Area group header */
.area-group-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    margin-top: 8px;
    margin-bottom: 4px;
}
.area-group-header .material-icons { color: var(--primary); font-size: 20px; }
.area-group-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--primary);
    flex: 1;
}
.area-group-count {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--border);
    border-radius: 999px;
    padding: 2px 10px;
}
</style>

<main class="main-content">
    <div class="content-header" style="position: relative; z-index: 200;">
        <h1 class="content-title">My Deliveries</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>My Deliveries</span>
        </p>
    </div>

    <!-- Desktop Sort Bar -->
    <div class="glass-card filter-bar-desktop" style="margin-bottom: 24px;" id="sort-bar-desktop">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">sort</span>
                    Sort by:
                </div>
                <button class="filter-btn active" data-sort="priority">Priority</button>
                <button class="filter-btn" data-sort="nearest">Nearest</button>
                <button class="filter-btn" data-sort="group_area">Group by Area</button>
                <button class="filter-btn" data-sort="customer">Customer Name</button>
                <button class="filter-btn" data-sort="amount_asc">Amount ↑</button>
                <button class="filter-btn" data-sort="amount_desc">Amount ↓</button>
            </div>
        </div>
    </div>

    <!-- Mobile Controls Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px;">
        <div style="padding: 16px; display: flex; flex-direction: column; gap: 12px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-sort-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">sort</span>
                    <span class="selected-text">Priority</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-sort-options">
                    <div class="custom-select-option selected" data-sort="priority">Priority</div>
                    <div class="custom-select-option" data-sort="nearest">Nearest</div>
                    <div class="custom-select-option" data-sort="group_area">Group by Area</div>
                    <div class="custom-select-option" data-sort="customer">Customer Name</div>
                    <div class="custom-select-option" data-sort="amount_asc">Amount ↑</div>
                    <div class="custom-select-option" data-sort="amount_desc">Amount ↓</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deliveries Cards -->
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0;">Deliveries (<span id="delivery-count">0</span>)</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;" id="drag-hint" style="display:none;">
                <span class="material-icons" style="font-size: 16px; vertical-align: middle;">drag_indicator</span>
                Drag to reorder priority
            </p>
        </div>
        <div id="deliveries-container" style="display: grid; gap: 16px; padding: 4px 0;">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-controls-wrapper" id="deliveries-pagination" style="display: none;">
            <div class="pagination-controls">
                <button class="btn-icon" onclick="prevDeliveriesPage()" id="del-prev-btn" title="Previous">
                    <span class="material-icons">chevron_left</span>
                </button>
                <span class="page-info" id="del-page-info">Page 1 of 1</span>
                <button class="btn-icon" onclick="nextDeliveriesPage()" id="del-next-btn" title="Next">
                    <span class="material-icons">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
