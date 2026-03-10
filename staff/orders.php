<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF/ADMIN ORDERS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Manage all orders (view, confirm, assign riders, cancel)
 * Role: STAFF, ADMIN
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Manage Orders";
$page_css = "main.css";
$page_js = "orders.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Manage Orders</h1>
            <div class="bulk-actions">
                <button class="btn-bulk btn-bulk-success" onclick="confirmAllPending()" title="Confirm all pending orders">
                    <span class="material-icons">done_all</span>
                    Confirm All Pending
                </button>
                <button class="btn-bulk btn-bulk-primary" onclick="autoAssignRiders()" title="Auto-assign riders to confirmed orders">
                    <span class="material-icons">delivery_dining</span>
                    Auto Assign Riders
                </button>
                <button class="btn-bulk btn-bulk-danger" onclick="cancelAllPending()" title="Cancel all pending orders">
                    <span class="material-icons">cancel</span>
                    Cancel All Pending
                </button>
            </div>
        </div>
    </div>
    
    <!-- Desktop Filter Bar -->
    <div class="glass-card filter-bar-desktop" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">filter_list</span>
                    Filter:
                </div>
                <button class="filter-btn active" data-status="">All</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="confirmed">Confirmed</button>
                <button class="filter-btn" data-status="assigned">Assigned</button>
                <button class="filter-btn" data-status="on_delivery">On Delivery</button>
                <button class="filter-btn" data-status="delivered">Delivered</button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Filter Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-filter-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">filter_list</span>
                    <span class="selected-text">All</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-filter-options">
                    <div class="custom-select-option selected" data-status="">All</div>
                    <div class="custom-select-option" data-status="pending">Pending</div>
                    <div class="custom-select-option" data-status="confirmed">Confirmed</div>
                    <div class="custom-select-option" data-status="assigned">Assigned</div>
                    <div class="custom-select-option" data-status="on_delivery">On Delivery</div>
                    <div class="custom-select-option" data-status="delivered">Delivered</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table" id="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr><td colspan="8" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div class="modal-overlay" id="order-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="modal-close" onclick="closeModal('order-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="modal-body" id="order-details"></div>
        <div class="modal-footer" id="order-actions"></div>
    </div>
</div>

<!-- Assign Rider Modal -->
<div class="modal-overlay" id="assign-rider-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Assign Rider</h3>
            <button class="modal-close" onclick="closeModal('assign-rider-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="assign-rider-form">
            <div class="modal-body">
                <input type="hidden" id="assign-order-id">
                <div id="reassign-reason-note" style="display: none;"></div>
                <label for="rider-select" style="display: block; margin-bottom: 8px; font-weight: 600;">Select Rider</label>
                <input type="hidden" id="rider-select" required>
                <div class="custom-select-wrapper" id="rider-wrapper">
                    <div class="custom-select-trigger" id="rider-trigger">
                        <span class="selected-text placeholder">Loading...</span>
                        <span class="material-icons arrow">expand_more</span>
                    </div>
                    <div class="custom-select-options" id="rider-options">
                        <div class="custom-select-option" data-value="">Loading...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-rider-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
