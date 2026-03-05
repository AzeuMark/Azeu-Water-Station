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
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Username</th>
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
                                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;"><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($log['username']); ?></strong></td>
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
let allLogs = <?php echo json_encode($logs); ?>;
let currentPage = 1;
let itemsPerPage = 20;

function renderLogs() {
    const tbody = document.getElementById('logs-tbody');
    
    if (allLogs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No session logs</p></div></td></tr>';
        updatePaginationControls(0);
        return;
    }
    
    const totalPages = Math.ceil(allLogs.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedLogs = allLogs.slice(startIndex, endIndex);
    
    let html = '';
    paginatedLogs.forEach((log, index) => {
        const rowNumber = startIndex + index + 1;
        const actionBadgeClass = log.action === 'login' ? 'badge-success' : (log.action === 'logout' ? 'badge-info' : 'badge-danger');
        
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td><strong>${log.username}</strong></td>
                <td><span class="badge badge-${log.role}">${log.role}</span></td>
                <td><span class="badge ${actionBadgeClass}">${log.action}</span></td>
                <td>${log.ip_address}</td>
                <td>${formatDate(log.created_at)}</td>
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
