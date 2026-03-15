<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF/ADMIN ACCOUNTS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Manage user accounts
 * Role: STAFF, ADMIN
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Manage Accounts";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Additional CSS for float-input-group -->
<link rel="stylesheet" href="../assets/css/auth.css">

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Manage Accounts</h1>
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
                <button class="filter-btn active" data-role="">All Roles</button>
                <button class="filter-btn" data-role="customer">Customers</button>
                <button class="filter-btn" data-role="rider">Riders</button>
                <button class="filter-btn" data-role="staff">Staff</button>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-filter-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">filter_list</span>
                    <span class="selected-text">All Roles</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-filter-options">
                    <div class="custom-select-option selected" data-role="">All Roles</div>
                    <div class="custom-select-option" data-role="customer">Customers</div>
                    <div class="custom-select-option" data-role="rider">Riders</div>
                    <div class="custom-select-option" data-role="staff">Staff</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="glass-card accounts-table-view">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th class="sortable-th" data-col="full_name">Name <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="username">Username <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="email">Email <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="role">Role <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="status">Status <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="created_at">Created Date <span class="sort-icon material-icons">unfold_more</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="accounts-tbody">
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

    <!-- Mobile/Tablet Card View -->
    <div class="accounts-card-view" id="accounts-cards">
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

<!-- Edit Account Modal -->
<div class="modal-overlay" id="edit-account-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Account</h3>
            <button class="modal-close" onclick="closeModal('edit-account-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="editAccountForm" onsubmit="submitEditAccount(event)">
            <div class="modal-body">
                <input type="hidden" id="edit_user_id">
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="edit_full_name" class="float-input" placeholder="Full Name" required>
                        <label for="edit_full_name" class="float-label">Full Name</label>
                        <span class="material-icons input-icon">person</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="edit_username" class="float-input" placeholder="Username" required>
                        <label for="edit_username" class="float-label">Username</label>
                        <span class="material-icons input-icon">badge</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="email" id="edit_email" class="float-input" placeholder="Email" required>
                        <label for="edit_email" class="float-label">Email</label>
                        <span class="material-icons input-icon">email</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="tel" id="edit_phone" class="float-input" placeholder="Phone" required>
                        <label for="edit_phone" class="float-label">Phone</label>
                        <span class="material-icons input-icon">phone</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="edit_role" class="float-input" placeholder="Role" disabled style="opacity: 0.6; cursor: not-allowed;">
                        <label for="edit_role" class="float-label">Role</label>
                        <span class="material-icons input-icon">admin_panel_settings</span>
                    </div>
                    <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 4px; padding-left: 12px;">Role cannot be changed here</small>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="password" id="edit_password" class="float-input" placeholder="New Password">
                        <label for="edit_password" class="float-label">New Password (Optional)</label>
                        <span class="material-icons input-icon">lock</span>
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('edit_password', this)">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                    <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 4px; padding-left: 12px;">Leave blank to keep current password</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-account-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px; vertical-align: middle;">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Sortable table headers */
.sortable-th {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
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

/* ============================================================================
   RESPONSIVE TABLE/CARD VIEW SWITCHING
   ============================================================================ */

/* Show/hide logic */
.accounts-card-view {
    display: none;
}

.accounts-table-view {
    display: block;
}

@media (max-width: 1024px) {
    .accounts-card-view {
        display: block;
    }
    .accounts-table-view {
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
   ACCOUNT CARD STYLES — Mobile/Tablet View
   ============================================================================ */

/* Card grid */
.account-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 600px) and (max-width: 1024px) {
    .account-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Individual card */
.account-card {
    background: var(--surface-card);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.account-card:hover {
    box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Card header */
.account-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.account-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}

.account-card-header-left .material-icons {
    font-size: 20px;
    color: var(--primary);
}

.account-card-actions {
    display: flex;
    gap: 4px;
}

.account-card-actions .btn-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
}

.account-card-actions .btn-icon .material-icons {
    font-size: 18px;
}

/* Card data rows */
.account-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.account-card-row:last-child {
    border-bottom: none;
}

.account-card-label {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-shrink: 0;
}

.account-card-label .material-icons {
    font-size: 16px;
}

.account-card-value {
    font-weight: 600;
    color: var(--text-primary);
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

/* Badge in card */
.account-card-value .badge {
    font-size: 12px;
    padding: 3px 10px;
}

/* Empty state for cards */
.account-cards-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
    background: var(--surface-card);
    border-radius: 16px;
    border: 1px solid var(--border);
}

.account-cards-empty .material-icons {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.account-cards-empty p {
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
</style>

<script>
let currentRoleFilter = '';
const currentUserRole = '<?php echo $_SESSION['role']; ?>';
let allAccounts = [];
let currentPage = 1;
let itemsPerPage = window.innerWidth <= 1024 ? 10 : 20;
let flagReasonsMap = {};
let sortCol = 'created_at';
let sortDir = 'desc';

function getItemsPerPage() {
    return window.innerWidth <= 1024 ? 10 : 20;
}

document.addEventListener('DOMContentLoaded', function() {
    loadAccounts();
    updateSortIcons();

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRoleFilter = this.dataset.role;
            loadAccounts();
        });
    });

    // Sortable header clicks
    document.querySelectorAll('.sortable-th').forEach(th => {
        th.addEventListener('click', function() {
            const col = this.dataset.col;
            if (sortCol === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortCol = col;
                sortDir = 'asc';
            }
            updateSortIcons();
            sortAccounts();
            currentPage = 1;
            renderAccounts();
        });
    });

    // Mobile Filter Dropdown Handler
    const mobileTrigger = document.getElementById('mobile-filter-trigger');
    const mobileOptions = document.getElementById('mobile-filter-options');
    const mobileSelectedText = mobileTrigger?.querySelector('.selected-text');

    if (mobileTrigger && mobileOptions) {
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

            const roleType = option.dataset.role;
            const text = option.textContent.trim();

            mobileOptions.querySelectorAll('.custom-select-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            option.classList.add('selected');
            mobileSelectedText.textContent = text;

            mobileTrigger.classList.remove('active');
            mobileOptions.classList.remove('active');

            const desktopButton = document.querySelector(`.filter-btn[data-role="${roleType}"]`);
            if (desktopButton) {
                desktopButton.click();
            }
        });
    }

    // Responsive items per page on resize
    window.addEventListener('resize', function() {
        const newPerPage = getItemsPerPage();
        if (newPerPage !== itemsPerPage) {
            itemsPerPage = newPerPage;
            currentPage = 1;
        }
        if (allAccounts.length > 0) {
            renderAccounts();
        }
    });
});

function sortAccounts() {
    allAccounts.sort((a, b) => {
        let valA = (a[sortCol] ?? '').toString().toLowerCase();
        let valB = (b[sortCol] ?? '').toString().toLowerCase();
        if (sortCol === 'created_at') {
            valA = new Date(a[sortCol] ?? 0);
            valB = new Date(b[sortCol] ?? 0);
        }
        if (valA < valB) return sortDir === 'asc' ? -1 : 1;
        if (valA > valB) return sortDir === 'asc' ? 1 : -1;
        return 0;
    });
}

function updateSortIcons() {
    document.querySelectorAll('.sortable-th').forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (th.dataset.col === sortCol) {
            icon.textContent = sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward';
            th.classList.add('th-sorted');
        } else {
            icon.textContent = 'unfold_more';
            th.classList.remove('th-sorted');
        }
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function loadAccounts() {
    try {
        const url = currentRoleFilter ? `../api/accounts/list.php?role=${currentRoleFilter}&status=active` : '../api/accounts/list.php?status=active';
        const response = await fetch(url);
        const data = await response.json();

        const tbody = document.getElementById('accounts-tbody');

        if (data.success && data.accounts.length > 0) {
            allAccounts = data.accounts;
            currentPage = 1;
            sortAccounts();
            renderAccounts();
        } else {
            allAccounts = [];
            tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><p>No accounts found</p></div></td></tr>';
            const cardsContainer = document.getElementById('accounts-cards');
            if (cardsContainer) {
                cardsContainer.innerHTML = '<div class="account-cards-empty"><span class="material-icons">people</span><p>No accounts found</p></div>';
            }
            updatePaginationControls(0);
        }
    } catch (error) {
        console.error('Failed to load accounts:', error);
    }
}

function renderAccounts() {
    const tbody = document.getElementById('accounts-tbody');
    const cardsContainer = document.getElementById('accounts-cards');

    if (allAccounts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><p>No accounts found</p></div></td></tr>';
        updatePaginationControls(0);
        if (cardsContainer) {
            cardsContainer.innerHTML = '<div class="account-cards-empty"><span class="material-icons">people</span><p>No accounts found</p></div>';
        }
        return;
    }

    const totalPages = Math.ceil(allAccounts.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedAccounts = allAccounts.slice(startIndex, endIndex);

    // Table view
    let html = '';
    flagReasonsMap = {};
    paginatedAccounts.forEach((acc, index) => {
        if (acc.flag_reason) flagReasonsMap[acc.id] = acc.flag_reason;
        const rowNumber = startIndex + index + 1;
        const actionButtons = getAccountActionButtons(acc);
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td>${acc.full_name}</td>
                <td>${acc.username}</td>
                <td>${acc.email}</td>
                <td><span class="badge badge-${acc.role}">${acc.role}</span></td>
                <td><span class="badge badge-${acc.status}">${acc.status}</span></td>
                <td style="white-space: nowrap; color: var(--text-secondary); font-size: 13px;">${formatDate(acc.created_at)}</td>
                <td>${actionButtons}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    updatePaginationControls(totalPages);

    // Card view
    if (cardsContainer) {
        renderAccountCards(paginatedAccounts, cardsContainer, startIndex);
    }
}

function getAccountActionButtons(acc) {
    if (acc.role === 'super_admin' || acc.role === 'admin' || acc.role === 'staff') {
        return `<span class="badge badge-restricted" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px;">
            <span class="material-icons" style="font-size: 14px;">block</span>
            RESTRICTED
        </span>`;
    }

    let buttons = '';

    buttons += `<button class="btn-icon" onclick="editAccount(${acc.id})" title="Edit Account">
        <span class="material-icons">edit</span>
    </button>`;

    if (acc.role === 'customer' || acc.role === 'rider') {
        if (acc.status === 'flagged') {
            buttons += `<button class="btn-icon btn-warning" onclick="viewFlagReason(${acc.id})" title="View Flag Reason">
                <span class="material-icons">info</span>
            </button>
            <button class="btn-icon" onclick="unflagAccount(${acc.id})" title="Unflag">
                <span class="material-icons">flag_circle</span>
            </button>`;
        } else if (acc.status !== 'pending') {
            buttons += `<button class="btn-icon" onclick="flagAccount(${acc.id})" title="Flag">
                <span class="material-icons">flag</span>
            </button>`;
        }
    }

    return buttons;
}

function renderAccountCards(accounts, container, startIndex = 0) {
    let cardsHtml = '<div class="account-cards-grid">';
    accounts.forEach((acc, index) => {
        if (acc.flag_reason) flagReasonsMap[acc.id] = acc.flag_reason;
        const cardNumber = startIndex + index + 1;
        const actionButtons = getAccountActionButtons(acc);
        cardsHtml += `
            <div class="account-card">
                <div class="account-card-header">
                    <div class="account-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    <div class="account-card-actions">
                        ${actionButtons}
                    </div>
                </div>
                <div class="account-card-row">
                    <div class="account-card-label"><span class="material-icons">person</span> Name</div>
                    <div class="account-card-value">${acc.full_name}</div>
                </div>
                <div class="account-card-row">
                    <div class="account-card-label"><span class="material-icons">badge</span> Username</div>
                    <div class="account-card-value">${acc.username}</div>
                </div>
                <div class="account-card-row">
                    <div class="account-card-label"><span class="material-icons">email</span> Email</div>
                    <div class="account-card-value">${acc.email}</div>
                </div>
                <div class="account-card-row">
                    <div class="account-card-label"><span class="material-icons">admin_panel_settings</span> Role</div>
                    <div class="account-card-value"><span class="badge badge-${acc.role}">${acc.role}</span></div>
                </div>
                <div class="account-card-row">
                    <div class="account-card-label"><span class="material-icons">info</span> Status</div>
                    <div class="account-card-value"><span class="badge badge-${acc.status}">${acc.status}</span></div>
                </div>
                <div class="account-card-row">
                    <div class="account-card-label"><span class="material-icons">calendar_today</span> Created</div>
                    <div class="account-card-value" style="color: var(--text-secondary); font-size: 13px;">${formatDate(acc.created_at)}</div>
                </div>
            </div>
        `;
    });
    cardsHtml += '</div>';
    container.innerHTML = cardsHtml;
}

function updatePaginationControls(totalPages) {
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const paginationWrapper = document.getElementById('pagination-wrapper');

    const pageInfoMobile = document.getElementById('page-info-mobile');
    const prevBtnMobile = document.getElementById('prev-btn-mobile');
    const nextBtnMobile = document.getElementById('next-btn-mobile');
    const paginationWrapperMobile = document.getElementById('pagination-wrapper-mobile');

    if (!pageInfo) return;

    if (totalPages <= 1) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
        return;
    }

    if (window.innerWidth <= 1024) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'flex';
    } else {
        if (paginationWrapper) paginationWrapper.style.display = 'flex';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
    }

    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    if (pageInfoMobile) pageInfoMobile.textContent = `Page ${currentPage} of ${totalPages}`;

    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
    if (prevBtnMobile) prevBtnMobile.disabled = currentPage <= 1;
    if (nextBtnMobile) nextBtnMobile.disabled = currentPage >= totalPages;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderAccounts();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allAccounts.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderAccounts();
    }
}

async function flagAccount(userId) {
    const { value: reason, isConfirmed } = await Swal.fire({
        title: 'Flag Account',
        input: 'textarea',
        inputLabel: 'Reason for flagging',
        inputPlaceholder: 'Enter the reason for flagging this account...',
        inputAttributes: { 'aria-label': 'Flag reason' },
        showCancelButton: true,
        confirmButtonText: 'Flag Account',
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value || !value.trim()) return 'Please provide a reason for flagging.';
        }
    });

    if (!isConfirmed || !reason) return;

    try {
        const response = await fetch('../api/accounts/flag.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, action: 'flag', reason: reason.trim(), csrf_token: getCSRFToken() })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Account flagged', 'success');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

async function unflagAccount(userId) {
    const result = await Swal.fire({
        title: 'Unflag Account?',
        text: 'This will restore the account to active status and reset the cancellation count.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, unflag it',
        confirmButtonColor: '#3085d6',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('../api/accounts/flag.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, action: 'unflag', csrf_token: getCSRFToken() })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Account unflagged', 'success');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

function viewFlagReason(userId) {
    const reason = flagReasonsMap[userId] || 'No reason provided.';
    Swal.fire({
        title: 'Flag Reason',
        text: reason,
        icon: 'warning',
        confirmButtonText: 'Close'
    });
}

async function editAccount(userId) {
    try {
        const response = await fetch(`../api/accounts/get.php?id=${userId}`);
        const data = await response.json();

        if (data.success && data.account) {
            const user = data.account;

            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_phone').value = user.phone;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_password').value = '';

            openModal('edit-account-modal');
        } else {
            showToast('Failed to load account details', 'error');
        }
    } catch (error) {
        console.error('Error loading account:', error);
        showToast('An error occurred', 'error');
    }
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    if (modalId === 'edit-account-modal') {
        document.getElementById('editAccountForm').reset();
    }
}

async function submitEditAccount(event) {
    event.preventDefault();

    const userId = document.getElementById('edit_user_id').value;
    const fullName = document.getElementById('edit_full_name').value;
    const username = document.getElementById('edit_username').value;
    const email = document.getElementById('edit_email').value;
    const phone = document.getElementById('edit_phone').value;
    const password = document.getElementById('edit_password').value;

    const payload = {
        user_id: parseInt(userId),
        full_name: fullName,
        username: username,
        email: email,
        phone: phone,
        csrf_token: getCSRFToken()
    };

    if (password.trim() !== '') {
        payload.password = password;
    }

    try {
        const response = await fetch('../api/accounts/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success) {
            showToast('Account updated successfully', 'success');
            closeModal('edit-account-modal');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed to update account', 'error');
        }
    } catch (error) {
        console.error('Error updating account:', error);
        showToast('An error occurred', 'error');
    }
}

// Password visibility toggle
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('.material-icons');

    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        const modalId = event.target.id;
        closeModal(modalId);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
