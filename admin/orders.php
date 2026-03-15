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
        <h1 class="content-title">Manage Orders</h1>
    </div>
    
    <!-- Mobile Filter & Actions Bar -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div class="filter-bar-mobile-inner">
            <div class="bulk-actions">
                <button class="btn-bulk btn-bulk-success" onclick="confirmAllPending()" title="Confirm all pending orders">
                    <span class="material-icons">done_all</span>
                    Confirm All Pending
                </button>

                <div class="bulk-dropdown" id="assign-bulk-dropdown-mobile">
                    <button class="btn-bulk btn-bulk-primary" onclick="toggleBulkDropdown('assign-bulk-dropdown-mobile')" title="Assign riders to confirmed delivery orders">
                        <span class="material-icons">delivery_dining</span>
                        Assign Rider
                        <span class="material-icons bulk-dropdown-arrow">expand_more</span>
                    </button>
                    <div class="bulk-dropdown-menu">
                        <button class="bulk-dropdown-item" onclick="autoAssignRiders(); closeBulkDropdown('assign-bulk-dropdown-mobile')">
                            <span class="material-icons">auto_awesome</span>
                            Auto Assign
                        </button>
                        <button class="bulk-dropdown-item" onclick="assignSpecificRider(); closeBulkDropdown('assign-bulk-dropdown-mobile')">
                            <span class="material-icons">person_pin</span>
                            Assign to Rider
                        </button>
                    </div>
                </div>

                <div class="bulk-dropdown" id="cancel-bulk-dropdown-mobile">
                    <button class="btn-bulk btn-bulk-danger" onclick="toggleBulkDropdown('cancel-bulk-dropdown-mobile')" title="Cancel orders by status">
                        <span class="material-icons">cancel</span>
                        Cancel Orders
                        <span class="material-icons bulk-dropdown-arrow">expand_more</span>
                    </button>
                    <div class="bulk-dropdown-menu">
                        <button class="bulk-dropdown-item item-danger" onclick="cancelAllCancellable(); closeBulkDropdown('cancel-bulk-dropdown-mobile')">
                            Cancel All Cancellable
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('pending'); closeBulkDropdown('cancel-bulk-dropdown-mobile')">
                            Cancel All Pending
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('confirmed'); closeBulkDropdown('cancel-bulk-dropdown-mobile')">
                            Cancel All Confirmed
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('assigned'); closeBulkDropdown('cancel-bulk-dropdown-mobile')">
                            Cancel All Assigned
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('reassign_requested'); closeBulkDropdown('cancel-bulk-dropdown-mobile')">
                            Cancel All Reassign Requested
                        </button>
                        <button class="bulk-dropdown-item item-danger" onclick="cancelByStatus('on_delivery'); closeBulkDropdown('cancel-bulk-dropdown-mobile')">
                            Cancel All On Delivery
                        </button>
                    </div>
                </div>
            </div>
            <div class="filter-bar-mobile-divider"></div>
            <div class="filter-bar-mobile-filter">
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
                        <div class="custom-select-option" data-status="ready_for_pickup">Ready for Pickup</div>
                        <div class="custom-select-option" data-status="delivered">Delivered</div>
                        <div class="custom-select-option" data-status="picked_up">Picked Up</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bulk Actions (Desktop) -->
    <div class="bulk-actions bulk-actions-full glass-card" style="margin-bottom: 16px;">
        <button class="btn-bulk btn-bulk-success" onclick="confirmAllPending()" title="Confirm all pending orders">
            <span class="material-icons">done_all</span>
            Confirm All Pending
        </button>

        <div class="bulk-dropdown" id="assign-bulk-dropdown">
            <button class="btn-bulk btn-bulk-primary" onclick="toggleBulkDropdown('assign-bulk-dropdown')" title="Assign riders to confirmed delivery orders">
                <span class="material-icons">delivery_dining</span>
                Assign Rider
                <span class="material-icons bulk-dropdown-arrow">expand_more</span>
            </button>
            <div class="bulk-dropdown-menu">
                <button class="bulk-dropdown-item" onclick="autoAssignRiders(); closeBulkDropdown('assign-bulk-dropdown')">
                    <span class="material-icons">auto_awesome</span>
                    Auto Assign
                </button>
                <button class="bulk-dropdown-item" onclick="assignSpecificRider(); closeBulkDropdown('assign-bulk-dropdown')">
                    <span class="material-icons">person_pin</span>
                    Assign to Rider
                </button>
            </div>
        </div>

        <div class="bulk-dropdown" id="cancel-bulk-dropdown">
            <button class="btn-bulk btn-bulk-danger" onclick="toggleBulkDropdown('cancel-bulk-dropdown')" title="Cancel orders by status">
                <span class="material-icons">cancel</span>
                Cancel Orders
                <span class="material-icons bulk-dropdown-arrow">expand_more</span>
            </button>
            <div class="bulk-dropdown-menu">
                <button class="bulk-dropdown-item item-danger" onclick="cancelAllCancellable(); closeBulkDropdown('cancel-bulk-dropdown')">
                    Cancel All Cancellable
                </button>
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

    <!-- Desktop Table View -->
    <div class="glass-card orders-table-view">
        <!-- Filter Bar -->
        <div class="orders-filter-section">
            <div class="filter-bar">
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
                <button class="filter-btn" data-status="ready_for_pickup">Ready for Pickup</button>
                <button class="filter-btn" data-status="delivered">Delivered</button>
                <button class="filter-btn" data-status="picked_up">Picked Up</button>
                <button class="filter-btn" data-status="cancelled">Cancelled</button>
            </div>
        </div>

        <div class="data-table-wrapper">
            <table class="data-table" id="orders-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th class="sortable-th" data-col="id">ID <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="customer_name">Customer <span class="sort-icon material-icons">unfold_more</span></th>
                        <th>Items</th>
                        <th class="sortable-th" data-col="order_date">Date <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="delivery_type">Type <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="total_amount">Total <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="status">Status <span class="sort-icon material-icons">unfold_more</span></th>
                        <th>Rider</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr><td colspan="10" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls (same pattern as accounts.php) -->
        <div class="pagination-controls-wrapper" id="pagination-wrapper" style="display: none;">
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
    <div id="pagination-wrapper-mobile" style="display: none; justify-content: center; align-items: center; padding: 16px 20px; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius); margin-top: 16px;">
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

<!-- Bulk Assign to Rider Modal -->
<div class="modal-overlay" id="bulk-assign-rider-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Assign to Rider</h3>
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
/* Filter Section inside glass-card — negates parent padding for full-width border */
.orders-filter-section {
    margin: -20px -20px 0 -20px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.orders-filter-section .filter-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Bulk actions full-width */
.bulk-actions-full {
    position: relative;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.bulk-actions-full .btn-bulk {
    flex: 1;
    justify-content: center;
}

.bulk-actions-full .bulk-dropdown {
    flex: 1;
    display: flex;
}

.bulk-actions-full .bulk-dropdown .btn-bulk {
    width: 100%;
    justify-content: center;
}

/* Bulk Action Dropdowns — styled like custom-select-wrapper */
.bulk-dropdown {
    position: relative;
    display: inline-flex;
}

.bulk-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 1px);
    left: 0;
    right: 0;
    background: var(--surface);
    border: 1px solid #e0e6ed;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.05);
    max-height: 250px;
    overflow-y: auto;
    z-index: 500;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bulk-dropdown.open .bulk-dropdown-menu {
    display: block;
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.bulk-dropdown-arrow {
    font-size: 18px !important;
    margin-left: -4px;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    padding: 12px 16px;
    border: none;
    background: transparent;
    font-size: 15px;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.bulk-dropdown-item:first-child {
    border-radius: 10px 10px 0 0;
}

.bulk-dropdown-item:last-child {
    border-radius: 0 0 10px 10px;
}

.bulk-dropdown-item:hover {
    background: linear-gradient(90deg, rgba(21, 101, 192, 0.08) 0%, rgba(21, 101, 192, 0.04) 100%);
    padding-left: 20px;
}

.bulk-dropdown-item .material-icons {
    font-size: 18px;
    color: var(--text-secondary);
}

.bulk-dropdown-item.item-danger {
    color: var(--danger);
}

.bulk-dropdown-item.item-danger:hover {
    background: linear-gradient(90deg, rgba(239, 83, 80, 0.12) 0%, rgba(239, 83, 80, 0.04) 100%);
    padding-left: 20px;
}

.bulk-dropdown-menu::-webkit-scrollbar { width: 6px; }
.bulk-dropdown-menu::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.bulk-dropdown-menu::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.bulk-dropdown-menu::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

body.dark-mode .bulk-dropdown-menu,
[data-theme="dark"] .bulk-dropdown-menu {
    background: var(--surface);
    border-color: #374151;
}

body.dark-mode .bulk-dropdown-item:hover,
[data-theme="dark"] .bulk-dropdown-item:hover {
    background: linear-gradient(90deg, rgba(66, 153, 225, 0.15) 0%, rgba(66, 153, 225, 0.08) 100%);
}

body.dark-mode .bulk-dropdown-item.item-danger:hover,
[data-theme="dark"] .bulk-dropdown-item.item-danger:hover {
    background: linear-gradient(90deg, rgba(239, 83, 80, 0.15) 0%, rgba(239, 83, 80, 0.08) 100%);
}

/* Pagination Controls - Bottom Center (same as accounts.php) */
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

/* Tablet: show mobile filter bar when table view hides */
@media (max-width: 1024px) {
    .filter-bar-mobile {
        display: block !important;
    }
    .bulk-actions-full {
        display: none;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .pagination-controls-wrapper {
        padding: 16px;
    }
    
    .page-info {
        font-size: 13px;
        min-width: 90px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
    }
    
    .btn-icon .material-icons {
        font-size: 20px;
    }
}
</style>

<script>
// Mobile Filter Dropdown Handler
document.addEventListener('DOMContentLoaded', function() {
    const mobileTrigger = document.getElementById('mobile-filter-trigger');
    const mobileOptions = document.getElementById('mobile-filter-options');
    const mobileSelectedText = mobileTrigger?.querySelector('.selected-text');
    
    if (!mobileTrigger || !mobileOptions) return;
    
    // Toggle mobile filter dropdown
    mobileTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        mobileTrigger.classList.toggle('active');
        mobileOptions.classList.toggle('active');
    });
    
    // Close mobile dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!mobileTrigger.contains(e.target) && !mobileOptions.contains(e.target)) {
            mobileTrigger.classList.remove('active');
            mobileOptions.classList.remove('active');
        }
    });
    
    // Handle mobile filter option selection
    mobileOptions.addEventListener('click', function(e) {
        const option = e.target.closest('.custom-select-option');
        if (!option) return;
        
        const statusType = option.dataset.status;
        const text = option.textContent.trim();
        
        // Remove selected class from all options
        mobileOptions.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        
        // Add selected class to clicked option
        option.classList.add('selected');
        
        // Update selected text
        mobileSelectedText.textContent = text;
        
        // Close dropdown
        mobileTrigger.classList.remove('active');
        mobileOptions.classList.remove('active');
        
        // Apply the filter (simulate click on desktop filter button)
        const desktopButton = document.querySelector(`.filter-btn[data-status="${statusType}"]`);
        if (desktopButton) {
            desktopButton.click();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
