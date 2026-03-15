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
    
    <!-- Mobile Filter Bar -->
    <div class="glass-card filter-bar-mobile logs-filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div class="filter-bar-mobile-inner">
            <div class="filter-bar-mobile-filter">
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger" id="mobile-filter-trigger">
                        <span class="material-icons" style="margin-right: 8px; font-size: 20px;">filter_list</span>
                        <span class="selected-text">All</span>
                        <span class="material-icons arrow">expand_more</span>
                    </div>
                    <div class="custom-select-options" id="mobile-filter-options">
                        <div class="custom-select-option selected" data-action="">All</div>
                        <div class="custom-select-option" data-action="login">Login</div>
                        <div class="custom-select-option" data-action="logout">Logout</div>
                        <div class="custom-select-option" data-action="force_logout">Force Logout</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="glass-card logs-table-view">
        <!-- Filter Bar -->
        <div class="logs-filter-section">
            <div class="filter-bar">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">filter_list</span>
                    Filter:
                </div>
                <button class="filter-btn active" data-action="">All</button>
                <button class="filter-btn" data-action="login">Login</button>
                <button class="filter-btn" data-action="logout">Logout</button>
                <button class="filter-btn" data-action="force_logout">Force Logout</button>
            </div>
        </div>

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
                                    <span class="badge <?php echo $log['action'] === 'login' ? 'badge-success' : ($log['action'] === 'logout' ? 'badge-info' : 'badge-danger'); ?>">
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
    <div class="logs-card-view" id="logs-cards"></div>

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
/* Override badge-success for session logs to match badge-info border style */
.main-content .badge-success {
    background: transparent !important;
    color: #28a745 !important;
    border: 1.5px solid #28a745 !important;
}

/* Show/hide logic — Table on desktop, Cards on mobile/tablet */
.logs-card-view {
    display: none;
}

.logs-table-view {
    display: block;
}

@media (max-width: 1024px) {
    .logs-card-view {
        display: block;
    }
    .logs-table-view {
        display: none;
    }
    .logs-filter-bar-mobile {
        display: block !important;
    }
}

/* Filter Section inside glass-card */
.logs-filter-section {
    margin: -20px -20px 0 -20px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.logs-filter-section .filter-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Card grid */
.log-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 600px) and (max-width: 1024px) {
    .log-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Individual card */
.log-card {
    background: var(--surface-card);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.log-card:hover {
    box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Card header */
.log-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.log-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}

.log-card-header-left .material-icons {
    font-size: 20px;
    color: var(--primary);
}

.log-card-action-badge .badge {
    font-size: 12px;
    padding: 3px 10px;
}

/* Card data rows */
.log-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.log-card-row:last-child {
    border-bottom: none;
}

.log-card-label {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-shrink: 0;
}

.log-card-label .material-icons {
    font-size: 16px;
}

.log-card-value {
    font-weight: 600;
    color: var(--text-primary);
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

/* Badge in card */
.log-card-value .badge {
    font-size: 12px;
    padding: 3px 10px;
}

/* Empty state for cards */
.log-cards-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
    background: var(--surface-card);
    border-radius: 16px;
    border: 1px solid var(--border);
}

.log-cards-empty .material-icons {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.log-cards-empty p {
    font-size: 15px;
    font-weight: 500;
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
    background: var(--surface);
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
let filteredLogs = [...allLogs];
let currentFilter = '';
let currentPage = 1;
let itemsPerPage = 20;

function applyFilter() {
    if (currentFilter) {
        filteredLogs = allLogs.filter(log => log.action === currentFilter);
    } else {
        filteredLogs = [...allLogs];
    }
    currentPage = 1;
    renderLogs();
}

function initFilterButtons() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.action;
            applyFilter();
        });
    });
}

function renderLogs() {
    const tbody = document.getElementById('logs-tbody');
    const cardsContainer = document.getElementById('logs-cards');
    
    if (filteredLogs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No session logs</p></div></td></tr>';
        if (cardsContainer) {
            cardsContainer.innerHTML = '<div class="log-cards-empty"><span class="material-icons">history</span><p>No session logs</p></div>';
        }
        updatePaginationControls(0);
        return;
    }
    
    const totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedLogs = filteredLogs.slice(startIndex, endIndex);
    
    // Render table rows
    let html = '';
    paginatedLogs.forEach((log, index) => {
        const rowNumber = startIndex + index + 1;
        const actionBadgeClass = log.action === 'login' ? 'badge-success' : (log.action === 'logout' ? 'badge-info' : 'badge-danger');
        
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
    
    // Render card view
    if (cardsContainer) {
        let cardsHtml = '<div class="log-cards-grid">';
        paginatedLogs.forEach((log, index) => {
            const cardNumber = startIndex + index + 1;
            const actionBadgeClass = log.action === 'login' ? 'badge-success' : (log.action === 'logout' ? 'badge-info' : 'badge-danger');
            
            cardsHtml += `
                <div class="log-card">
                    <div class="log-card-header">
                        <div class="log-card-header-left">
                            <span class="material-icons">tag</span>
                            <span>${cardNumber}</span>
                        </div>
                        <div class="log-card-action-badge">
                            <span class="badge ${actionBadgeClass}">${log.action}</span>
                        </div>
                    </div>
                    <div class="log-card-row">
                        <div class="log-card-label"><span class="material-icons">person</span> Username</div>
                        <div class="log-card-value"><strong>${log.username}</strong></div>
                    </div>
                    <div class="log-card-row">
                        <div class="log-card-label"><span class="material-icons">badge</span> Role</div>
                        <div class="log-card-value"><span class="badge badge-${log.role}">${log.role}</span></div>
                    </div>
                    <div class="log-card-row">
                        <div class="log-card-label"><span class="material-icons">language</span> IP Address</div>
                        <div class="log-card-value">${log.ip_address}</div>
                    </div>
                    <div class="log-card-row">
                        <div class="log-card-label"><span class="material-icons">schedule</span> Timestamp</div>
                        <div class="log-card-value">${formatDate(log.created_at)}</div>
                    </div>
                </div>
            `;
        });
        cardsHtml += '</div>';
        cardsContainer.innerHTML = cardsHtml;
    }
    
    updatePaginationControls(totalPages);
}

function updatePaginationControls(totalPages) {
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const pageInfoMobile = document.getElementById('page-info-mobile');
    const prevBtnMobile = document.getElementById('prev-btn-mobile');
    const nextBtnMobile = document.getElementById('next-btn-mobile');
    const paginationWrapper = document.getElementById('pagination-wrapper');
    const paginationWrapperMobile = document.getElementById('pagination-wrapper-mobile');
    
    const showPagination = totalPages > 1;
    
    if (paginationWrapper) paginationWrapper.style.display = showPagination ? 'flex' : 'none';
    if (paginationWrapperMobile) paginationWrapperMobile.style.display = showPagination ? 'flex' : 'none';
    
    if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${totalPages || 1}`;
    if (pageInfoMobile) pageInfoMobile.textContent = `Page ${currentPage} of ${totalPages || 1}`;
    
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
    const totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderLogs();
    }
}

// Mobile Filter Dropdown Handler
function initMobileFilter() {
    const mobileTrigger = document.getElementById('mobile-filter-trigger');
    const mobileOptions = document.getElementById('mobile-filter-options');
    const mobileSelectedText = mobileTrigger?.querySelector('.selected-text');
    
    if (!mobileTrigger || !mobileOptions) return;
    
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
        
        const actionType = option.dataset.action;
        const text = option.textContent.trim();
        
        mobileOptions.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        option.classList.add('selected');
        mobileSelectedText.textContent = text;
        
        mobileTrigger.classList.remove('active');
        mobileOptions.classList.remove('active');
        
        // Sync with desktop filter buttons
        const desktopButton = document.querySelector(`.filter-btn[data-action="${actionType}"]`);
        if (desktopButton) {
            desktopButton.click();
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initFilterButtons();
    initMobileFilter();
    renderLogs();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
