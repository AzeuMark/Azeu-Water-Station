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
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Assigned Deliveries</h1>
            <button class="btn btn-primary" id="start-all-btn" onclick="startAllDeliveries()" style="display: none;">
                <span class="material-icons">play_circle</span>
                Start All Deliveries
            </button>
        </div>
    </div>
    
    <!-- Filter/Sort Bar -->
    <div class="glass-card" style="margin-bottom: 24px;" id="filter-sort-bar" style="display: none;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px;">
                <span class="material-icons" style="font-size: 20px;">sort</span>
                Sort by:
            </div>
            <button class="filter-btn active" data-sort="priority">Priority</button>
            <button class="filter-btn" data-sort="customer">Customer Name</button>
            <button class="filter-btn" data-sort="address">Address</button>
            <button class="filter-btn" data-sort="amount_asc">Amount ↑</button>
            <button class="filter-btn" data-sort="amount_desc">Amount ↓</button>
        </div>
    </div>
    
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Delivery Queue (<span id="delivery-count">0</span>)</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0;">
                <span class="material-icons" style="font-size: 18px; vertical-align: middle;">info</span>
                Drag to reorder priority
            </p>
        </div>
        
        <div id="assigned-deliveries-list">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
            </div>
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
let currentSort = 'priority';
let assignedPage = 1;
const assignedPerPage = 10;

document.addEventListener('DOMContentLoaded', function() {
    loadAssignedDeliveries();
    initSortButtons();
});

function initSortButtons() {
    document.querySelectorAll('[data-sort]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-sort]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.dataset.sort;
            assignedPage = 1;
            applySortAndRender();
        });
    });
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
            document.getElementById('delivery-count').textContent = assignedOrders.length;
            
            applySortAndRender();
            initSortable();
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
        case 'priority':
        default:
            // Keep original order (server order = priority)
            break;
    }
    
    renderAssignedDeliveries();
}

function renderAssignedDeliveries() {
    const container = document.getElementById('assigned-deliveries-list');
    
    if (sortedOrders.length === 0) {
        showEmptyState();
        return;
    }
    
    // Pagination
    const totalPages = Math.ceil(sortedOrders.length / assignedPerPage);
    const startIdx = (assignedPage - 1) * assignedPerPage;
    const pageOrders = sortedOrders.slice(startIdx, startIdx + assignedPerPage);
    
    let html = '<div id="sortable-list">';
    
    pageOrders.forEach((order, index) => {
        const globalIndex = startIdx + index;
        html += `
            <div class="delivery-card sortable-item" data-order-id="${order.id}">
                <div class="drag-handle">
                    <span class="material-icons">drag_indicator</span>
                </div>
                <div style="flex: 1;">
                    <div class="delivery-header">
                        <div>
                            <h4>Order #${order.id}</h4>
                            <p style="font-size: 0.9rem; color: var(--text-muted);">${order.customer_name}</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Priority</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">#${globalIndex + 1}</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 20px; margin-top: 12px;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Address</div>
                            <div style="font-size: 0.95rem;">${order.delivery_address || 'N/A'}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Amount</div>
                            <div style="font-weight: 700; color: var(--primary);">${formatCurrency(order.total_amount)}</div>
                        </div>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <button class="btn btn-primary" onclick="startDelivery(${order.id})" style="white-space: nowrap;">
                        <span class="material-icons">play_arrow</span>
                        Start
                    </button>
                    <button class="btn btn-warning" onclick="requestReassign(${order.id})" title="Request Reassignment" style="white-space: nowrap; font-size: 12px; padding: 6px 10px;">
                        <span class="material-icons" style="font-size: 16px;">swap_horiz</span>
                        Reassign
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Pagination controls
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
    document.getElementById('assigned-pagination').style.display = 'none';
    document.getElementById('delivery-count').textContent = '0';
    
    const container = document.getElementById('assigned-deliveries-list');
    container.innerHTML = `
        <div class="empty-state">
            <span class="material-icons empty-icon">assignment</span>
            <p class="empty-title">No assigned deliveries</p>
            <p class="empty-message">New deliveries will appear here when assigned to you</p>
        </div>
    `;
}

function initSortable() {
    const list = document.getElementById('sortable-list');
    if (!list || typeof Sortable === 'undefined') return;
    
    Sortable.create(list, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: savePriority
    });
}

async function savePriority() {
    const items = document.querySelectorAll('.sortable-item');
    const priorities = [];
    
    items.forEach((item, index) => {
        priorities.push({
            order_id: parseInt(item.dataset.orderId),
            priority: index + 1
        });
    });
    
    try {
        await fetch('../api/riders/update_priority.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                priorities: priorities,
                csrf_token: getCSRFToken()
            })
        });
        
        // Update priority numbers in UI
        items.forEach((item, index) => {
            const priorityNum = item.querySelector('.delivery-header > div:last-child > div:last-child');
            if (priorityNum) {
                priorityNum.textContent = '#' + (index + 1);
            }
        });
    } catch (error) {
        console.error('Failed to save priority:', error);
    }
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
