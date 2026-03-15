<?php
/**
 * ============================================================================
 * AZEU WATER STATION - APPEALS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Review and approve/deny cancellation appeals
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Appeals";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Cancellation Appeals</h1>
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
                <button class="filter-btn" data-status="approved">Approved</button>
                <button class="filter-btn" data-status="denied">Denied</button>
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
                    <div class="custom-select-option" data-status="approved">Approved</div>
                    <div class="custom-select-option" data-status="denied">Denied</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="glass-card appeals-table-view">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th class="sortable-th" data-col="customer_name">Customer <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="reason">Reason <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="created_at">Date <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="status">Status <span class="sort-icon material-icons">unfold_more</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="appeals-tbody">
                    <tr><td colspan="6" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Desktop Pagination Controls -->
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
    <div class="appeals-card-view" id="appeals-cards">
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

<!-- Review Appeal Modal -->
<div class="modal-overlay" id="review-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Review Appeal</h3>
            <button class="modal-close" onclick="closeModal('review-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="review-form">
            <div class="modal-body">
                <input type="hidden" id="appeal-id">
                <div id="appeal-details" style="margin-bottom: 20px;"></div>
                <div class="form-group">
                    <label for="admin-notes">Admin Notes (Optional)</label>
                    <textarea id="admin-notes" class="form-select" rows="3" placeholder="Add notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('review-modal')">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="reviewAppeal('deny')">Deny</button>
                <button type="button" class="btn btn-success" onclick="reviewAppeal('approve')">Approve</button>
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

/* Show/hide table vs card view */
.appeals-table-view {
    display: block;
}

.appeals-card-view {
    display: none;
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

/* Tablet: switch to card view and mobile filter at 1024px */
@media (max-width: 1024px) {
    .filter-bar-desktop {
        display: none;
    }

    .filter-bar-mobile {
        display: block !important;
    }

    .appeals-table-view {
        display: none;
    }

    .appeals-card-view {
        display: block;
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
let currentStatusFilter = '';
let allAppeals = [];
let currentPage = 1;
let itemsPerPage = 20;
let sortCol = 'created_at';
let sortDir = 'desc';

document.addEventListener('DOMContentLoaded', function() {
    loadAppeals();

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentStatusFilter = this.dataset.status;
            loadAppeals();
        });
    });

    // Sortable headers
    document.querySelectorAll('.sortable-th').forEach(th => {
        th.addEventListener('click', function() {
            const col = this.dataset.col;
            if (sortCol === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortCol = col;
                sortDir = col === 'created_at' ? 'desc' : 'asc';
            }
            updateSortIcons();
            sortAppeals();
            currentPage = 1;
            renderAppeals();
        });
    });
    updateSortIcons();

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

            const statusType = option.dataset.status;
            const text = option.textContent.trim();

            mobileOptions.querySelectorAll('.custom-select-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            option.classList.add('selected');
            mobileSelectedText.textContent = text;

            mobileTrigger.classList.remove('active');
            mobileOptions.classList.remove('active');

            const desktopButton = document.querySelector(`.filter-btn[data-status="${statusType}"]`);
            if (desktopButton) {
                desktopButton.click();
            }
        });
    }
});

async function loadAppeals() {
    try {
        const url = currentStatusFilter ? `../api/appeals/list.php?status=${currentStatusFilter}` : '../api/appeals/list.php';
        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.appeals.length > 0) {
            allAppeals = data.appeals;
            currentPage = 1;
            sortAppeals();
            renderAppeals();
        } else {
            allAppeals = [];
            const tbody = document.getElementById('appeals-tbody');
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No appeals found</p></div></td></tr>';
            const cardsContainer = document.getElementById('appeals-cards');
            if (cardsContainer) {
                cardsContainer.innerHTML = '<div class="order-cards-empty"><span class="material-icons">gavel</span><p>No appeals found</p></div>';
            }
            updatePaginationControls(0);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function sortAppeals() {
    const statusOrder = { pending: 1, approved: 2, denied: 3 };
    allAppeals.sort((a, b) => {
        let valA = a[sortCol] ?? '';
        let valB = b[sortCol] ?? '';
        if (sortCol === 'created_at') {
            valA = new Date(valA); valB = new Date(valB);
        } else if (sortCol === 'status') {
            valA = statusOrder[valA] ?? 99; valB = statusOrder[valB] ?? 99;
        } else {
            valA = valA.toString().toLowerCase(); valB = valB.toString().toLowerCase();
        }
        if (valA < valB) return sortDir === 'asc' ? -1 : 1;
        if (valA > valB) return sortDir === 'asc' ? 1 : -1;
        return 0;
    });
}

function updateSortIcons() {
    document.querySelectorAll('.sortable-th').forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (!icon) return;
        if (th.dataset.col === sortCol) {
            icon.textContent = sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward';
            th.classList.add('th-sorted');
        } else {
            icon.textContent = 'unfold_more';
            th.classList.remove('th-sorted');
        }
    });
}

function renderAppeals() {
    const tbody = document.getElementById('appeals-tbody');
    const cardsContainer = document.getElementById('appeals-cards');

    if (allAppeals.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No appeals found</p></div></td></tr>';
        updatePaginationControls(0);
        if (cardsContainer) {
            cardsContainer.innerHTML = '<div class="order-cards-empty"><span class="material-icons">gavel</span><p>No appeals found</p></div>';
        }
        return;
    }

    const totalPages = Math.ceil(allAppeals.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedAppeals = allAppeals.slice(startIndex, endIndex);

    // Table view
    let html = '';
    paginatedAppeals.forEach((appeal, index) => {
        const rowNumber = startIndex + index + 1;
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td><strong>${appeal.customer_name}</strong></td>
                <td>${truncate(appeal.reason, 60)}</td>
                <td>${formatDate(appeal.created_at)}</td>
                <td><span class="badge badge-${appeal.status}">${appeal.status}</span></td>
                <td>
                    ${appeal.status === 'pending' ?
                        `<button class="btn-icon" onclick="showReview(${appeal.id})" title="Review">
                            <span class="material-icons">rate_review</span>
                        </button>` :
                        `<span style="color: var(--text-muted);">Reviewed</span>`
                    }
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    updatePaginationControls(totalPages);

    // Card view
    if (cardsContainer) {
        renderAppealCards(paginatedAppeals, cardsContainer, startIndex);
    }
}

function renderAppealCards(appeals, container, startIndex) {
    let cardsHtml = '<div class="order-cards-grid">';
    appeals.forEach((appeal, index) => {
        const cardNumber = startIndex + index + 1;
        const actionHtml = appeal.status === 'pending'
            ? `<button class="btn-icon" onclick="showReview(${appeal.id})" title="Review">
                   <span class="material-icons">rate_review</span>
               </button>`
            : `<span style="color: var(--text-muted); font-size: 13px;">Reviewed</span>`;

        cardsHtml += `
            <div class="order-card">
                <div class="order-card-header">
                    <div class="order-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    <div class="order-card-actions">
                        ${actionHtml}
                    </div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">person</span> Customer</div>
                    <div class="order-card-value">${appeal.customer_name}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">subject</span> Reason</div>
                    <div class="order-card-value">${truncate(appeal.reason, 80)}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">calendar_today</span> Date</div>
                    <div class="order-card-value">${formatDate(appeal.created_at)}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">info</span> Status</div>
                    <div class="order-card-value"><span class="badge badge-${appeal.status}">${appeal.status}</span></div>
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

    // Mobile pagination elements
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

    if (paginationWrapper) paginationWrapper.style.display = 'flex';
    if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'flex';

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
        renderAppeals();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allAppeals.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderAppeals();
    }
}

async function showReview(appealId) {
    try {
        const response = await fetch('../api/appeals/list.php');
        const data = await response.json();

        if (data.success) {
            const appeal = data.appeals.find(a => a.id === appealId);
            if (appeal) {
                document.getElementById('appeal-id').value = appealId;
                document.getElementById('appeal-details').innerHTML = `
                    <div><strong>Customer:</strong> ${appeal.customer_name}</div>
                    <div style="margin-top: 12px;"><strong>Reason:</strong></div>
                    <div style="background: var(--surface); padding: 12px; border-radius: var(--radius-sm); margin-top: 8px;">
                        ${appeal.reason}
                    </div>
                `;
                document.getElementById('admin-notes').value = '';
                openModal('review-modal');
            }
        }
    } catch (error) {
        showToast('Error loading appeal', 'error');
    }
}

async function reviewAppeal(action) {
    const appealId = document.getElementById('appeal-id').value;
    const notes = document.getElementById('admin-notes').value;

    if (!confirm(`${action === 'approve' ? 'Approve' : 'Deny'} this appeal?`)) return;

    showLoading();

    try {
        const response = await fetch('../api/appeals/review.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                appeal_id: parseInt(appealId),
                action: action,
                admin_notes: notes,
                csrf_token: getCSRFToken()
            })
        });

        const data = await response.json();

        hideLoading();

        if (data.success) {
            showToast(`Appeal ${action}d successfully`, 'success');
            closeModal('review-modal');
            loadAppeals();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Error occurred', 'error');
    }
}

function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
