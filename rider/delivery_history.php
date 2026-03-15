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
    
    <!-- Mobile Filter Bar -->
    <div class="glass-card filter-bar-mobile history-filter-mobile" style="margin-bottom: 24px; display: none;">
        <div class="filter-bar-mobile-inner">
            <div class="filter-bar-mobile-filter">
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger" id="history-mobile-filter-trigger">
                        <span class="material-icons" style="margin-right: 8px; font-size: 20px;">filter_list</span>
                        <span class="selected-text">All</span>
                        <span class="material-icons arrow">expand_more</span>
                    </div>
                    <div class="custom-select-options" id="history-mobile-filter-options">
                        <div class="custom-select-option selected" data-status="">All</div>
                        <div class="custom-select-option" data-status="delivered">Delivered</div>
                        <div class="custom-select-option" data-status="accepted">Completed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="glass-card history-table-view">
        <!-- Desktop Filter Bar -->
        <div class="history-filter-section">
            <div class="filter-bar">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">filter_list</span>
                    Filter:
                </div>
                <button class="filter-btn active" data-status="">All</button>
                <button class="filter-btn" data-status="delivered">Delivered</button>
                <button class="filter-btn" data-status="accepted">Completed</button>
            </div>
        </div>
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
        
        <!-- Desktop Pagination -->
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

    <!-- Mobile/Tablet Card View -->
    <div class="history-card-view" id="history-cards">
        <div class="spinner" style="margin: 40px auto;"></div>
    </div>

    <!-- Mobile Pagination -->
    <div id="history-pagination-mobile" style="display: none; justify-content: center; align-items: center; padding: 16px 20px; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius); margin-top: 16px;">
        <div class="pagination-controls">
            <button class="btn-icon" onclick="prevHistoryPage()" id="history-prev-btn-mobile" title="Previous">
                <span class="material-icons">chevron_left</span>
            </button>
            <span class="page-info" id="history-page-info-mobile">Page 1 of 1</span>
            <button class="btn-icon" onclick="nextHistoryPage()" id="history-next-btn-mobile" title="Next">
                <span class="material-icons">chevron_right</span>
            </button>
        </div>
    </div>
</main>

<style>
/* Show/hide logic — table on desktop, cards on tablet/mobile */
.history-card-view {
    display: none;
}

.history-table-view {
    display: block;
}

@media (max-width: 1024px) {
    .history-card-view {
        display: block;
    }
    .history-table-view {
        display: none;
    }
    .history-filter-mobile {
        display: block !important;
    }
}

/* Filter section inside glass-card */
.history-filter-section {
    margin: -20px -20px 0 -20px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.history-filter-section .filter-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Pagination */
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

/* Card grid */
.history-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 600px) and (max-width: 1024px) {
    .history-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Individual card */
.history-card {
    background: var(--surface-card);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.history-card:hover {
    box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Card header */
.history-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}

.history-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}

.history-card-header-left .material-icons {
    font-size: 20px;
    color: var(--primary);
}

/* Card data rows */
.history-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.history-card-row:last-child {
    border-bottom: none;
}

.history-card-label {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-shrink: 0;
}

.history-card-label .material-icons {
    font-size: 16px;
}

.history-card-value {
    font-weight: 600;
    color: var(--text-primary);
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

.history-card-value.total-highlight {
    color: var(--primary);
    font-size: 15px;
    font-weight: 700;
}

/* Badge in card */
.history-card-value .badge {
    font-size: 12px;
    padding: 3px 10px;
}

/* Empty state for cards */
.history-cards-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
    background: var(--surface-card);
    border-radius: 16px;
    border: 1px solid var(--border);
}

.history-cards-empty .material-icons {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.history-cards-empty p {
    font-size: 15px;
    font-weight: 500;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .pagination-controls-wrapper {
        padding: 16px;
    }
    .page-info {
        font-size: 13px;
        min-width: 90px;
    }
}
</style>

<script>
let allHistoryOrders = [];
let filteredHistoryOrders = [];
let historyFilter = '';
let historyPage = 1;
const historyPerPageDesktop = 10;
const historyPerPageMobile = 5;

function getHistoryPerPage() {
    return window.innerWidth <= 1024 ? historyPerPageMobile : historyPerPageDesktop;
}

document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
    initHistoryFilters();
    initHistoryMobileFilter();
    
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (filteredHistoryOrders.length > 0) renderHistory();
        }, 150);
    });
});

function initHistoryFilters() {
    document.querySelectorAll('.history-filter-section .filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.history-filter-section .filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            historyFilter = this.dataset.status;
            applyHistoryFilter();
        });
    });
}

function initHistoryMobileFilter() {
    const trigger = document.getElementById('history-mobile-filter-trigger');
    const options = document.getElementById('history-mobile-filter-options');
    const selectedText = trigger?.querySelector('.selected-text');
    
    if (!trigger || !options) return;
    
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        trigger.classList.toggle('active');
        options.classList.toggle('active');
    });
    
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !options.contains(e.target)) {
            trigger.classList.remove('active');
            options.classList.remove('active');
        }
    });
    
    options.addEventListener('click', function(e) {
        const option = e.target.closest('.custom-select-option');
        if (!option) return;
        
        options.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        selectedText.textContent = option.textContent.trim();
        
        trigger.classList.remove('active');
        options.classList.remove('active');
        
        // Sync with desktop filter
        historyFilter = option.dataset.status;
        document.querySelectorAll('.history-filter-section .filter-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.status === historyFilter);
        });
        applyHistoryFilter();
    });
}

function applyHistoryFilter() {
    if (historyFilter) {
        filteredHistoryOrders = allHistoryOrders.filter(o => o.status === historyFilter);
    } else {
        filteredHistoryOrders = [...allHistoryOrders];
    }
    historyPage = 1;
    renderHistory();
}

async function loadHistory() {
    try {
        const response = await fetch('../api/orders/list.php?limit=100');
        const data = await response.json();
        
        if (data.success) {
            allHistoryOrders = data.orders.filter(o => 
                o.status === 'delivered' || o.status === 'accepted'
            );
            applyHistoryFilter();
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
    const cardsContainer = document.getElementById('history-cards');
    
    if (filteredHistoryOrders.length === 0) {
        showEmptyState();
        return;
    }
    
    const perPage = getHistoryPerPage();
    const totalPages = Math.ceil(filteredHistoryOrders.length / perPage);
    if (historyPage > totalPages) historyPage = totalPages;
    const startIdx = (historyPage - 1) * perPage;
    const pageOrders = filteredHistoryOrders.slice(startIdx, startIdx + perPage);
    
    // Table view
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
    
    // Card view
    if (cardsContainer) {
        renderHistoryCards(pageOrders, cardsContainer, startIdx);
    }
    
    updateHistoryPagination(totalPages);
}

function renderHistoryCards(orders, container, startIdx) {
    let cardsHtml = '<div class="history-cards-grid">';
    orders.forEach((order, index) => {
        const cardNumber = startIdx + index + 1;
        const statusLabel = order.status === 'accepted' ? 'Completed' : 'Delivered';
        const statusClass = order.status.replace('_', '-');
        cardsHtml += `
            <div class="history-card">
                <div class="history-card-header">
                    <div class="history-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    <span class="badge badge-${statusClass}">${statusLabel}</span>
                </div>
                <div class="history-card-row">
                    <div class="history-card-label"><span class="material-icons">receipt</span> Order ID</div>
                    <div class="history-card-value"><strong>#${order.id}</strong></div>
                </div>
                <div class="history-card-row">
                    <div class="history-card-label"><span class="material-icons">calendar_today</span> Date</div>
                    <div class="history-card-value">${formatDate(order.delivered_at || order.order_date)}</div>
                </div>
                <div class="history-card-row">
                    <div class="history-card-label"><span class="material-icons">person</span> Customer</div>
                    <div class="history-card-value">${order.customer_name}</div>
                </div>
                <div class="history-card-row">
                    <div class="history-card-label"><span class="material-icons">location_on</span> Address</div>
                    <div class="history-card-value">${order.delivery_address ? truncate(order.delivery_address, 35) : 'Pickup'}</div>
                </div>
                <div class="history-card-row">
                    <div class="history-card-label"><span class="material-icons">payments</span> Amount</div>
                    <div class="history-card-value total-highlight">${formatCurrency(order.total_amount)}</div>
                </div>
            </div>
        `;
    });
    cardsHtml += '</div>';
    container.innerHTML = cardsHtml;
}

function updateHistoryPagination(totalPages) {
    const wrapper = document.getElementById('history-pagination');
    const wrapperMobile = document.getElementById('history-pagination-mobile');
    const info = document.getElementById('history-page-info');
    const infoMobile = document.getElementById('history-page-info-mobile');
    const prevBtn = document.getElementById('history-prev-btn');
    const nextBtn = document.getElementById('history-next-btn');
    const prevBtnMobile = document.getElementById('history-prev-btn-mobile');
    const nextBtnMobile = document.getElementById('history-next-btn-mobile');
    
    if (totalPages <= 1) {
        wrapper.style.display = 'none';
        if (wrapperMobile) wrapperMobile.style.display = 'none';
        return;
    }
    
    wrapper.style.display = 'flex';
    info.textContent = `Page ${historyPage} of ${totalPages}`;
    prevBtn.disabled = historyPage <= 1;
    nextBtn.disabled = historyPage >= totalPages;
    
    if (infoMobile) infoMobile.textContent = `Page ${historyPage} of ${totalPages}`;
    if (prevBtnMobile) prevBtnMobile.disabled = historyPage <= 1;
    if (nextBtnMobile) nextBtnMobile.disabled = historyPage >= totalPages;
}

function prevHistoryPage() {
    if (historyPage > 1) {
        historyPage--;
        renderHistory();
    }
}

function nextHistoryPage() {
    const totalPages = Math.ceil(filteredHistoryOrders.length / getHistoryPerPage());
    if (historyPage < totalPages) {
        historyPage++;
        renderHistory();
    }
}

function showEmptyState() {
    document.getElementById('history-pagination').style.display = 'none';
    const paginationMobile = document.getElementById('history-pagination-mobile');
    if (paginationMobile) paginationMobile.style.display = 'none';
    
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
    
    const cardsContainer = document.getElementById('history-cards');
    if (cardsContainer) {
        cardsContainer.innerHTML = `
            <div class="history-cards-empty">
                <span class="material-icons">history</span>
                <p>No delivery history</p>
                <p style="font-size: 13px; margin-top: 4px; opacity: 0.7;">Your completed deliveries will appear here</p>
            </div>
        `;
    }
}

function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
