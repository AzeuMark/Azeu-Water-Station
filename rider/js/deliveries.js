/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DELIVERIES JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Active deliveries management logic
 * Functions: Load deliveries, update status, sort, filter, drag-to-reorder
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

let allDeliveryOrders = [];
let sortedDeliveryOrders = [];
let deliveriesPage = 1;
const deliveriesPerPage = 10;
let currentSort = 'priority';

document.addEventListener('DOMContentLoaded', function() {
    loadDeliveries();
    initSortButtons();
});

/**
 * Initialize sort buttons (desktop + mobile)
 */
function initSortButtons() {
    const sortBtns = document.querySelectorAll('[data-sort]');
    sortBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            sortBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.dataset.sort;
            deliveriesPage = 1;
            applySortAndRender();
        });
    });

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
                sortBtns.forEach(b => b.classList.toggle('active', b.dataset.sort === currentSort));
                deliveriesPage = 1;
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

/**
 * Apply current sort and render
 */
function applySortAndRender() {
    sortedDeliveryOrders = [...allDeliveryOrders];
    switch (currentSort) {
        case 'nearest':
            sortedDeliveryOrders.sort((a, b) => (a.delivery_address || '').localeCompare(b.delivery_address || ''));
            break;
        case 'customer':
            sortedDeliveryOrders.sort((a, b) => (a.customer_name || '').localeCompare(b.customer_name || ''));
            break;
        case 'amount_asc':
            sortedDeliveryOrders.sort((a, b) => parseFloat(a.total_amount) - parseFloat(b.total_amount));
            break;
        case 'amount_desc':
            sortedDeliveryOrders.sort((a, b) => parseFloat(b.total_amount) - parseFloat(a.total_amount));
            break;
        case 'group_area':
            renderGroupedByArea(allDeliveryOrders);
            return;
        case 'priority':
        default:
            // Keep server order
            break;
    }
    renderDeliveries(sortedDeliveryOrders);
}

// ---- Address similarity helpers ----

// Words to ignore when tokenizing Philippine addresses
const ADDRESS_NOISE_WORDS = new Set([
    'st', 'str', 'street', 'ave', 'avenue', 'blvd', 'boulevard', 'rd', 'road',
    'no', 'num', '#', 'lot', 'block', 'blk', 'unit', 'floor', 'flr',
    'and', 'the', 'of', 'at', 'in', 'near', 'beside', 'behind', 'front',
    'ph', 'phase', 'bldg', 'building', 'compound', 'subdivision', 'subd',
    '1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
]);

/**
 * Tokenize an address into significant location words.
 * Strips punctuation, lowercases, filters noise.
 */
function tokenizeAddress(address) {
    if (!address) return [];
    return address
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, ' ')
        .split(/\s+/)
        .filter(w => w.length > 2 && !ADDRESS_NOISE_WORDS.has(w));
}

/**
 * Detect Philippine barangay/purok/sitio keyword in an address.
 * Returns the token immediately after brgy/barangay/purok/sitio if found,
 * otherwise returns the most-shared token across all orders.
 */
function detectAreaKey(address, globalFreqMap) {
    const lower = (address || '').toLowerCase();
    // Try to extract named barangay/purok/sitio
    const patterns = [
        /(?:barangay|brgy\.?|bgy\.?|purok|sitio|sto\.?|sta\.?)\s+([a-z0-9]+(?:\s+[a-z0-9]+)?)/i
    ];
    for (const re of patterns) {
        const m = lower.match(re);
        if (m && m[1] && m[1].trim().length > 1) {
            return m[1].trim().replace(/\s+/g, ' ');
        }
    }
    // Fall back to the highest-frequency token in this address
    const tokens = tokenizeAddress(address);
    let bestToken = null, bestFreq = 0;
    for (const t of tokens) {
        const freq = globalFreqMap.get(t) || 0;
        if (freq > bestFreq) { bestFreq = freq; bestToken = t; }
    }
    return bestToken || 'Other Area';
}

/**
 * Build a global token frequency map across all delivery addresses.
 */
function buildFreqMap(orders) {
    const map = new Map();
    for (const o of orders) {
        const tokens = new Set(tokenizeAddress(o.delivery_address)); // unique per order
        for (const t of tokens) map.set(t, (map.get(t) || 0) + 1);
    }
    return map;
}

/**
 * Group orders by detected area key.
 * Groups with more orders come first; within each group orders keep original order.
 */
function groupOrdersByArea(orders) {
    const freqMap = buildFreqMap(orders);
    const groups = new Map(); // key → { label, orders[] }

    for (const o of orders) {
        const key = detectAreaKey(o.delivery_address, freqMap);
        // Capitalise the key nicely
        const label = key.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        if (!groups.has(key)) groups.set(key, { label, orders: [] });
        groups.get(key).orders.push(o);
    }

    // Sort groups: most orders first, then alphabetically
    return Array.from(groups.values()).sort((a, b) =>
        b.orders.length - a.orders.length || a.label.localeCompare(b.label)
    );
}

/**
 * Render deliveries grouped by similar area (no pagination — all visible)
 */
function renderGroupedByArea(orders) {
    const container = document.getElementById('deliveries-container');
    if (!container) return;

    if (!orders || orders.length === 0) {
        showEmptyState();
        return;
    }

    const dragHint = document.getElementById('drag-hint');
    if (dragHint) dragHint.style.display = 'none';

    const groups = groupOrdersByArea(orders);
    let html = '';

    groups.forEach(group => {
        html += `
            <div class="area-group-header">
                <span class="material-icons">location_on</span>
                <span class="area-group-title">${group.label}</span>
                <span class="area-group-count">${group.orders.length} ${group.orders.length === 1 ? 'delivery' : 'deliveries'}</span>
            </div>
        `;
        group.orders.forEach(order => {
            html += buildDeliveryCardHtml(order);
        });
    });

    container.innerHTML = html;
    // Hide pagination in grouped view
    const pagination = document.getElementById('deliveries-pagination');
    if (pagination) pagination.style.display = 'none';
}

/**
 * Load deliveries
 */
async function loadDeliveries() {
    try {
        let url = '../api/orders/list.php?limit=100&status=on_delivery';

        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            allDeliveryOrders = data.orders;
            deliveriesPage = 1;
            const count = document.getElementById('delivery-count');
            if (count) count.textContent = allDeliveryOrders.length;
            applySortAndRender();
        } else {
            allDeliveryOrders = [];
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load deliveries:', error);
        showEmptyState();
    }
}

/**
 * Build HTML for a single delivery card.
 * @param {object} order
 * @param {number|null} priority  - Pass a number to show priority label, null to hide it
 * @param {boolean} draggable     - Whether to include drag handle
 */
function buildDeliveryCardHtml(order, priority = null, draggable = false) {
    const isActive = order.status === 'on_delivery';
    const priorityLabel = priority !== null
        ? `<span style="color: var(--primary); margin-right: 6px;">#${priority}</span>` : '';
    const dragHandle = draggable
        ? `<div class="drag-handle" title="Drag to reorder"><span class="material-icons">drag_indicator</span></div>` : '';
    return `
        <div class="delivery-card${draggable ? ' sortable-item' : ''}" data-order-id="${order.id}">
            ${dragHandle}
            <div class="delivery-card-inner">
                <div class="delivery-card-header">
                    <div>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">
                            ${priorityLabel}Order #${order.id}
                        </div>
                        <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">${formatDate(order.order_date)}</div>
                    </div>
                    <span class="badge badge-${order.status}">${getStatusLabel(order.status)}</span>
                </div>
                <div class="delivery-card-body">
                    <div class="delivery-info-item">
                        <span class="material-icons">person</span>
                        <div>
                            <div class="delivery-info-label">Customer</div>
                            <div class="delivery-info-value">${order.customer_name || '—'}</div>
                        </div>
                    </div>
                    <div class="delivery-info-item">
                        <span class="material-icons">phone</span>
                        <div>
                            <div class="delivery-info-label">Phone</div>
                            <div class="delivery-info-value">${order.customer_phone || '—'}</div>
                        </div>
                    </div>
                    <div class="delivery-info-item">
                        <span class="material-icons">location_on</span>
                        <div>
                            <div class="delivery-info-label">Address</div>
                            <div class="delivery-info-value">${order.delivery_address || '—'}</div>
                        </div>
                    </div>
                    <div class="delivery-info-item">
                        <span class="material-icons">payments</span>
                        <div>
                            <div class="delivery-info-label">Amount</div>
                            <div class="delivery-info-value" style="color: var(--primary);">${formatCurrency(order.total_amount)}</div>
                        </div>
                    </div>
                    <div class="delivery-info-item">
                        <span class="material-icons">payment</span>
                        <div>
                            <div class="delivery-info-label">Payment</div>
                            <div class="delivery-info-value">${(order.payment_type || '').toUpperCase()}</div>
                        </div>
                    </div>
                    ${order.notes ? `
                    <div class="delivery-info-item">
                        <span class="material-icons">notes</span>
                        <div>
                            <div class="delivery-info-label">Notes</div>
                            <div class="delivery-info-value">${order.notes}</div>
                        </div>
                    </div>` : ''}
                </div>
                ${isActive ? `
                <div class="delivery-card-footer">
                    <button class="btn btn-success" onclick="markAsDelivered(${order.id})">
                        <span class="material-icons">check_circle</span>
                        Mark as Delivered
                    </button>
                    <button class="btn btn-warning" onclick="requestReassign(${order.id})">
                        <span class="material-icons">swap_horiz</span>
                        Request Reassign
                    </button>
                    <button class="btn btn-danger" onclick="cancelOrder(${order.id})">
                        <span class="material-icons">cancel</span>
                        Cancel Order
                    </button>
                </div>` : ''}
            </div>
        </div>
    `;
}

/**
 * Render deliveries as draggable cards with pagination
 */
function renderDeliveries(orders) {
    const container = document.getElementById('deliveries-container');
    if (!container) return;
    
    if (!orders || orders.length === 0) {
        showEmptyState();
        return;
    }

    // Show drag hint only when showing on_delivery (active)
    const dragHint = document.getElementById('drag-hint');
    if (dragHint) dragHint.style.display = 'block';
    
    const totalPages = Math.ceil(orders.length / deliveriesPerPage);
    const startIdx = (deliveriesPage - 1) * deliveriesPerPage;
    const pageOrders = orders.slice(startIdx, startIdx + deliveriesPerPage);
    
    let html = '<div id="sortable-delivery-list">';
    pageOrders.forEach((order, index) => {
        html += buildDeliveryCardHtml(order, startIdx + index + 1, true);
    });
    html += '</div>';
    
    container.innerHTML = html;
    updateDeliveriesPagination(totalPages);
    initDeliveriesSortable();
}

/**
 * Initialize Sortable.js drag-to-reorder on delivery cards
 */
function initDeliveriesSortable() {
    const list = document.getElementById('sortable-delivery-list');
    if (!list || typeof Sortable === 'undefined') return;
    // Only allow drag when sort is priority mode
    if (currentSort !== 'priority') return;
    Sortable.create(list, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: saveDeliveryPriority
    });
}

/**
 * Save reordered priority to server
 */
async function saveDeliveryPriority() {
    const items = document.querySelectorAll('#sortable-delivery-list .sortable-item');
    const priorities = [];
    items.forEach((item, index) => {
        // Update priority number label
        const lbl = item.querySelector('.delivery-card-inner .delivery-card-header div div:first-child span');
        if (lbl) lbl.textContent = '#' + (index + 1);
        priorities.push({ order_id: parseInt(item.dataset.orderId), priority: index + 1 });
    });
    try {
        await fetch('../api/riders/update_priority.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ priorities, csrf_token: getCSRFToken() })
        });
    } catch (error) {
        console.error('Failed to save priority:', error);
    }
}

function updateDeliveriesPagination(totalPages) {
    const wrapper = document.getElementById('deliveries-pagination');
    if (!wrapper) return;
    const info = document.getElementById('del-page-info');
    const prevBtn = document.getElementById('del-prev-btn');
    const nextBtn = document.getElementById('del-next-btn');
    if (totalPages <= 1) { wrapper.style.display = 'none'; return; }
    wrapper.style.display = 'flex';
    info.textContent = `Page ${deliveriesPage} of ${totalPages}`;
    prevBtn.disabled = deliveriesPage <= 1;
    nextBtn.disabled = deliveriesPage >= totalPages;
}

function prevDeliveriesPage() {
    if (deliveriesPage > 1) { deliveriesPage--; renderDeliveries(sortedDeliveryOrders); }
}

function nextDeliveriesPage() {
    const totalPages = Math.ceil(sortedDeliveryOrders.length / deliveriesPerPage);
    if (deliveriesPage < totalPages) { deliveriesPage++; renderDeliveries(sortedDeliveryOrders); }
}

/**
 * Show empty state
 */
function showEmptyState() {
    const container = document.getElementById('deliveries-container');
    if (container) {
        container.innerHTML = `
            <div class="empty-state">
                <span class="material-icons empty-icon">local_shipping</span>
                <p class="empty-title">No deliveries found</p>
                <p class="empty-message">Your active deliveries will appear here</p>
            </div>
        `;
    }
    const count = document.getElementById('delivery-count');
    if (count) count.textContent = '0';
    const dragHint = document.getElementById('drag-hint');
    if (dragHint) dragHint.style.display = 'none';
    const pagination = document.getElementById('deliveries-pagination');
    if (pagination) pagination.style.display = 'none';
}

/**
 * Get status label
 */
function getStatusLabel(status) {
    const labels = {
        'pending': 'Pending', 'confirmed': 'Confirmed', 'assigned': 'Assigned',
        'on_delivery': 'On Delivery', 'delivered': 'Delivered',
        'ready_for_pickup': 'Ready for Pickup', 'picked_up': 'Picked Up', 'cancelled': 'Cancelled'
    };
    return labels[status] || status;
}

/**
 * Mark delivery as delivered
 */
async function markAsDelivered(orderId) {
    const confirm = await Swal.fire({
        title: 'Mark as Delivered',
        text: 'Confirm that this order has been delivered?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delivered',
        confirmButtonColor: '#66BB6A'
    });
    if (!confirm.isConfirmed) return;
    showLoading();
    try {
        const response = await fetch('../api/orders/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, status: 'delivered', csrf_token: getCSRFToken() })
        });
        const data = await response.json();
        hideLoading();
        if (data.success) {
            showToast('Order marked as delivered!', 'success');
            allDeliveryOrders = allDeliveryOrders.filter(o => o.id != orderId);
            const count = document.getElementById('delivery-count');
            if (count) count.textContent = allDeliveryOrders.length;
            applySortAndRender();
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('An error occurred', 'error');
    }
}

/**
 * Cancel an order with a reason
 */
async function cancelOrder(orderId) {
    const result = await Swal.fire({
        title: 'Cancel Order',
        text: 'Please provide a reason for cancelling this order:',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter cancellation reason...',
        showCancelButton: true,
        confirmButtonText: 'Cancel Order',
        confirmButtonColor: '#EF5350',
        cancelButtonText: 'Go Back',
        inputValidator: (value) => { if (!value || !value.trim()) return 'Please provide a reason!'; }
    });
    if (!result.isConfirmed) return;
    showLoading();
    try {
        const response = await fetch('../api/orders/cancel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, reason: result.value, csrf_token: getCSRFToken() })
        });
        const data = await response.json();
        hideLoading();
        if (data.success) {
            showToast('Order cancelled successfully', 'success');
            allDeliveryOrders = allDeliveryOrders.filter(o => o.id != orderId);
            const count = document.getElementById('delivery-count');
            if (count) count.textContent = allDeliveryOrders.length;
            applySortAndRender();
        } else {
            showToast(data.message || 'Failed to cancel order', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('An error occurred', 'error');
    }
}

/**
 * Request reassignment
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
        inputValidator: (value) => { if (!value) return 'Please provide a reason!'; }
    });
    if (!result.isConfirmed) return;
    showLoading();
    try {
        const response = await fetch('../api/orders/request_reassign.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, reason: result.value, csrf_token: getCSRFToken() })
        });
        const data = await response.json();
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Reassignment requested', 'success');
            allDeliveryOrders = allDeliveryOrders.filter(o => o.id != orderId);
            const count = document.getElementById('delivery-count');
            if (count) count.textContent = allDeliveryOrders.length;
            applySortAndRender();
        } else {
            showToast(data.message || 'Failed to request reassignment', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('An error occurred', 'error');
    }
}



