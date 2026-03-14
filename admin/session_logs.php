<?php
/**
 * ============================================================================
 * AZEU WATER STATION - SESSION LOGS
 * ============================================================================
 * 
 * Purpose: View login/logout activity logs
 * Role: ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Session Logs";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get session logs
$logs = db_fetch_all("SELECT * FROM session_logs ORDER BY created_at DESC LIMIT 100");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Session Logs</h1>
    </div>
    
    <!-- Desktop Table View -->
    <div class="glass-card session-logs-table-view">
        <div class="data-table-wrapper sticky-table-wrapper">
            <table class="data-table sticky-cols-table">
                <thead>
                    <tr>
                        <th class="sticky-col sticky-col-1" style="width: 60px; text-align: center;">No</th>
                        <th class="sticky-col sticky-col-2">Username</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="logs-tbody">
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr>
                                <td class="sticky-col sticky-col-1" style="text-align: center; color: var(--text-secondary); font-weight: 600;"><?php echo $index + 1; ?></td>
                                <td class="sticky-col sticky-col-2"><strong><?php echo htmlspecialchars($log['username']); ?></strong></td>
                                <td><span class="badge badge-<?php echo $log['role']; ?>"><?php echo $log['role']; ?></span></td>
                                <td>
                                    <span class="badge <?php echo $log['action'] === 'login' ? 'badge-login' : ($log['action'] === 'logout' ? 'badge-info' : 'badge-danger'); ?>">
                                        <?php echo $log['action']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td><?php echo format_date($log['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6"><div class="empty-state"><p>No session logs</p></div></td></tr>
                    <?php endif; ?>
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
    <div class="session-logs-card-view" id="logs-cards">
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
/* Login/Logout action badges — outlined stroke style */
.badge-login {
    background: transparent;
    color: #28a745;
    border: 1.5px solid #28a745;
}

/* Sticky Columns - Responsive Table */
.sticky-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    position: relative;
}

.sticky-cols-table {
    min-width: 700px;
}

.sticky-col {
    position: sticky;
    z-index: 2;
    background: var(--surface-card);
}

.sticky-cols-table thead .sticky-col {
    z-index: 3;
    background: var(--surface);
}

.sticky-col-1 {
    left: 0;
    min-width: 50px;
    max-width: 50px;
}

.sticky-col-2 {
    left: 50px;
    min-width: 120px;
}

/* Shadow on second sticky col only when scrolled */
.sticky-col-2::after {
    content: '';
    position: absolute;
    top: 0;
    right: -6px;
    bottom: 0;
    width: 6px;
    box-shadow: inset 6px 0 6px -6px rgba(0, 0, 0, 0.15);
}

.sticky-cols-table tbody tr:hover .sticky-col {
    background: var(--hover, var(--surface));
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

/* Show/hide table vs card view */
.session-logs-table-view {
    display: block;
}

.session-logs-card-view {
    display: none;
}

/* Tablet: switch to card view at 1024px */
@media (max-width: 1024px) {
    .session-logs-table-view {
        display: none;
    }
    
    .session-logs-card-view {
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
let allLogs = <?php echo json_encode($logs); ?>;
let currentPage = 1;
let itemsPerPage = 20;

function renderLogs() {
    const tbody = document.getElementById('logs-tbody');
    const cardsContainer = document.getElementById('logs-cards');
    
    if (allLogs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No session logs</p></div></td></tr>';
        updatePaginationControls(0);
        if (cardsContainer) {
            cardsContainer.innerHTML = '<div class="order-cards-empty"><span class="material-icons">history</span><p>No session logs</p></div>';
        }
        return;
    }
    
    const totalPages = Math.ceil(allLogs.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedLogs = allLogs.slice(startIndex, endIndex);
    
    // Table view
    let html = '';
    paginatedLogs.forEach((log, index) => {
        const rowNumber = startIndex + index + 1;
        const actionBadgeClass = log.action === 'login' ? 'badge-login' : (log.action === 'logout' ? 'badge-info' : 'badge-danger');
        
        html += `
            <tr>
                <td class="sticky-col sticky-col-1" style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td class="sticky-col sticky-col-2"><strong>${log.username}</strong></td>
                <td><span class="badge badge-${log.role}">${log.role}</span></td>
                <td><span class="badge ${actionBadgeClass}">${log.action}</span></td>
                <td>${log.ip_address}</td>
                <td>${formatDate(log.created_at)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updatePaginationControls(totalPages);
    
    // Card view
    if (cardsContainer) {
        renderLogCards(paginatedLogs, cardsContainer, startIndex);
    }
}

function renderLogCards(logs, container, startIndex) {
    let cardsHtml = '<div class="order-cards-grid">';
    logs.forEach((log, index) => {
        const cardNumber = startIndex + index + 1;
        const actionBadgeClass = log.action === 'login' ? 'badge-login' : (log.action === 'logout' ? 'badge-info' : 'badge-danger');
        
        cardsHtml += `
            <div class="order-card">
                <div class="order-card-header">
                    <div class="order-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    <div class="order-card-actions">
                        <span class="badge ${actionBadgeClass}">${log.action}</span>
                    </div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">person</span> Username</div>
                    <div class="order-card-value">${log.username}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">shield</span> Role</div>
                    <div class="order-card-value"><span class="badge badge-${log.role}">${log.role}</span></div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">language</span> IP Address</div>
                    <div class="order-card-value">${log.ip_address}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">schedule</span> Timestamp</div>
                    <div class="order-card-value">${formatDate(log.created_at)}</div>
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
    
    // Hide pagination if only 1 page or no pages
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
        renderLogs();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allLogs.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderLogs();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (allLogs.length > 0) {
        renderLogs();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
