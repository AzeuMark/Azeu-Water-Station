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
 * - List all assigned deliveries as ticket cards (desktop + mobile)
 * - Request reassignment before starting delivery
 * - Start all deliveries at once
 * - Filter/sort by address, customer name, amount
 * - Pagination
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
    <div class="content-header" style="position: relative; z-index: 200; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="content-title">Assigned Deliveries</h1>
            <p class="content-breadcrumb">
                <span>Home</span>
                <span class="breadcrumb-separator">/</span>
                <span>Assigned Deliveries</span>
            </p>
        </div>
        <button class="btn btn-primary" id="start-all-btn" onclick="startAllDeliveries()" style="display: none; flex-shrink: 0;">
            <span class="material-icons">play_circle</span>
            Start All
        </button>
    </div>

    <!-- Desktop Sort Bar -->
    <div class="glass-card filter-bar-desktop" id="filter-sort-bar" style="margin-bottom: 24px; display: none;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">sort</span>
                    Sort by:
                </div>
                <button class="filter-btn active" data-sort="customer">
                    <span class="material-icons" style="font-size: 15px;">person</span> Customer Name
                </button>
                <button class="filter-btn" data-sort="address">
                    <span class="material-icons" style="font-size: 15px;">location_on</span> Address
                </button>
                <button class="filter-btn" data-sort="amount_asc">
                    <span class="material-icons" style="font-size: 15px;">arrow_upward</span> Amount ↑
                </button>
                <button class="filter-btn" data-sort="amount_desc">
                    <span class="material-icons" style="font-size: 15px;">arrow_downward</span> Amount ↓
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Sort Dropdown -->
    <div class="glass-card filter-bar-mobile" id="filter-sort-bar-mobile" style="margin-bottom: 24px; display: none;">
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

    <!-- Assigned Deliveries Cards Container -->
    <div id="assigned-container" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="text-align: center; padding: 60px;">
            <div class="spinner"></div>
        </div>
    </div>

    <!-- Pagination -->
    <div id="assigned-pagination" style="display: none; justify-content: center; align-items: center; padding: 20px; margin-top: 8px; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-icon" onclick="prevAssignedPage()" id="assigned-prev-btn" title="Previous">
                <span class="material-icons">chevron_left</span>
            </button>
            <span id="assigned-page-info" style="font-size: 14px; font-weight: 500; color: var(--text-primary); min-width: 100px; text-align: center;">Page 1 of 1</span>
            <button class="btn-icon" onclick="nextAssignedPage()" id="assigned-next-btn" title="Next">
                <span class="material-icons">chevron_right</span>
            </button>
        </div>
    </div>
</main>

<script>
let assignedOrders = [];
let sortedOrders = [];
let currentSort = 'customer';
let assignedPage = 1;
let assignedPerPage = getAssignedPerPage();

function getAssignedPerPage() {
    return window.innerWidth <= 1024 ? 5 : 10;
}

document.addEventListener('DOMContentLoaded', function() {
    loadAssignedDeliveries();
    initSortButtons();

    window.addEventListener('resize', function() {
        const newPerPage = getAssignedPerPage();
        if (newPerPage !== assignedPerPage) {
            assignedPerPage = newPerPage;
            assignedPage = 1;
            if (sortedOrders.length > 0) renderAssignedDeliveries();
        }
    });
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
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            trigger.classList.toggle('active');
            optionsList.classList.toggle('active');
        });
        optionsList.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.addEventListener('click', function() {
                optionsList.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                trigger.querySelector('.selected-text').textContent = this.textContent;
                trigger.classList.remove('active');
                optionsList.classList.remove('active');
                currentSort = this.dataset.sort;
                // Sync desktop buttons
                sortBtns.forEach(b => b.classList.toggle('active', b.dataset.sort === currentSort));
                assignedPage = 1;
                applySortAndRender();
            });
        });
        document.addEventListener('click', e => {
            if (!trigger.contains(e.target) && !optionsList.contains(e.target)) {
                trigger.classList.remove('active');
                optionsList.classList.remove('active');
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

            document.getElementById('start-all-btn').style.display = 'inline-flex';
            document.getElementById('filter-sort-bar').style.removeProperty('display');
            document.getElementById('filter-sort-bar-mobile').style.removeProperty('display');

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
    }

    renderAssignedDeliveries();
}

/**
 * Build HTML for a single assigned delivery ticket card.
 */
function buildAssignedCardHtml(order, cardNumber) {
    const phoneHtml = order.customer_phone
        ? `<a href="tel:${order.customer_phone}" class="dcard-phone-link">
               <span class="material-icons">phone</span>
               ${order.customer_phone}
           </a>`
        : `<span style="color:var(--text-muted); font-size:0.85rem;">No phone</span>`;

    const paymentText = (order.payment_type || '').replace(/_/g, ' ') || '—';

    const notesHtml = order.notes
        ? `<div class="dcard-notes">
               <span class="material-icons">notes</span>
               <span>${order.notes}</span>
           </div>` : '';

    return `
        <div class="delivery-card" data-order-id="${order.id}">

            <!-- Header: sequential number + date + status badge -->
            <div class="dcard-header">
                <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 15px; color: var(--text-primary); flex-shrink: 0;">
                    <span class="material-icons" style="font-size: 20px; color: var(--primary);">tag</span>
                    <span>${cardNumber}</span>
                </div>
                <div class="dcard-header-info">
                    <span class="dcard-order-date">${formatDate(order.order_date)}</span>
                </div>
                <span class="badge badge-assigned" style="flex-shrink:0;">Assigned</span>
            </div>

            <!-- Body: content left, actions right (desktop) / actions bottom (mobile) -->
            <div class="dcard-body-wrapper">
                <div class="dcard-content">

                    <!-- Order ID -->
                    <div style="display: flex; align-items: center; gap: 7px; font-size: 0.82rem; color: var(--text-muted);">
                        <span class="material-icons" style="font-size: 16px; color: var(--primary);">receipt</span>
                        <span>Order <strong style="color: var(--text-primary);">#${order.id}</strong></span>
                    </div>

                    <!-- Customer name + tappable phone -->
                    <div class="dcard-customer-row">
                        <div class="dcard-customer-name">
                            <span class="material-icons">person</span>
                            <span>${order.customer_name || '—'}</span>
                        </div>
                        ${phoneHtml}
                    </div>

                    <!-- Delivery address -->
                    <div class="dcard-address">
                        <span class="material-icons">location_on</span>
                        <span>${order.delivery_address || 'No address provided'}</span>
                    </div>

                    <!-- Payment type + amount -->
                    <div class="dcard-meta">
                        <div class="dcard-payment">
                            <span class="material-icons">payment</span>
                            <span>${paymentText}</span>
                        </div>
                        <div class="dcard-amount">${formatCurrency(order.total_amount)}</div>
                    </div>

                    ${notesHtml}
                </div>

                <!-- Actions panel -->
                <div class="dcard-actions">
                    <button class="btn btn-primary" onclick="startDelivery(${order.id})">
                        <span class="material-icons">play_arrow</span>
                        <span class="btn-label">Start</span>
                    </button>
                    <button class="btn btn-warning" onclick="requestReassign(${order.id})">
                        <span class="material-icons">swap_horiz</span>
                        <span class="btn-label">Reassign</span>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderAssignedDeliveries() {
    const container = document.getElementById('assigned-container');

    if (sortedOrders.length === 0) {
        showEmptyState();
        return;
    }

    const totalPages = Math.ceil(sortedOrders.length / assignedPerPage);
    const startIdx = (assignedPage - 1) * assignedPerPage;
    const pageOrders = sortedOrders.slice(startIdx, startIdx + assignedPerPage);

    let html = '';
    pageOrders.forEach((order, index) => {
        html += buildAssignedCardHtml(order, startIdx + index + 1);
    });

    container.innerHTML = html;
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

    document.getElementById('assigned-container').innerHTML = `
        <div class="empty-state">
            <span class="material-icons empty-icon">assignment</span>
            <p class="empty-title">No assigned deliveries</p>
            <p class="empty-message">New deliveries will appear here when assigned to you</p>
        </div>
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
            assignedOrders = assignedOrders.filter(o => o.id != orderId);
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
        showToast('An error occurred', 'error');
    }
}

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
            if (data.success) successCount++; else failCount++;
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
            assignedOrders = assignedOrders.filter(o => o.id != orderId);
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
