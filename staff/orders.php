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
    <div class="content-header" style="position: relative; z-index: 200;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Manage Orders</h1>
            <div class="bulk-actions">
                <button class="btn-bulk btn-bulk-success" onclick="confirmAllPending()" title="Confirm all pending orders">
                    <span class="material-icons">done_all</span>
                    Confirm All Pending
                </button>

                <!-- Assign Rider dropdown -->
                <div class="bulk-dropdown" id="assign-bulk-dropdown">
                    <button class="btn-bulk btn-bulk-primary" onclick="toggleBulkDropdown('assign-bulk-dropdown')" title="Assign riders to confirmed delivery orders">
                        <span class="material-icons">delivery_dining</span>
                        Assign Rider
                        <span class="material-icons bulk-dropdown-arrow">expand_more</span>
                    </button>
                    <div class="bulk-dropdown-menu">
                        <button class="bulk-dropdown-item" onclick="autoAssignRiders(); closeBulkDropdown('assign-bulk-dropdown')">
                            <span class="material-icons">auto_awesome</span>
                            Auto Assign All
                        </button>
                        <button class="bulk-dropdown-item" onclick="assignSpecificRider(); closeBulkDropdown('assign-bulk-dropdown')">
                            <span class="material-icons">person_pin</span>
                            Assign to Specific Rider
                        </button>
                    </div>
                </div>

                <!-- Cancel Orders dropdown -->
                <div class="bulk-dropdown" id="cancel-bulk-dropdown">
                    <button class="btn-bulk btn-bulk-danger" onclick="toggleBulkDropdown('cancel-bulk-dropdown')" title="Cancel orders by status">
                        <span class="material-icons">cancel</span>
                        Cancel Orders
                        <span class="material-icons bulk-dropdown-arrow">expand_more</span>
                    </button>
                    <div class="bulk-dropdown-menu">
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('pending'); closeBulkDropdown('cancel-bulk-dropdown')">
                            Cancel All Pending
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('confirmed'); closeBulkDropdown('cancel-bulk-dropdown')">
                            Cancel All Confirmed
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('assigned'); closeBulkDropdown('cancel-bulk-dropdown')">
                            Cancel All Assigned
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('reassign_requested'); closeBulkDropdown('cancel-bulk-dropdown')">
                            Cancel All Reassign Requested
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('on_delivery'); closeBulkDropdown('cancel-bulk-dropdown')">
                            Cancel All On Delivery
                        </button>
                    </div>
                </div>
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
                <button class="filter-btn" data-status="reassign_requested">Reassign Requested</button>
                <button class="filter-btn" data-status="on_delivery">On Delivery</button>
                <button class="filter-btn" data-status="delivered">Delivered</button>
                <button class="filter-btn" data-status="cancelled">Cancelled</button>
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
                        <th>Rider</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr><td colspan="9" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
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

<!-- Bulk Assign to Specific Rider Modal -->
<div class="modal-overlay" id="bulk-assign-rider-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Assign to Specific Rider</h3>
            <button class="modal-close" onclick="closeModal('bulk-assign-rider-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="bulk-assign-rider-form">
            <div class="modal-body">
                <div style="background: rgba(30, 136, 229, 0.08); border: 1px solid rgba(30, 136, 229, 0.25); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--primary); margin-bottom: 4px;">
                        <span class="material-icons" style="font-size: 18px;">delivery_dining</span>
                        Bulk Assignment
                    </div>
                    <div id="bulk-assign-count-text" style="font-size: 13px; color: var(--text-secondary);"></div>
                </div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Select Rider</label>
                <input type="hidden" id="bulk-rider-select" required>
                <div class="custom-select-wrapper" id="bulk-rider-wrapper">
                    <div class="custom-select-trigger" id="bulk-rider-trigger">
                        <span class="selected-text placeholder">Select a rider...</span>
                        <span class="material-icons arrow">expand_more</span>
                    </div>
                    <div class="custom-select-options" id="bulk-rider-options">
                        <div class="custom-select-option" data-value="">Loading...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('bulk-assign-rider-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px; vertical-align: middle;">person_pin</span>
                    Assign All
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Bulk Action Dropdowns */
.bulk-dropdown {
    position: relative;
    display: inline-flex;
}

.bulk-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    background: var(--surface-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-lg);
    min-width: 230px;
    z-index: 500;
    overflow: hidden;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    animation: bulkDropFadeIn 0.15s ease;
}

@keyframes bulkDropFadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bulk-dropdown.open .bulk-dropdown-menu {
    display: block;
}

.bulk-dropdown-arrow {
    font-size: 18px !important;
    margin-left: -4px;
    transition: transform 0.2s ease;
    position: relative;
    z-index: 1;
}

.bulk-dropdown.open .bulk-dropdown-arrow {
    transform: rotate(180deg);
}

.bulk-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 11px 16px;
    border: none;
    background: transparent;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
    text-align: left;
    transition: background 0.15s ease;
    white-space: nowrap;
}

.bulk-dropdown-item:hover {
    background: rgba(21, 101, 192, 0.07);
}

.bulk-dropdown-item .material-icons {
    font-size: 18px;
    color: var(--text-secondary);
}

.bulk-dropdown-item.item-danger {
    color: var(--danger);
}

.bulk-dropdown-item.item-danger:hover {
    background: rgba(239, 83, 80, 0.07);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
