<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF INVENTORY VIEW
 * ============================================================================
 * 
 * Purpose: View-only inventory for staff users
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Inventory";
$page_css = "main.css";
$page_js = "inventory.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$isStaff = ($_SESSION['role'] === ROLE_STAFF);
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="content-title">Inventory</h1>
            <?php if (!$isStaff): ?>
            <button class="btn btn-primary" onclick="showAddItem()">
                <span class="material-icons">add</span> Add Item
            </button>
            <?php else: ?>
            <span class="badge" style="background: var(--surface); color: var(--text-muted); padding: 8px 14px; border-radius: 8px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-icons" style="font-size: 16px;">visibility</span>
                View Only
            </span>
            <?php endif; ?>
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
                <button class="filter-btn active" data-sort="name" onclick="applySortFilter(this, 'name')">Name (A-Z)</button>
                <button class="filter-btn" data-sort="name-desc" onclick="applySortFilter(this, 'name-desc')">Name (Z-A)</button>
                <button class="filter-btn" data-sort="stock-asc" onclick="applySortFilter(this, 'stock-asc')">Stock ↑</button>
                <button class="filter-btn" data-sort="stock-desc" onclick="applySortFilter(this, 'stock-desc')">Stock ↓</button>
                <button class="filter-btn" data-sort="price-asc" onclick="applySortFilter(this, 'price-asc')">Price ↑</button>
                <button class="filter-btn" data-sort="price-desc" onclick="applySortFilter(this, 'price-desc')">Price ↓</button>
                <button class="filter-btn" data-sort="status" onclick="applySortFilter(this, 'status')">Status</button>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-filter-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">filter_list</span>
                    <span class="selected-text">Name (A-Z)</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-filter-options">
                    <div class="custom-select-option selected" data-sort="name">Name (A-Z)</div>
                    <div class="custom-select-option" data-sort="name-desc">Name (Z-A)</div>
                    <div class="custom-select-option" data-sort="stock-asc">Stock (Low to High)</div>
                    <div class="custom-select-option" data-sort="stock-desc">Stock (High to Low)</div>
                    <div class="custom-select-option" data-sort="price-asc">Price (Low to High)</div>
                    <div class="custom-select-option" data-sort="price-desc">Price (High to Low)</div>
                    <div class="custom-select-option" data-sort="status">Status</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="glass-card inventory-table-view">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th class="sortable-th th-sorted th-asc" data-sort="name" data-sort-desc="name-desc">Item <span class="sort-icon material-icons">arrow_upward</span></th>
                        <th class="sortable-th" data-sort="price-asc" data-sort-desc="price-desc">Price <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-sort="stock-asc" data-sort-desc="stock-desc">Stock <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-sort="status" data-sort-desc="status">Status <span class="sort-icon material-icons">unfold_more</span></th>
                        <?php if (!$isStaff): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="inventory-tbody">
                    <tr><td colspan="<?php echo $isStaff ? '5' : '6'; ?>" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
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

    <!-- Mobile/Tablet Card View -->
    <div class="inventory-card-view" id="inventory-cards">
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

<?php if (!$isStaff): ?>
<!-- Add/Edit Item Modal -->
<div class="modal-overlay" id="item-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3 id="item-modal-title">Add Item</h3>
            <button class="modal-close" onclick="closeModal('item-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="item-form">
            <div class="modal-body">
                <input type="hidden" id="item-id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">Item Name</label>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger" id="item-select-trigger">
                            <span class="selected-text">Select an item...</span>
                            <span class="material-icons arrow">expand_more</span>
                        </div>
                        <div class="custom-select-options" id="item-select-options">
                            <!-- Dynamic options loaded from default_items -->
                        </div>
                    </div>
                    <input type="hidden" id="item-name-select" name="item-name" required>
                </div>
                <div class="form-group" id="custom-item-name-group" style="margin-bottom: 16px; display: none;">
                    <label>Custom Item Name</label>
                    <input type="text" id="item-name-custom" class="form-select" placeholder="Enter custom item name">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Price</label>
                    <input type="number" id="item-price" class="form-select" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" id="item-stock" class="form-select" min="0" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('item-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal-overlay" id="restock-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Restock Item</h3>
            <button class="modal-close" onclick="closeModal('restock-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="restock-form">
            <div class="modal-body">
                <input type="hidden" id="restock-item-id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Quantity to Add</label>
                    <input type="number" id="restock-qty" class="form-select" min="1" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '') || ''" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('restock-modal')">Cancel</button>
                <button type="submit" class="btn btn-success">Restock</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
/* Pagination Controls - Now at bottom center */
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

/* ============================================================================
   RESPONSIVE TABLE/CARD VIEW SWITCHING
   ============================================================================ */

.inventory-card-view {
    display: none;
}

.inventory-table-view {
    display: block;
}

@media (max-width: 1024px) {
    .inventory-card-view {
        display: block;
    }
    .inventory-table-view {
        display: none;
    }
    .filter-bar-desktop {
        display: none;
    }
    .filter-bar-mobile {
        display: block !important;
    }
}

/* ============================================================================
   INVENTORY CARD STYLES — Mobile/Tablet View
   ============================================================================ */

.inventory-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 600px) and (max-width: 1024px) {
    .inventory-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.inventory-card {
    background: var(--surface-card);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.inventory-card:hover {
    box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.inventory-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.inventory-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}

.inventory-card-header-left .material-icons {
    font-size: 20px;
    color: var(--primary);
}

.inventory-card-actions {
    display: flex;
    gap: 4px;
}

.inventory-card-actions .btn-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
}

.inventory-card-actions .btn-icon .material-icons {
    font-size: 18px;
}

.inventory-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.inventory-card-row:last-child {
    border-bottom: none;
}

.inventory-card-label {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-shrink: 0;
}

.inventory-card-label .material-icons {
    font-size: 16px;
}

.inventory-card-value {
    font-weight: 600;
    color: var(--text-primary);
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

.inventory-card-value.price-highlight {
    color: var(--primary);
    font-size: 15px;
    font-weight: 700;
}

.inventory-card-value .badge {
    font-size: 12px;
    padding: 3px 10px;
}

.inventory-cards-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
    background: var(--surface-card);
    border-radius: 16px;
    border: 1px solid var(--border);
}

.inventory-cards-empty .material-icons {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.inventory-cards-empty p {
    font-size: 15px;
    font-weight: 500;
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

/* Beautiful Custom Select Box */
.custom-select-wrapper {
    position: relative;
    width: 100%;
    user-select: none;
}

.custom-select-trigger {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    font-size: 15px;
    font-weight: 500;
    color: var(--text-primary);
    background: var(--surface-card);
    border: 2px solid #e0e6ed;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.custom-select-trigger:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(21, 101, 192, 0.15);
    transform: translateY(-1px);
}

.custom-select-trigger.active {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
}

.custom-select-trigger .selected-text {
    flex: 1;
    color: var(--text-primary);
}

.custom-select-trigger .selected-text.placeholder {
    color: #9ca3af;
}

.custom-select-trigger .arrow {
    font-size: 24px;
    color: #6b7280;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-select-trigger.active .arrow {
    transform: rotate(180deg);
    color: var(--primary-color);
}

.custom-select-options {
    position: absolute;
    top: calc(100% + 1px);
    left: 0;
    right: 0;
    background: var(--surface);
    border: 1px solid #e0e6ed;
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
    font-size: 15px;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.custom-select-option:first-child {
    border-radius: 10px 10px 0 0;
}

.custom-select-option:last-child {
    border-radius: 0 0 10px 10px;
}

.custom-select-option:hover {
    background: linear-gradient(90deg, rgba(21, 101, 192, 0.08) 0%, rgba(21, 101, 192, 0.04) 100%);
    padding-left: 20px;
}

.custom-select-option.selected {
    background: linear-gradient(90deg, rgba(21, 101, 192, 0.12) 0%, rgba(21, 101, 192, 0.06) 100%);
    color: var(--primary-color);
    font-weight: 600;
}

.custom-select-option.custom-option {
    border-top: 1px solid #e0e6ed;
    margin-top: 4px;
    font-style: italic;
    color: var(--primary-color);
}

.custom-select-option.custom-option::before {
    content: '✏️';
    margin-right: 8px;
}

/* Custom Scrollbar */
.custom-select-options::-webkit-scrollbar {
    width: 6px;
}

.custom-select-options::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.custom-select-options::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-select-options::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark Mode */
body.dark-mode .custom-select-trigger {
    background: var(--surface);
    border-color: #374151;
}

body.dark-mode .custom-select-options {
    background: var(--surface);
    border-color: #374151;
}

body.dark-mode .custom-select-option:hover {
    background: linear-gradient(90deg, rgba(66, 153, 225, 0.15) 0%, rgba(66, 153, 225, 0.08) 100%);
}
</style>

<script>
const isStaffReadOnly = <?php echo $isStaff ? 'true' : 'false'; ?>;

<?php if (!$isStaff): ?>
// Custom Select Box JavaScript (modal item name picker)
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('item-select-trigger');
    const optionsContainer = document.getElementById('item-select-options');
    const hiddenInput = document.getElementById('item-name-select');
    const selectedText = trigger.querySelector('.selected-text');

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        trigger.classList.toggle('active');
        optionsContainer.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !optionsContainer.contains(e.target)) {
            trigger.classList.remove('active');
            optionsContainer.classList.remove('active');
        }
    });

    optionsContainer.addEventListener('click', function(e) {
        const option = e.target.closest('.custom-select-option');
        if (!option) return;

        const value = option.dataset.value;
        const text = option.textContent.trim();

        optionsContainer.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
        });

        option.classList.add('selected');
        selectedText.textContent = text;
        selectedText.classList.remove('placeholder');
        hiddenInput.value = value;

        trigger.classList.remove('active');
        optionsContainer.classList.remove('active');

        toggleCustomItemName();
    });
});

function toggleCustomItemName() {
    const hiddenInput = document.getElementById('item-name-select');
    const customGroup = document.getElementById('custom-item-name-group');
    const customInput = document.getElementById('item-name-custom');

    if (hiddenInput.value === '__custom__') {
        customGroup.style.display = 'block';
        customInput.required = true;
        hiddenInput.required = false;
    } else {
        customGroup.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
        hiddenInput.required = true;
    }
}
<?php endif; ?>

// Mobile Filter Dropdown
document.addEventListener('DOMContentLoaded', function() {
    const mobileTrigger = document.getElementById('mobile-filter-trigger');
    const mobileOptions = document.getElementById('mobile-filter-options');
    const mobileSelectedText = mobileTrigger.querySelector('.selected-text');

    mobileTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        mobileTrigger.classList.toggle('active');
        mobileOptions.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!mobileTrigger.contains(e.target) && !mobileOptions.contains(e.target)) {
            mobileTrigger.classList.remove('active');
            mobileOptions.classList.remove('active');
        }
    });

    mobileOptions.addEventListener('click', function(e) {
        const option = e.target.closest('.custom-select-option');
        if (!option) return;

        const sortType = option.dataset.sort;
        const text = option.textContent.trim();

        mobileOptions.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
        });

        option.classList.add('selected');
        mobileSelectedText.textContent = text;

        mobileTrigger.classList.remove('active');
        mobileOptions.classList.remove('active');

        const desktopButton = document.querySelector(`.filter-btn[data-sort="${sortType}"]`);
        if (desktopButton) {
            applySortFilter(desktopButton, sortType);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
