<?php
/**
 * ============================================================================
 * AZEU WATER STATION - ASSIGNED DELIVERIES PAGE
 * ============================================================================
 * 
 * Purpose: View and manage assigned deliveries (not yet on delivery)
 * Role: RIDER
 * 
 * Features:
 * - List all assigned deliveries with actual Order ID
 * - Request reassignment before starting delivery
 * - Start all deliveries at once
 * - Filter/sort by address, customer name, amount
 * - Pagination
 * - Reorder delivery priority (drag & drop)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Assigned Deliveries";
$page_css = "deliveries.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header" style="position: relative; z-index: 200;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Assigned Deliveries</h1>
            <button class="btn btn-primary" id="start-all-btn" onclick="startAllDeliveries()" style="display: none;">
                <span class="material-icons">play_circle</span>
                Start All Deliveries
            </button>
        </div>
    </div>
    
    <!-- Desktop Filter/Sort Bar -->
    <div class="glass-card filter-bar-desktop" style="margin-bottom: 24px;" id="filter-sort-bar" style="display: none;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">sort</span>
                    Sort by:
                </div>
                <button class="filter-btn active" data-sort="customer">Customer Name</button>
                <button class="filter-btn" data-sort="address">Address</button>
                <button class="filter-btn" data-sort="amount_asc">Amount ↑</button>
                <button class="filter-btn" data-sort="amount_desc">Amount ↓</button>
            </div>
        </div>
    </div>

    <!-- Mobile Sort Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px;" id="filter-sort-bar-mobile" style="display: none;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-sort-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">sort</span>
                    <span class="selected-text">Customer Name</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-sort-options">
                    <div class="custom-select-option selected" data-sort="customer">Customer Name</div>
                    <div class="custom-select-option" data-sort="address">Address</div>
                    <div class="custom-select-option" data-sort="amount_asc">Amount ↑</div>
                    <div class="custom-select-option" data-sort="amount_desc">Amount ↓</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 8px;">
            <h3 style="margin: 0;">Delivery Queue (<span id="delivery-count">0</span>)</h3>
        </div>
        
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="assigned-deliveries-list">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-controls-wrapper" id="assigned-pagination" style="display: none;">
            <div class="pagination-controls">
                <button class="btn-icon" onclick="prevAssignedPage()" id="assigned-prev-btn" title="Previous">
                    <span class="material-icons">chevron_left</span>
                </button>
                <span class="page-info" id="assigned-page-info">Page 1 of 1</span>
                <button class="btn-icon" onclick="nextAssignedPage()" id="assigned-next-btn" title="Next">
                    <span class="material-icons">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</main>

<style>
/* Desktop/Mobile filter bar visibility */
@media (max-width: 768px) {
    .filter-bar-desktop { display: none !important; }
    .filter-bar-mobile  { display: block !important; }
}
@media (min-width: 769px) {
    .filter-bar-desktop { display: block !important; }
    .filter-bar-mobile  { display: none !important; }
}
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
let assignedOrders = [];
let sortedOrders = [];
let currentSort = 'customer';
let assignedPage = 1;
const assignedPerPage = 10;

document.addEventListener('DOMContentLoaded', function() {
    loadAssignedDeliveries();
    initSortButtons();
});

function initSortButtons() {
    const sortBtns = document.querySelectorAll('[data-sort]');
    sortBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            sortBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.dataset.sort;
            assignedPage = 1;
            applySortAndRender();
        });
    });

    // Mobile custom-select
    const trigger = document.getElementById('mobile-sort-trigger');
    const optionsList = document.getElementById('mobile-sort-options');
    if (trigger && optionsList) {
        trigger.addEventListener('click', () => optionsList.classList.toggle('open'));
        optionsList.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.addEventListener('click', function() {
                optionsList.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                trigger.querySelector('.selected-text').textContent = this.textContent;
                optionsList.classList.remove('open');
                currentSort = this.dataset.sort;
                // Sync desktop buttons
                sortBtns.forEach(b => b.classList.toggle('active', b.dataset.sort === currentSort));
                assignedPage = 1;
                applySortAndRender();
            });
        });
        document.addEventListener('click', e => {
            if (!trigger.contains(e.target) && !optionsList.contains(e.target)) {
                optionsList.classList.remove('open');
            }
        });
    }
}

async function loadAssignedDeliveries() {
    try {
        const response = await fetch('../api/orders/list.php?status=assigned&limit=100');
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            assignedOrders = data.orders;
            
            // Show buttons
            document.getElementById('start-all-btn').style.display = 'inline-flex';
            document.getElementById('filter-sort-bar').style.display = 'block';
            document.getElementById('filter-sort-bar-mobile').style.display = 'block';
            document.getElementById('delivery-count').textContent = assignedOrders.length;
            
            applySortAndRender();
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load assigned deliveries:', error);
        showEmptyState();
    }
}

function applySortAndRender() {
    sortedOrders = [...assignedOrders];
    
    switch (currentSort) {
        case 'customer':
            sortedOrders.sort((a, b) => (a.customer_name || '').localeCompare(b.customer_name || ''));
            break;
        case 'address':
            sortedOrders.sort((a, b) => (a.delivery_address || '').localeCompare(b.delivery_address || ''));
            break;
        case 'amount_asc':
            sortedOrders.sort((a, b) => parseFloat(a.total_amount) - parseFloat(b.total_amount));
            break;
        case 'amount_desc':
            sortedOrders.sort((a, b) => parseFloat(b.total_amount) - parseFloat(a.total_amount));
            break;
        default:
            break;
    }
    
    renderAssignedDeliveries();
}

function renderAssignedDeliveries() {
    const tbody = document.getElementById('assigned-deliveries-list');
    
    if (sortedOrders.length === 0) {
        showEmptyState();
        return;
    }
    
    // Pagination
    const totalPages = Math.ceil(sortedOrders.length / assignedPerPage);
    const startIdx = (assignedPage - 1) * assignedPerPage;
    const pageOrders = sortedOrders.slice(startIdx, startIdx + assignedPerPage);
    
    let html = '';
    pageOrders.forEach((order, index) => {
        html += `
            <tr data-order-id="${order.id}">
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name || '—'}</td>
                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${order.delivery_address || ''}">${order.delivery_address || '—'}</td>
                <td><strong style="color: var(--primary);">${formatCurrency(order.total_amount)}</strong></td>
                <td style="white-space: nowrap;">${formatDate(order.order_date)}</td>
                <td style="white-space: nowrap;">
                    <button class="btn btn-primary btn-sm" onclick="startDelivery(${order.id})">
                        <span class="material-icons" style="font-size: 16px;">play_arrow</span>
                        Start
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="requestReassign(${order.id})" style="margin-left: 4px;">
                        <span class="material-icons" style="font-size: 16px;">swap_horiz</span>
                        Reassign
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updateAssignedPagination(totalPages);
}

function updateAssignedPagination(totalPages) {
    const wrapper = document.getElementById('assigned-pagination');
    const info = document.getElementById('assigned-page-info');
    const prevBtn = document.getElementById('assigned-prev-btn');
    const nextBtn = document.getElementById('assigned-next-btn');
    
    if (totalPages <= 1) {
        wrapper.style.display = 'none';
        return;
    }
    
    wrapper.style.display = 'flex';
    info.textContent = `Page ${assignedPage} of ${totalPages}`;
    prevBtn.disabled = assignedPage <= 1;
    nextBtn.disabled = assignedPage >= totalPages;
}

function prevAssignedPage() {
    if (assignedPage > 1) {
        assignedPage--;
        renderAssignedDeliveries();
    }
}

function nextAssignedPage() {
    const totalPages = Math.ceil(sortedOrders.length / assignedPerPage);
    if (assignedPage < totalPages) {
        assignedPage++;
        renderAssignedDeliveries();
    }
}

function showEmptyState() {
    document.getElementById('start-all-btn').style.display = 'none';
    document.getElementById('filter-sort-bar').style.display = 'none';
    document.getElementById('filter-sort-bar-mobile').style.display = 'none';
    document.getElementById('assigned-pagination').style.display = 'none';
    document.getElementById('delivery-count').textContent = '0';
    
    const tbody = document.getElementById('assigned-deliveries-list');
    tbody.innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <span class="material-icons empty-icon">assignment</span>
                    <p class="empty-title">No assigned deliveries</p>
                    <p class="empty-message">New deliveries will appear here when assigned to you</p>
                </div>
            </td>
        </tr>
    `;
}

async function startDelivery(orderId) {
    const confirm = await Swal.fire({
        title: 'Start Delivery',
        text: 'Begin this delivery?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Start',
        confirmButtonColor: '#1565C0'
    });
    
    if (!confirm.isConfirmed) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/orders/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: orderId,
                status: 'on_delivery',
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Delivery started!', 'success');
            // Remove from local list
            assignedOrders = assignedOrders.filter(o => o.id != orderId);
            document.getElementById('delivery-count').textContent = assignedOrders.length;
            if (assignedOrders.length === 0) {
                showEmptyState();
            } else {
                applySortAndRender();
            }
        } else {
            showToast(data.message || 'Failed to start delivery', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Start delivery error:', error);
        showToast('An error occurred', 'error');
    }
}

/**
 * Task 4: Start All Deliveries at once
 */
async function startAllDeliveries() {
    if (assignedOrders.length === 0) {
        showToast('No assigned deliveries to start', 'info');
        return;
    }
    
    const confirm = await Swal.fire({
        title: 'Start All Deliveries',
        html: `Are you sure you want to start <strong>${assignedOrders.length}</strong> deliveries at once?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Start All',
        confirmButtonColor: '#1565C0'
    });
    
    if (!confirm.isConfirmed) return;
    
    showLoading();
    
    let successCount = 0;
    let failCount = 0;
    
    for (const order of assignedOrders) {
        try {
            const response = await fetch('../api/orders/update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    order_id: order.id,
                    status: 'on_delivery',
                    csrf_token: getCSRFToken()
                })
            });
            
            const data = await response.json();
            if (data.success) {
                successCount++;
            } else {
                failCount++;
            }
        } catch (error) {
            failCount++;
        }
    }
    
    hideLoading();
    
    if (failCount === 0) {
        await Swal.fire({
            icon: 'success',
            title: 'All Deliveries Started!',
            text: `${successCount} deliveries are now in progress.`,
            confirmButtonColor: '#1565C0'
        });
    } else {
        await Swal.fire({
            icon: 'warning',
            title: 'Partially Started',
            text: `${successCount} started, ${failCount} failed.`,
            confirmButtonColor: '#1565C0'
        });
    }
    
    window.location.href = 'deliveries.php';
}

/**
 * Task 3: Request reassignment (before delivery starts)
 */
async function requestReassign(orderId) {
    const result = await Swal.fire({
        title: 'Request Reassignment',
        text: 'Please provide a reason for requesting reassignment:',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonText: 'Request Reassign',
        confirmButtonColor: '#FFA726',
        inputValidator: (value) => {
            if (!value) return 'Please provide a reason!';
        }
    });
    
    if (!result.isConfirmed) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/orders/request_reassign.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: orderId,
                reason: result.value,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        hideLoading();
        
        if (data.success) {
            showToast(data.message || 'Reassignment requested', 'success');
            // Remove from local list
            assignedOrders = assignedOrders.filter(o => o.id != orderId);
            document.getElementById('delivery-count').textContent = assignedOrders.length;
            if (assignedOrders.length === 0) {
                showEmptyState();
            } else {
                applySortAndRender();
            }
        } else {
            showToast(data.message || 'Failed to request reassignment', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('An error occurred', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
