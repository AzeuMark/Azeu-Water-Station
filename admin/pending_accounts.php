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
        <h1 class="content-title">Pending Accounts</h1>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
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
let itemsPerPage = 20;

document.addEventListener('DOMContentLoaded', loadPending);

async function loadPending() {
    try {
        const response = await fetch('../api/accounts/list.php?status=pending');
        const data = await response.json();
        
        if (data.success && data.accounts.length > 0) {
            allPendingAccounts = data.accounts;
            currentPage = 1;
            renderPending();
        } else {
            const tbody = document.getElementById('pending-tbody');
            tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No pending accounts</p></div></td></tr>';
            updatePaginationControls(0);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function renderPending() {
    const tbody = document.getElementById('pending-tbody');
    
    if (allPendingAccounts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No pending accounts</p></div></td></tr>';
        updatePaginationControls(0);
        return;
    }
    
    const totalPages = Math.ceil(allPendingAccounts.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedAccounts = allPendingAccounts.slice(startIndex, endIndex);
    
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
                <td>
                    <button class="btn btn-sm btn-success" onclick="approve(${acc.id})">
                        <span class="material-icons">check</span> Approve
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updatePaginationControls(totalPages);
}

function updatePaginationControls(totalPages) {
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const paginationWrapper = document.getElementById('pagination-wrapper');
    
    if (!pageInfo) return;
    
    // Hide pagination if only 1 page or no pages
    if (totalPages <= 1) {
        paginationWrapper.style.display = 'none';
        return;
    }
    
    paginationWrapper.style.display = 'flex';
    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    
    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
