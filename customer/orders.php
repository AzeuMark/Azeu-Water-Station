<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER ORDERS PAGE
 * ============================================================================
 * 
 * Purpose: View and manage customer orders
 * Role: CUSTOMER
 * 
 * Features:
 * - List all orders with filtering by status
 * - View order details
 * - Cancel pending orders
 * - Confirm delivery
 * - View receipt
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "My Orders";
$page_js = "orders.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<style>
/* Sortable Table Headers */
.sortable-th {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
    transition: background 0.2s;
}
.sortable-th:hover {
    background: var(--primary);
    color: #fff;
}
.sortable-th .sort-icon {
    font-size: 14px;
    vertical-align: middle;
    margin-left: 4px;
    opacity: 0.5;
    transition: opacity 0.2s;
}
.sortable-th:hover .sort-icon,
.sortable-th.th-sorted .sort-icon {
    opacity: 1;
}
.sortable-th.th-sorted {
    background: var(--primary);
    color: #fff;
}

/* ============================================================================
   RESPONSIVE ORDER CARDS — Mobile/Tablet View
   ============================================================================ */

/* Show/hide logic */
.orders-card-view {
    display: none;
}

.orders-table-view {
    display: block;
}

@media (max-width: 1024px) {
    .orders-card-view {
        display: block;
    }
    .orders-table-view {
        display: none;
    }
}

/* Desktop/Mobile filter bar visibility */
@media (max-width: 1024px) {
    .filter-bar-desktop { display: none !important; }
    .filter-bar-mobile  { display: block !important; }
}
@media (min-width: 1025px) {
    .filter-bar-desktop { display: block !important; }
    .filter-bar-mobile  { display: none !important; }
}

/* Card grid */
.order-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 600px) and (max-width: 1024px) {
    .order-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Individual card */
.order-card {
    background: var(--surface-card);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.order-card:hover {
    box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Card header */
.order-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.order-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}

.order-card-header-left .material-icons {
    font-size: 20px;
    color: var(--primary);
}

.order-card-actions {
    display: flex;
    gap: 4px;
}

.order-card-actions .btn-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
}

.order-card-actions .btn-icon .material-icons {
    font-size: 18px;
}

/* Card data rows */
.order-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.order-card-row:last-child {
    border-bottom: none;
}

.order-card-label {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-shrink: 0;
}

.order-card-label .material-icons {
    font-size: 16px;
}

.order-card-value {
    font-weight: 600;
    color: var(--text-primary);
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

.order-card-value.total-highlight {
    color: var(--primary);
    font-size: 15px;
    font-weight: 700;
}

/* Badge in card */
.order-card-value .badge {
    font-size: 12px;
    padding: 3px 10px;
}

/* Empty state for cards */
.order-cards-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
    background: var(--surface-card);
    border-radius: 16px;
    border: 1px solid var(--border);
}

.order-cards-empty .material-icons {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.order-cards-empty p {
    font-size: 15px;
    font-weight: 500;
}

/* ============================================================================
   ORDER DETAILS MODAL — Premium Card-Based Design
   ============================================================================ */
.odm-header-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    background: var(--surface);
    border-radius: 12px;
    margin-bottom: 16px;
}

.odm-order-id {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--primary);
}

.odm-order-id .material-icons {
    font-size: 22px;
}

.odm-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}

.odm-info-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--surface-card);
}

.odm-info-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.odm-info-icon .material-icons {
    font-size: 18px;
}

.odm-info-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.odm-info-label {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    font-weight: 600;
}

.odm-info-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.odm-detail-rows {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.odm-detail-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--surface);
    border-radius: 8px;
    font-size: 0.875rem;
}

.odm-detail-label {
    font-weight: 600;
    color: var(--text-secondary);
    min-width: 52px;
}

.odm-detail-value {
    color: var(--text-primary);
    flex: 1;
    min-width: 0;
    word-break: break-word;
}

.odm-section {
    margin-bottom: 16px;
}

.odm-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 0.875rem;
    color: var(--text-primary);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.odm-section-title .material-icons {
    font-size: 18px;
    color: var(--primary);
}

.odm-items-list {
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
}

.odm-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
}

.odm-item:not(:last-child) {
    border-bottom: 1px solid var(--border);
}

.odm-item-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.odm-item-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text-primary);
}

.odm-item-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.odm-item-amount {
    font-weight: 700;
    font-size: 0.875rem;
    color: var(--text-primary);
    white-space: nowrap;
    margin-left: 12px;
}

.odm-totals {
    background: var(--surface);
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
}

.odm-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.odm-total-row:not(:last-child) {
    border-bottom: 1px dashed var(--border);
    padding-bottom: 8px;
    margin-bottom: 8px;
}

.odm-grand-total {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--primary);
    padding-top: 6px;
}

.odm-cancel-reason {
    padding: 12px 16px;
    background: rgba(239,83,80,0.06);
    border: 1px solid rgba(239,83,80,0.25);
    border-radius: 10px;
}

.odm-cancel-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--danger);
    margin-bottom: 6px;
}

.odm-cancel-title .material-icons {
    font-size: 18px;
}

.odm-cancel-reason p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-secondary);
    line-height: 1.5;
}

@media (max-width: 480px) {
    .odm-info-grid {
        grid-template-columns: 1fr;
    }
}

/* Prevent glass-card hover/transition effects on table container */
.glass-card.orders-table-view {
    transition: box-shadow 0.3s ease;
}
.glass-card.orders-table-view:hover {
    transform: none;
}

/* Pagination Controls - Bottom Center */
.pagination-controls-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    border-top: 1px solid var(--border);
    background: var(--surface);
    border-radius: 0 0 var(--radius) var(--radius);
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    white-space: nowrap;
}

.page-info {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    padding: 0 8px;
    min-width: 100px;
    text-align: center;
}

/* Desktop pagination - hidden by default, shown via JS class */
#pagination-wrapper {
    display: none;
}
#pagination-wrapper.active {
    display: flex !important;
}

/* Mobile pagination - hidden by default, shown via JS class */
#pagination-wrapper-mobile {
    display: none;
}
#pagination-wrapper-mobile.active {
    display: flex !important;
}

@media (max-width: 768px) {
    .pagination-controls-wrapper {
        padding: 16px;
    }

    .page-info {
        font-size: 13px;
        min-width: 90px;
    }
}
</style>

<main class="main-content">
    <div class="content-header" style="position: relative; z-index: 200;">
        <h1 class="content-title">My Orders</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>My Orders</span>
        </p>
    </div>
    
    <!-- Desktop Filter Bar -->
    <div class="glass-card filter-bar-desktop" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">filter_list</span>
                    Filter:
                </div>
                <button class="filter-btn active" data-status="">All Orders</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="confirmed">Confirmed</button>
                <button class="filter-btn" data-status="assigned">Assigned</button>
                <button class="filter-btn" data-status="on_delivery">On Delivery</button>
                <button class="filter-btn" data-status="delivered">Delivered</button>
                <button class="filter-btn" data-status="ready_for_pickup">Ready for Pickup</button>
                <button class="filter-btn" data-status="picked_up">Picked Up</button>
                <button class="filter-btn" data-status="cancelled">Cancelled</button>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-filter-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">filter_list</span>
                    <span class="selected-text">All Orders</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-filter-options">
                    <div class="custom-select-option selected" data-status="">All Orders</div>
                    <div class="custom-select-option" data-status="pending">Pending</div>
                    <div class="custom-select-option" data-status="confirmed">Confirmed</div>
                    <div class="custom-select-option" data-status="assigned">Assigned</div>
                    <div class="custom-select-option" data-status="on_delivery">On Delivery</div>
                    <div class="custom-select-option" data-status="delivered">Delivered</div>
                    <div class="custom-select-option" data-status="ready_for_pickup">Ready for Pickup</div>
                    <div class="custom-select-option" data-status="picked_up">Picked Up</div>
                    <div class="custom-select-option" data-status="cancelled">Cancelled</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Desktop Table View -->
    <div class="glass-card orders-table-view">
        <div class="data-table-wrapper">
            <table class="data-table" id="orders-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th class="sortable-th" data-col="id">Order ID <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="order_date">Date <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="delivery_type">Type <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="payment_type">Payment <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="total_amount">Total <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="status">Status <span class="sort-icon material-icons">unfold_more</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="pagination-controls-wrapper" id="pagination-wrapper">
            <div class="pagination-controls">
                <button class="btn-icon" onclick="previousPage()" id="prev-btn" title="Previous Page">
                    <span class="material-icons">chevron_left</span>
                </button>
                <span class="page-info" id="page-info">Page 1 of 1</span>
                <button class="btn-icon" onclick="nextPage()" id="next-btn" title="Next Page">
                    <span class="material-icons">chevron_right</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile/Tablet Card View -->
    <div class="orders-card-view" id="orders-cards">
        <div class="spinner" style="margin: 40px auto;"></div>
    </div>

    <!-- Mobile Pagination -->
    <div id="pagination-wrapper-mobile" style="justify-content: center; align-items: center; padding: 16px 20px; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius); margin-top: 16px;">
        <div class="pagination-controls">
            <button class="btn-icon" onclick="previousPage()" id="prev-btn-mobile" title="Previous Page">
                <span class="material-icons">chevron_left</span>
            </button>
            <span class="page-info" id="page-info-mobile">Page 1 of 1</span>
            <button class="btn-icon" onclick="nextPage()" id="next-btn-mobile" title="Next Page">
                <span class="material-icons">chevron_right</span>
            </button>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div class="modal-overlay" id="order-details-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="modal-close" onclick="closeModal('order-details-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="modal-body" id="order-details-content">
            <!-- Content loaded dynamically -->
        </div>
        <div class="modal-footer" id="order-details-actions">
            <!-- Actions loaded dynamically -->
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
