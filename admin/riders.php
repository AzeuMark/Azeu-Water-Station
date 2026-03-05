<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDERS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: View and manage rider accounts
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Riders";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Riders</h1>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Available</th>
                        <th>Total Deliveries</th>
                        <th>Active</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody id="riders-tbody">
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
let allRiders = [];
let currentPage = 1;
let itemsPerPage = 20;

document.addEventListener('DOMContentLoaded', loadRiders);

async function loadRiders() {
    try {
        const response = await fetch('../api/riders/list.php');
        const data = await response.json();
        
        if (data.success && data.riders.length > 0) {
            allRiders = data.riders;
            currentPage = 1;
            renderRiders();
        } else {
            const tbody = document.getElementById('riders-tbody');
            tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No riders</p></div></td></tr>';
            updatePaginationControls(0);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function renderRiders() {
    const tbody = document.getElementById('riders-tbody');
    
    if (allRiders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No riders</p></div></td></tr>';
        updatePaginationControls(0);
        return;
    }
    
    const totalPages = Math.ceil(allRiders.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedRiders = allRiders.slice(startIndex, endIndex);
    
    let html = '';
    paginatedRiders.forEach((rider, index) => {
        const rowNumber = startIndex + index + 1;
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td><strong>${rider.full_name}</strong></td>
                <td>${rider.phone}</td>
                <td>
                    <span class="badge ${rider.is_available ? 'badge-success' : 'badge-danger'}">
                        ${rider.is_available ? 'Available' : 'Unavailable'}
                    </span>
                </td>
                <td>${rider.total_deliveries}</td>
                <td>${rider.active_deliveries}</td>
                <td>${rider.completed_deliveries}</td>
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
    
    if (!pageInfo) return;
    
    pageInfo.textContent = `Page ${currentPage} of ${totalPages || 1}`;
    
    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderRiders();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allRiders.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderRiders();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
