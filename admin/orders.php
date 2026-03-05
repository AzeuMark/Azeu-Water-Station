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
        <h1 class="content-title">Manage Orders</h1>
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
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>ID</th>
                        <th>Customer</th>
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
        
        <!-- Pagination Controls -->
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
                <label for="rider-select" style="display: block; margin-bottom: 8px; font-weight: 600;">Select Rider</label>
                <select id="rider-select" class="form-select" required>
                    <option value="">Loading...</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-rider-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<style>
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

/* Filter Bar Responsive */
.filter-bar-desktop {
    display: block;
}

.filter-bar-mobile {
    display: none;
    position: relative;
    z-index: 100;
}

/* Custom Select Styles */
.custom-select-wrapper {
    position: relative;
    width: 100%;
}

.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: var(--surface);
    border: 2px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: var(--text-primary);
}

.custom-select-trigger:hover {
    border-color: var(--primary);
}

.custom-select-trigger.active {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
}

.custom-select-trigger .arrow {
    transition: transform 0.3s ease;
}

.custom-select-trigger.active .arrow {
    transform: rotate(180deg);
}

.custom-select-options {
    position: absolute;
    top: calc(100% + 1px);
    left: 0;
    right: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.05);
    max-height: 190px;
    overflow-y: auto;
    z-index: 1001;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-select-options.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.custom-select-option {
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.2s;
    color: var(--text-primary);
}

.custom-select-option:hover {
    background: var(--hover);
}

.custom-select-option.selected {
    background: var(--primary);
    color: white;
    font-weight: 600;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .filter-bar-desktop {
        display: none;
    }
    
    .filter-bar-mobile {
        display: block !important;
    }
    
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
