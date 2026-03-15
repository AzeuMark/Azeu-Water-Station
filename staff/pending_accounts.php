<?php
/**
 * ============================================================================
 * AZEU WATER STATION - PENDING ACCOUNTS PAGE
 * ============================================================================
 * 
 * Purpose: Approve/reject pending customer registrations
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Pending Accounts";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Pending Accounts</h1>
            <button class="btn btn-primary" onclick="approveAll()">
                <span class="material-icons">done_all</span>
                Approve All
            </button>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="glass-card pending-table-view">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th class="sortable-th" data-col="full_name">Name <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="username">Username <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="email">Email <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th" data-col="phone">Phone <span class="sort-icon material-icons">unfold_more</span></th>
                        <th class="sortable-th th-sorted" data-col="created_at">Registered <span class="sort-icon material-icons">arrow_downward</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pending-tbody">
                    <tr><td colspan="7" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
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
    <div class="pending-card-view" id="pending-cards">
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

/* ============================================================================
   RESPONSIVE TABLE/CARD VIEW SWITCHING
   ============================================================================ */

/* Show/hide logic */
.pending-card-view {
    display: none;
}

.pending-table-view {
    display: block;
}

@media (max-width: 1024px) {
    .pending-card-view {
        display: block;
    }
    .pending-table-view {
        display: none;
    }
}

/* ============================================================================
   PENDING ACCOUNT CARD STYLES — Mobile/Tablet View
   ============================================================================ */

/* Card grid */
.pending-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 600px) and (max-width: 1024px) {
    .pending-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Individual card */
.pending-card {
    background: var(--surface-card);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.pending-card:hover {
    box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Card header */
.pending-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.pending-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}

.pending-card-header-left .material-icons {
    font-size: 20px;
    color: var(--primary);
}

/* Card data rows */
.pending-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.pending-card-row:last-child {
    border-bottom: none;
}

.pending-card-label {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-shrink: 0;
}

.pending-card-label .material-icons {
    font-size: 16px;
}

.pending-card-value {
    font-weight: 600;
    color: var(--text-primary);
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

/* Card actions */
.pending-card-actions {
    display: flex;
    gap: 8px;
    padding: 14px 16px;
    border-top: 1px solid var(--border);
    background: var(--surface);
}

.pending-card-actions .btn {
    flex: 1;
    justify-content: center;
}

/* Empty state for cards */
.pending-cards-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
    background: var(--surface-card);
    border-radius: 16px;
    border: 1px solid var(--border);
}

.pending-cards-empty .material-icons {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.pending-cards-empty p {
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
let allPendingAccounts = [];
let currentPage = 1;
let itemsPerPage = window.innerWidth <= 1024 ? 10 : 20;
let sortCol = 'created_at';
let sortDir = 'desc';

function getItemsPerPage() {
    return window.innerWidth <= 1024 ? 10 : 20;
}

document.addEventListener('DOMContentLoaded', function() {
    loadPending();

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
            sortPending();
            currentPage = 1;
            renderPending();
        });
    });
    updateSortIcons();

    // Responsive items per page on resize
    window.addEventListener('resize', function() {
        const newPerPage = getItemsPerPage();
        if (newPerPage !== itemsPerPage) {
            itemsPerPage = newPerPage;
            currentPage = 1;
        }
        if (allPendingAccounts.length > 0) {
            renderPending();
        }
    });
});

async function loadPending() {
    try {
        const response = await fetch('../api/accounts/list.php?status=pending');
        const data = await response.json();

        if (data.success && data.accounts.length > 0) {
            allPendingAccounts = data.accounts;
            currentPage = 1;
            sortPending();
            renderPending();
        } else {
            allPendingAccounts = [];
            const tbody = document.getElementById('pending-tbody');
            tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No pending accounts</p></div></td></tr>';
            const cardsContainer = document.getElementById('pending-cards');
            if (cardsContainer) {
                cardsContainer.innerHTML = '<div class="pending-cards-empty"><span class="material-icons">hourglass_empty</span><p>No pending accounts</p></div>';
            }
            updatePaginationControls(0);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function sortPending() {
    allPendingAccounts.sort((a, b) => {
        let valA = sortCol === 'created_at' ? new Date(a[sortCol] ?? 0) : (a[sortCol] ?? '').toString().toLowerCase();
        let valB = sortCol === 'created_at' ? new Date(b[sortCol] ?? 0) : (b[sortCol] ?? '').toString().toLowerCase();
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

function renderPending() {
    const tbody = document.getElementById('pending-tbody');
    const cardsContainer = document.getElementById('pending-cards');

    if (allPendingAccounts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No pending accounts</p></div></td></tr>';
        updatePaginationControls(0);
        if (cardsContainer) {
            cardsContainer.innerHTML = '<div class="pending-cards-empty"><span class="material-icons">hourglass_empty</span><p>No pending accounts</p></div>';
        }
        return;
    }

    const totalPages = Math.ceil(allPendingAccounts.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedAccounts = allPendingAccounts.slice(startIndex, endIndex);

    // Table view
    let html = '';
    paginatedAccounts.forEach((acc, index) => {
        const rowNumber = startIndex + index + 1;
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td>${acc.full_name}</td>
                <td>${acc.username}</td>
                <td>${acc.email}</td>
                <td>${acc.phone}</td>
                <td>${formatDate(acc.created_at)}</td>
                <td style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button class="btn btn-sm btn-success" onclick="approve(${acc.id})">
                        <span class="material-icons">check</span> Approve
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deny(${acc.id}, '${acc.full_name.replace(/'/g, "\\'")}')">
                        <span class="material-icons">close</span> Deny
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    updatePaginationControls(totalPages);

    // Card view
    if (cardsContainer) {
        renderPendingCards(paginatedAccounts, cardsContainer, startIndex);
    }
}

function renderPendingCards(accounts, container, startIndex = 0) {
    let cardsHtml = '<div class="pending-cards-grid">';
    accounts.forEach((acc, index) => {
        const cardNumber = startIndex + index + 1;
        cardsHtml += `
            <div class="pending-card">
                <div class="pending-card-header">
                    <div class="pending-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                </div>
                <div class="pending-card-row">
                    <div class="pending-card-label"><span class="material-icons">person</span> Name</div>
                    <div class="pending-card-value">${acc.full_name}</div>
                </div>
                <div class="pending-card-row">
                    <div class="pending-card-label"><span class="material-icons">badge</span> Username</div>
                    <div class="pending-card-value">${acc.username}</div>
                </div>
                <div class="pending-card-row">
                    <div class="pending-card-label"><span class="material-icons">email</span> Email</div>
                    <div class="pending-card-value">${acc.email}</div>
                </div>
                <div class="pending-card-row">
                    <div class="pending-card-label"><span class="material-icons">phone</span> Phone</div>
                    <div class="pending-card-value">${acc.phone}</div>
                </div>
                <div class="pending-card-row">
                    <div class="pending-card-label"><span class="material-icons">calendar_today</span> Registered</div>
                    <div class="pending-card-value" style="color: var(--text-secondary); font-size: 13px;">${formatDate(acc.created_at)}</div>
                </div>
                <div class="pending-card-actions">
                    <button class="btn btn-sm btn-success" onclick="approve(${acc.id})">
                        <span class="material-icons">check</span> Approve
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deny(${acc.id}, '${acc.full_name.replace(/'/g, "\\'")}')">
                        <span class="material-icons">close</span> Deny
                    </button>
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

    // Hide pagination if only 1 page or no pages
    if (totalPages <= 1) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
        return;
    }

    // Show only the correct one for current viewport
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
        renderPending();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allPendingAccounts.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderPending();
    }
}

async function approve(userId) {
    if (!confirm('Approve this account?')) return;

    try {
        const response = await fetch('../api/accounts/approve.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, csrf_token: getCSRFToken() })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Account approved', 'success');
            await loadPending();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('Error occurred', 'error');
    }
}

async function deny(userId, fullName) {
    const result = await Swal.fire({
        title: 'Deny Account',
        html: `Deny and delete the account of <strong>${fullName}</strong>? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Deny',
        confirmButtonColor: '#EF5350',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('../api/accounts/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, csrf_token: getCSRFToken() })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Account denied and removed', 'success');
            await loadPending();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('Error occurred', 'error');
    }
}

async function approveAll() {
    if (allPendingAccounts.length === 0) {
        showToast('No pending accounts to approve', 'info');
        return;
    }

    const result = await Swal.fire({
        title: 'Approve All Pending',
        html: `Approve all <strong>${allPendingAccounts.length}</strong> pending account(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve All',
        confirmButtonColor: '#66BB6A',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    showLoading();
    let success = 0, failed = 0;

    for (const acc of allPendingAccounts) {
        try {
            const response = await fetch('../api/accounts/approve.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: acc.id, csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (e) { failed++; }
    }

    hideLoading();
    showToast(`Approved: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadPending();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
