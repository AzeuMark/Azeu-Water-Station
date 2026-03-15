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
let deliveriesPerPage = getDeliveriesPerPage();
let currentSort = 'priority';

function getDeliveriesPerPage() {
    return window.innerWidth <= 1024 ? 5 : 10;
}

document.addEventListener('DOMContentLoaded', function() {
    loadDeliveries();
    initSortButtons();

    // Delegated listener for UP/DOWN reorder buttons (avoids Sortable.js touch capture)
    const container = document.getElementById('deliveries-container');
    if (container) {
        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.dcard-reorder-btn');
            if (!btn || btn.disabled) return;
            e.stopPropagation();
            const action = btn.dataset.action;
            const orderId = parseInt(btn.dataset.orderId, 10);
            if (action === 'reorder-up')   moveDeliveryCard(orderId, -1);
            else if (action === 'reorder-down') moveDeliveryCard(orderId, 1);
        });
    }

    window.addEventListener('resize', function() {
        const newPerPage = getDeliveriesPerPage();
        if (newPerPage !== deliveriesPerPage) {
            deliveriesPerPage = newPerPage;
            deliveriesPage = 1;
            if (sortedDeliveryOrders.length > 0) applySortAndRender();
        }
    });
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
                sortBtns.forEach(b => b.classList.toggle('active', b.dataset.sort === currentSort));
                deliveriesPage = 1;
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
            const badge = document.getElementById('delivery-count-badge');
            if (badge) badge.style.display = 'inline-flex';
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
 * @param {number|null} priority    - Sequential number to show, null to hide
 * @param {boolean} draggable       - Whether to include drag handle + reorder buttons
 * @param {number} idxInPage        - 0-based index within current page (for disabling UP/DOWN)
 * @param {number} totalInPage      - Total cards on current page (for disabling UP/DOWN)
 */
function buildDeliveryCardHtml(order, priority = null, draggable = false, idxInPage = 0, totalInPage = 1) {
    const isActive = order.status === 'on_delivery';

    const dragHandleHtml = draggable
        ? `<div class="dcard-drag-handle" title="Drag to reorder">
               <span class="material-icons">drag_indicator</span>
           </div>` : '';

    const reorderBtnsHtml = draggable
        ? `<div class="dcard-reorder-btns">
               <button type="button" class="dcard-reorder-btn" data-action="reorder-up" data-order-id="${order.id}" title="Move up"${idxInPage === 0 ? ' disabled' : ''}>
                   <span class="material-icons">keyboard_arrow_up</span>
               </button>
               <button type="button" class="dcard-reorder-btn" data-action="reorder-down" data-order-id="${order.id}" title="Move down"${idxInPage === totalInPage - 1 ? ' disabled' : ''}>
                   <span class="material-icons">keyboard_arrow_down</span>
               </button>
           </div>` : '';

    const priorityHtml = priority !== null
        ? `<span class="dcard-priority">#${priority}</span>` : '';

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

    const itemCountLabel = order.item_count ? `${order.item_count} item${order.item_count > 1 ? 's' : ''}` : 'Items';

    const actionsHtml = isActive
        ? `<div class="dcard-actions">
               <button class="btn btn-success" onclick="markAsDelivered(${order.id})">
                   <span class="material-icons">check_circle</span>
                   <span class="btn-label">Mark Delivered</span>
               </button>
               <button class="btn btn-warning" onclick="requestReassign(${order.id})">
                   <span class="material-icons">swap_horiz</span>
                   <span class="btn-label">Reassign</span>
               </button>
               <button class="btn btn-danger" onclick="cancelOrder(${order.id})">
                   <span class="material-icons">cancel</span>
                   <span class="btn-label">Cancel</span>
               </button>
           </div>` : '';

    return `
        <div class="delivery-card${draggable ? ' sortable-item' : ''}" data-order-id="${order.id}">

            <!-- Header: drag handle (desktop) | up/down (mobile) + priority + order # + date + status -->
            <div class="dcard-header">
                ${dragHandleHtml}
                ${reorderBtnsHtml}
                ${priorityHtml}
                <div class="dcard-header-info">
                    <span class="dcard-order-num">Order #${order.id}</span>
                    <span class="dcard-order-date">${formatDate(order.order_date)}</span>
                </div>
                <button class="btn btn-outline dcard-view-items-btn" onclick="toggleOrderItems(${order.id}, this)" style="flex-shrink:0;">
                    <span class="material-icons">receipt_long</span>
                    <span class="btn-label">${itemCountLabel}</span>
                </button>
            </div>

            <!-- Body wrapper: content left, actions right (desktop) -->
            <div class="dcard-body-wrapper">
                <div class="dcard-content">

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

                    <!-- Order Items (toggled via View Items button) -->
                    <div class="dcard-items-wrapper" id="dcard-items-${order.id}" style="display:none;">
                        <div class="dcard-items-box">
                            <div class="items-loading">Loading items...</div>
                        </div>
                    </div>
                </div>

                ${actionsHtml}
            </div>
        </div>
    `;
}

/**
 * Render deliveries as cards with pagination
 */
function renderDeliveries(orders) {
    const container = document.getElementById('deliveries-container');
    if (!container) return;
    
    if (!orders || orders.length === 0) {
        showEmptyState();
        return;
    }

    // Show drag hint on desktop priority mode only; hide on mobile (UP/DOWN is self-explanatory)
    const dragHint = document.getElementById('drag-hint');
    if (dragHint) {
        const isMobile = window.innerWidth <= 1024;
        const isPriority = currentSort === 'priority';
        dragHint.style.display = (!isMobile && isPriority) ? 'flex' : 'none';
    }
    
    const totalPages = Math.ceil(orders.length / deliveriesPerPage);
    const startIdx = (deliveriesPage - 1) * deliveriesPerPage;
    const pageOrders = orders.slice(startIdx, startIdx + deliveriesPerPage);
    
    let html = '<div id="sortable-delivery-list">';
    pageOrders.forEach((order, index) => {
        html += buildDeliveryCardHtml(order, startIdx + index + 1, true, index, pageOrders.length);
    });
    html += '</div>';
    
    container.innerHTML = html;
    updateDeliveriesPagination(totalPages);
    initDeliveriesSortable();
}

/**
 * Initialize Sortable.js drag-to-reorder — desktop only.
 * On mobile/tablet we use UP/DOWN buttons instead (touch events conflict with Sortable).
 */
function initDeliveriesSortable() {
    const list = document.getElementById('sortable-delivery-list');
    if (!list || typeof Sortable === 'undefined') return;
    if (currentSort !== 'priority') return;
    if (window.innerWidth <= 1024) return; // use UP/DOWN buttons on touch screens
    Sortable.create(list, {
        animation: 150,
        handle: '.dcard-drag-handle',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: saveDeliveryPriority
    });
}

/**
 * Save reordered priority to server
 */
async function saveDeliveryPriority() {
    // Sync sortedDeliveryOrders to match the DOM order after drag
    const items = document.querySelectorAll('#sortable-delivery-list .sortable-item');
    const startIdx = (deliveriesPage - 1) * deliveriesPerPage;
    items.forEach((item, index) => {
        const globalIdx = startIdx + index;
        const orderId = parseInt(item.dataset.orderId);
        const arrayIdx = sortedDeliveryOrders.findIndex(o => o.id === orderId);
        if (arrayIdx !== -1 && arrayIdx !== globalIdx) {
            const [moved] = sortedDeliveryOrders.splice(arrayIdx, 1);
            sortedDeliveryOrders.splice(globalIdx, 0, moved);
        }
        // Update priority label
        const lbl = item.querySelector('.dcard-priority');
        if (lbl) lbl.textContent = '#' + (globalIdx + 1);
    });
    // Re-render to refresh UP/DOWN disabled states
    renderDeliveries(sortedDeliveryOrders);
    savePriorityFromArray();
}

/**
 * Move a delivery card UP (-1) or DOWN (+1) in the sorted order.
 * Works across pagination pages by operating on the full sortedDeliveryOrders array.
 */
function moveDeliveryCard(orderId, direction) {
    const globalIdx = sortedDeliveryOrders.findIndex(o => o.id == orderId);
    if (globalIdx === -1) return;

    const swapIdx = globalIdx + direction;
    if (swapIdx < 0 || swapIdx >= sortedDeliveryOrders.length) return;

    const otherOrderId = sortedDeliveryOrders[swapIdx].id;

    // FLIP — First: record positions of both cards before re-render
    const cardA = document.querySelector(`.delivery-card[data-order-id="${orderId}"]`);
    const cardB = document.querySelector(`.delivery-card[data-order-id="${otherOrderId}"]`);
    const rectA = cardA ? cardA.getBoundingClientRect() : null;
    const rectB = cardB ? cardB.getBoundingClientRect() : null;

    // Swap in the full array
    [sortedDeliveryOrders[globalIdx], sortedDeliveryOrders[swapIdx]] =
        [sortedDeliveryOrders[swapIdx], sortedDeliveryOrders[globalIdx]];

    // If moving off current page, follow the card
    const startIdx = (deliveriesPage - 1) * deliveriesPerPage;
    const endIdx = startIdx + deliveriesPerPage;
    if (swapIdx < startIdx) deliveriesPage--;
    else if (swapIdx >= endIdx) deliveriesPage++;

    const label = direction === -1 ? 'moved up' : 'moved down';
    showToast(`Order #${orderId} ${label}`, 'info');

    renderDeliveries(sortedDeliveryOrders);
    savePriorityFromArray();

    // FLIP — Last + Invert + Play: animate both cards sliding to their new positions
    const flipAnimate = (el, fromRect) => {
        if (!el || !fromRect) return;
        const toRect = el.getBoundingClientRect();
        const deltaY = fromRect.top - toRect.top;
        if (Math.abs(deltaY) < 1) return;
        el.style.transition = 'none';
        el.style.transform = `translateY(${deltaY}px)`;
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                el.style.transition = 'transform 0.32s cubic-bezier(0.22, 1, 0.36, 1)';
                el.style.transform = 'translateY(0)';
                el.addEventListener('transitionend', () => {
                    el.style.transition = '';
                    el.style.transform = '';
                }, { once: true });
            });
        });
    };

    flipAnimate(document.querySelector(`.delivery-card[data-order-id="${orderId}"]`), rectA);
    flipAnimate(document.querySelector(`.delivery-card[data-order-id="${otherOrderId}"]`), rectB);
}

/**
 * Save priority based on current sortedDeliveryOrders array order.
 */
async function savePriorityFromArray() {
    const priorities = sortedDeliveryOrders.map((o, i) => ({ order_id: o.id, priority: i + 1 }));
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
                <p class="empty-title">No active deliveries</p>
                <p class="empty-message">Your active deliveries will appear here</p>
            </div>
        `;
    }
    const count = document.getElementById('delivery-count');
    if (count) count.textContent = '0';
    const badge = document.getElementById('delivery-count-badge');
    if (badge) badge.style.display = 'none';
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
            updateDeliveryCountBadge();
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
            updateDeliveryCountBadge();
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
            updateDeliveryCountBadge();
            applySortAndRender();
        } else {
            showToast(data.message || 'Failed to request reassignment', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('An error occurred', 'error');
    }
}

function updateDeliveryCountBadge() {
    const count = document.getElementById('delivery-count');
    const badge = document.getElementById('delivery-count-badge');
    if (count) count.textContent = allDeliveryOrders.length;
    if (badge) badge.style.display = allDeliveryOrders.length > 0 ? 'inline-flex' : 'none';
}

/**
 * Toggle order items visibility on a delivery card.
 * Fetches items on first open, then toggles display.
 */
const _loadedItems = {};
async function toggleOrderItems(orderId, btnEl) {
    const wrapper = document.getElementById(`dcard-items-${orderId}`);
    if (!wrapper) return;

    const isVisible = wrapper.style.display !== 'none';
    const label = btnEl ? btnEl.querySelector('.btn-label') : null;
    const order = allDeliveryOrders.find(o => o.id == orderId);
    const itemCountLabel = order && order.item_count ? `${order.item_count} item${order.item_count > 1 ? 's' : ''}` : 'Items';

    if (isVisible) {
        wrapper.style.display = 'none';
        if (btnEl) btnEl.classList.remove('active');
        if (label) label.textContent = itemCountLabel;
        return;
    }

    wrapper.style.display = 'block';
    if (btnEl) btnEl.classList.add('active');
    if (label) label.textContent = 'Collapse';

    // Only fetch once
    if (_loadedItems[orderId]) return;

    try {
        const response = await fetch(`../api/orders/get.php?id=${orderId}`);
        const data = await response.json();
        const box = wrapper.querySelector('.dcard-items-box');
        if (!box) return;

        if (data.success && data.items && data.items.length > 0) {
            let html = '';
            data.items.forEach((item, idx) => {
                html += `<div class="dcard-item-entry">
                    <span class="dcard-item-num">${idx + 1}.</span>
                    <span class="dcard-item-info">${item.item_name} × ${item.quantity}</span>
                    <span class="dcard-item-amount">${formatCurrency(item.subtotal)}</span>
                </div>`;
            });
            box.innerHTML = html;
            _loadedItems[orderId] = true;
        } else {
            box.innerHTML = '<div class="items-loading" style="color:var(--text-muted);">No items found</div>';
        }
    } catch (e) {
        const box = wrapper.querySelector('.dcard-items-box');
        if (box) box.innerHTML = '<div class="items-loading" style="color:var(--danger);">Failed to load items</div>';
    }
}
