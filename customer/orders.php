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
/* Desktop/Mobile filter bar visibility */
@media (max-width: 768px) {
    .filter-bar-desktop { display: none !important; }
    .filter-bar-mobile  { display: block !important; }
}
@media (min-width: 769px) {
    .filter-bar-desktop { display: block !important; }
    .filter-bar-mobile  { display: none !important; }
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
    
    <!-- Orders Table -->
    <div class="glass-card">
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
