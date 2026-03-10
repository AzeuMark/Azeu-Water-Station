<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DELIVERY HISTORY PAGE
 * ============================================================================
 * 
 * Purpose: View completed delivery history
 * Role: RIDER
 * 
 * Features:
 * - List all completed deliveries
 * - Pagination
 * - View delivery details
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Delivery History";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Delivery History</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>Delivery History</span>
        </p>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table" id="history-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-controls-wrapper" id="history-pagination" style="display: none;">
            <div class="pagination-controls">
                <button class="btn-icon" onclick="prevHistoryPage()" id="history-prev-btn" title="Previous">
                    <span class="material-icons">chevron_left</span>
                </button>
                <span class="page-info" id="history-page-info">Page 1 of 1</span>
                <button class="btn-icon" onclick="nextHistoryPage()" id="history-next-btn" title="Next">
                    <span class="material-icons">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</main>

<style>
.pagination-controls-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    border-top: 1px solid var(--border);
}
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}
.page-info {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    padding: 0 8px;
    min-width: 100px;
    text-align: center;
}
</style>

<script>
let allHistoryOrders = [];
let historyPage = 1;
const historyPerPage = 15;

document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
});

async function loadHistory() {
    try {
        const response = await fetch('../api/orders/list.php?limit=100');
        const data = await response.json();
        
        if (data.success) {
            // Filter completed orders
            allHistoryOrders = data.orders.filter(o => 
                o.status === 'delivered' || o.status === 'accepted'
            );
            historyPage = 1;
            renderHistory();
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load history:', error);
        showEmptyState();
    }
}

function renderHistory() {
    const tbody = document.getElementById('history-tbody');
    
    if (allHistoryOrders.length === 0) {
        showEmptyState();
        return;
    }
    
    // Pagination
    const totalPages = Math.ceil(allHistoryOrders.length / historyPerPage);
    const startIdx = (historyPage - 1) * historyPerPage;
    const pageOrders = allHistoryOrders.slice(startIdx, startIdx + historyPerPage);
    
    let html = '';
    
    pageOrders.forEach(order => {
        html += `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${formatDate(order.delivered_at || order.order_date)}</td>
                <td>${order.customer_name}</td>
                <td>${order.delivery_address ? truncate(order.delivery_address, 40) : 'Pickup'}</td>
                <td><strong>${formatCurrency(order.total_amount)}</strong></td>
                <td>
                    <span class="badge badge-${order.status.replace('_', '-')}">
                        ${order.status === 'accepted' ? 'Completed' : 'Delivered'}
                    </span>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updateHistoryPagination(totalPages);
}

function updateHistoryPagination(totalPages) {
    const wrapper = document.getElementById('history-pagination');
    const info = document.getElementById('history-page-info');
    const prevBtn = document.getElementById('history-prev-btn');
    const nextBtn = document.getElementById('history-next-btn');
    
    if (totalPages <= 1) {
        wrapper.style.display = 'none';
        return;
    }
    
    wrapper.style.display = 'flex';
    info.textContent = `Page ${historyPage} of ${totalPages}`;
    prevBtn.disabled = historyPage <= 1;
    nextBtn.disabled = historyPage >= totalPages;
}

function prevHistoryPage() {
    if (historyPage > 1) {
        historyPage--;
        renderHistory();
    }
}

function nextHistoryPage() {
    const totalPages = Math.ceil(allHistoryOrders.length / historyPerPage);
    if (historyPage < totalPages) {
        historyPage++;
        renderHistory();
    }
}

function showEmptyState() {
    document.getElementById('history-pagination').style.display = 'none';
    const tbody = document.getElementById('history-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <span class="material-icons empty-icon">history</span>
                    <p class="empty-title">No delivery history</p>
                    <p class="empty-message">Your completed deliveries will appear here</p>
                </div>
            </td>
        </tr>
    `;
}

function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
