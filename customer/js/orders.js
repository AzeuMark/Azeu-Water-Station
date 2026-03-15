/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER ORDERS JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Orders page logic for customers
 * Functions: List orders, view details, cancel, confirm delivery
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

let currentFilter = '';
let allOrders = [];
let currentPage = 1;
let itemsPerPage = window.innerWidth <= 1024 ? 10 : 20;
let sortCol = 'order_date';
let sortDir = 'desc';

function getItemsPerPage() {
    return window.innerWidth <= 1024 ? 10 : 20;
}

document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    initFilterButtons();
    initSortHeaders();
    
    // Check if specific order ID in URL
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id');
    if (orderId) {
        viewOrderDetails(parseInt(orderId));
    }

    window.addEventListener('resize', function() {
        const newPerPage = getItemsPerPage();
        if (newPerPage !== itemsPerPage) {
            itemsPerPage = newPerPage;
            currentPage = 1;
        }
        if (allOrders.length > 0) {
            sortOrders();
        }
    });
});

/**
 * Initialize filter buttons
 */
function initFilterButtons() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.status;
            currentPage = 1;
            loadOrders();
        });
    });

    // Mobile custom-select
    const trigger = document.getElementById('mobile-filter-trigger');
    const optionsList = document.getElementById('mobile-filter-options');
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
                currentFilter = this.dataset.status;
                currentPage = 1;
                // Keep desktop filter buttons in sync
                filterBtns.forEach(b => {
                    b.classList.toggle('active', b.dataset.status === currentFilter);
                });
                loadOrders();
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
 * Load orders
 */
async function loadOrders() {
    try {
        let url = '../api/orders/list.php?limit=100';
        if (currentFilter) {
            url += `&status=${currentFilter}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            allOrders = data.orders;
            sortOrders();
        } else {
            allOrders = [];
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
        allOrders = [];
        showEmptyState();
    }
}

/**
 * Sort stored orders and re-render
 */
function sortOrders() {
    const sorted = [...allOrders].sort((a, b) => {
        let valA = a[sortCol], valB = b[sortCol];
        if (sortCol === 'total_amount' || sortCol === 'id') {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else {
            valA = (valA || '').toString().toLowerCase();
            valB = (valB || '').toString().toLowerCase();
        }
        if (valA < valB) return sortDir === 'asc' ? -1 : 1;
        if (valA > valB) return sortDir === 'asc' ? 1 : -1;
        return 0;
    });
    renderOrders(sorted);
    updateSortIcons();
}

/**
 * Initialize sortable column header click handlers
 */
function initSortHeaders() {
    document.querySelectorAll('.sortable-th').forEach(th => {
        th.addEventListener('click', function() {
            const col = this.dataset.col;
            if (sortCol === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortCol = col;
                sortDir = 'asc';
            }
            sortOrders();
        });
    });
}

/**
 * Update sort icon states
 */
function updateSortIcons() {
    document.querySelectorAll('.sortable-th').forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (!icon) return;
        th.classList.remove('th-sorted');
        if (th.dataset.col === sortCol) {
            th.classList.add('th-sorted');
            icon.textContent = sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward';
        } else {
            icon.textContent = 'unfold_more';
        }
    });
}

/**
 * Build action buttons for an order
 */
function getOrderActions(order) {
    let actions = `
        <button class="btn-icon" onclick="viewOrderDetails(${order.id})" title="View Details">
            <span class="material-icons">visibility</span>
        </button>
    `;
    
    if (order.status === 'pending') {
        actions += `
            <button class="btn-icon" onclick="cancelOrder(${order.id})" title="Cancel Order" style="color: var(--danger);">
                <span class="material-icons">cancel</span>
            </button>
        `;
    }
    
    if ((order.status === 'delivered' || order.status === 'picked_up') && order.customer_confirmed == 0) {
        actions += `
            <button class="btn-icon" onclick="confirmDelivery(${order.id})" title="Confirm Receipt" style="color: var(--success);">
                <span class="material-icons">check_circle</span>
            </button>
        `;
    }
    
    return actions;
}

/**
 * Render orders table
 */
function renderOrders(orders) {
    const tbody = document.getElementById('orders-tbody');
    const cardsContainer = document.getElementById('orders-cards');
    
    if (orders.length === 0) {
        showEmptyState();
        updatePaginationControls(0);
        return;
    }
    
    // Pagination
    const totalPages = Math.ceil(orders.length / itemsPerPage);
    if (currentPage > totalPages) currentPage = totalPages;
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedOrders = orders.slice(startIndex, endIndex);
    
    let html = '';
    
    paginatedOrders.forEach((order, index) => {
        const rowNumber = startIndex + index + 1;
        const actions = getOrderActions(order);
        
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-muted);">${rowNumber}</td>
                <td><strong>#${order.id}</strong></td>
                <td>${formatDate(order.order_date)}</td>
                <td>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</td>
                <td>${order.payment_type.toUpperCase()}</td>
                <td><strong>${formatCurrency(order.total_amount)}</strong></td>
                <td>
                    <span class="badge badge-${order.status}">
                        ${getStatusLabel(order.status)}
                    </span>
                </td>
                <td style="white-space: nowrap;">
                    ${actions}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updatePaginationControls(totalPages);

    // Card view
    if (cardsContainer) {
        renderOrderCards(paginatedOrders, cardsContainer, startIndex);
    }
}

/**
 * Render mobile/tablet card view
 */
function renderOrderCards(orders, container, startIndex = 0) {
    let cardsHtml = '<div class="order-cards-grid">';
    orders.forEach((order, index) => {
        const cardNumber = startIndex + index + 1;
        const actions = getOrderActions(order);
        cardsHtml += `
            <div class="order-card">
                <div class="order-card-header">
                    <div class="order-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    <div class="order-card-actions">
                        ${actions}
                    </div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">receipt</span> Order ID</div>
                    <div class="order-card-value"><strong>#${order.id}</strong></div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">calendar_today</span> Date</div>
                    <div class="order-card-value">${formatDate(order.order_date)}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">local_shipping</span> Type</div>
                    <div class="order-card-value">${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">credit_card</span> Payment</div>
                    <div class="order-card-value">${order.payment_type.toUpperCase()}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">payments</span> Total</div>
                    <div class="order-card-value total-highlight">${formatCurrency(order.total_amount)}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">info</span> Status</div>
                    <div class="order-card-value"><span class="badge badge-${order.status}">${getStatusLabel(order.status)}</span></div>
                </div>
            </div>
        `;
    });
    cardsHtml += '</div>';
    container.innerHTML = cardsHtml;
}

/**
 * Show empty state
 */
function showEmptyState() {
    const tbody = document.getElementById('orders-tbody');
    const cardsContainer = document.getElementById('orders-cards');
    const emptyMessage = currentFilter ? 'No orders with this status' : 'You haven\'t placed any orders yet';
    const placeOrderBtn = !currentFilter ? '<a href="place_order.php" class="btn btn-primary" style="margin-top: 16px;"><span class="material-icons">add_shopping_cart</span> Place Order</a>' : '';

    tbody.innerHTML = `
        <tr>
            <td colspan="8">
                <div class="empty-state">
                    <span class="material-icons empty-icon">inbox</span>
                    <p class="empty-title">No orders found</p>
                    <p class="empty-message">${emptyMessage}</p>
                    ${placeOrderBtn}
                </div>
            </td>
        </tr>
    `;

    if (cardsContainer) {
        cardsContainer.innerHTML = `
            <div class="order-cards-empty">
                <span class="material-icons">inbox</span>
                <p>${emptyMessage}</p>
                ${placeOrderBtn}
            </div>
        `;
    }
}

/**
 * Update pagination controls
 */
function updatePaginationControls(totalPages) {
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const paginationWrapper = document.getElementById('pagination-wrapper');

    const pageInfoMobile = document.getElementById('page-info-mobile');
    const prevBtnMobile = document.getElementById('prev-btn-mobile');
    const nextBtnMobile = document.getElementById('next-btn-mobile');
    const paginationWrapperMobile = document.getElementById('pagination-wrapper-mobile');

    if (!pageInfo) return;

    // Hide pagination if only 1 page or no pages
    if (totalPages <= 1) {
        if (paginationWrapper) paginationWrapper.classList.remove('active');
        if (paginationWrapperMobile) paginationWrapperMobile.classList.remove('active');
        return;
    }

    // Show only the correct one for current viewport
    if (window.innerWidth <= 1024) {
        if (paginationWrapper) paginationWrapper.classList.remove('active');
        if (paginationWrapperMobile) paginationWrapperMobile.classList.add('active');
    } else {
        if (paginationWrapper) paginationWrapper.classList.add('active');
        if (paginationWrapperMobile) paginationWrapperMobile.classList.remove('active');
    }

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
        sortOrders();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allOrders.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        sortOrders();
    }
}

/**
 * View order details
 */
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`../api/orders/get.php?id=${orderId}`);
        const data = await response.json();
        
        if (data.success) {
            showOrderDetailsModal(data.order, data.items);
        } else {
            showToast('Failed to load order details', 'error');
        }
    } catch (error) {
        console.error('Failed to load order details:', error);
        showToast('An error occurred', 'error');
    }
}

/**
 * Show order details modal
 */
function showOrderDetailsModal(order, items) {
    const content = document.getElementById('order-details-content');
    const actions = document.getElementById('order-details-actions');
    
    const statusLabel = getStatusLabel(order.status);
    const typeIcon = order.delivery_type === 'delivery' ? 'local_shipping' : 'storefront';
    const typeLabel = order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup';
    const paymentLabels = { 'cod': 'Cash on Delivery', 'pickup': 'Pay on Pickup', 'online': 'Online Payment' };
    const paymentLabel = paymentLabels[order.payment_type] || order.payment_type || '—';
    
    let html = `
        <!-- Order Header Banner -->
        <div class="odm-header-banner">
            <div class="odm-order-id">
                <span class="material-icons">receipt</span>
                Order #${order.id}
            </div>
            <span class="badge badge-${order.status}">${statusLabel}</span>
        </div>
        
        <!-- Info Cards Grid -->
        <div class="odm-info-grid">
            <div class="odm-info-card">
                <div class="odm-info-icon" style="background: rgba(255,152,0,0.1); color: #FF9800;">
                    <span class="material-icons">calendar_today</span>
                </div>
                <div class="odm-info-content">
                    <span class="odm-info-label">Date</span>
                    <span class="odm-info-value">${formatDate(order.order_date)}</span>
                </div>
            </div>
            <div class="odm-info-card">
                <div class="odm-info-icon" style="background: rgba(171,71,188,0.1); color: #AB47BC;">
                    <span class="material-icons">${typeIcon}</span>
                </div>
                <div class="odm-info-content">
                    <span class="odm-info-label">Type</span>
                    <span class="odm-info-value">${typeLabel}</span>
                </div>
            </div>
            <div class="odm-info-card">
                <div class="odm-info-icon" style="background: rgba(102,187,106,0.1); color: #66BB6A;">
                    <span class="material-icons">payments</span>
                </div>
                <div class="odm-info-content">
                    <span class="odm-info-label">Payment</span>
                    <span class="odm-info-value">${paymentLabel}</span>
                </div>
            </div>
            <div class="odm-info-card">
                <div class="odm-info-icon" style="background: rgba(21,101,192,0.1); color: var(--primary);">
                    <span class="material-icons">shopping_bag</span>
                </div>
                <div class="odm-info-content">
                    <span class="odm-info-label">Items</span>
                    <span class="odm-info-value">${items.length} item${items.length !== 1 ? 's' : ''}</span>
                </div>
            </div>
        </div>
        
        ${order.delivery_type === 'delivery' ? `
        <!-- Rider & Address -->
        <div class="odm-detail-rows">
            <div class="odm-detail-row">
                <span class="material-icons" style="color: var(--primary); font-size: 18px;">two_wheeler</span>
                <span class="odm-detail-label">Rider</span>
                <span class="odm-detail-value">${order.rider_name ? `${order.rider_name}${order.rider_phone ? ' — ' + order.rider_phone : ''}` : '<span style="color:var(--text-muted);font-style:italic;">Not assigned</span>'}</span>
            </div>
            ${order.delivery_address ? `
            <div class="odm-detail-row">
                <span class="material-icons" style="color: var(--danger); font-size: 18px;">location_on</span>
                <span class="odm-detail-label">Address</span>
                <span class="odm-detail-value">${order.delivery_address}</span>
            </div>` : ''}
        </div>` : ''}
        
        <!-- Items Section -->
        <div class="odm-section">
            <div class="odm-section-title">
                <span class="material-icons">shopping_bag</span>
                Order Items
            </div>
            <div class="odm-items-list">
                ${items.map(item => `
                <div class="odm-item">
                    <div class="odm-item-info">
                        <span class="odm-item-name">${item.item_name}</span>
                        <span class="odm-item-meta">${item.quantity} × ${formatCurrency(item.item_price)}</span>
                    </div>
                    <span class="odm-item-amount">${formatCurrency(item.subtotal)}</span>
                </div>`).join('')}
            </div>
        </div>
        
        <!-- Totals -->
        <div class="odm-totals">
            <div class="odm-total-row">
                <span>Subtotal</span>
                <span>${formatCurrency(order.subtotal)}</span>
            </div>
            ${order.delivery_fee > 0 ? `
            <div class="odm-total-row">
                <span>Delivery Fee</span>
                <span>${formatCurrency(order.delivery_fee)}</span>
            </div>` : ''}
            <div class="odm-total-row odm-grand-total">
                <span>Total</span>
                <span>${formatCurrency(order.total_amount)}</span>
            </div>
        </div>
        
        ${order.status === 'cancelled' && order.cancellation_reason ? `
        <div class="odm-cancel-reason">
            <div class="odm-cancel-title">
                <span class="material-icons">info</span>
                Cancellation Reason
            </div>
            <p>${order.cancellation_reason}</p>
        </div>` : ''}
    `;
    
    content.innerHTML = html;
    
    // Action buttons
    let actionsHtml = '<button class="btn btn-outline" onclick="closeModal(\'order-details-modal\')">Close</button>';
    
    // Cancel button (only for pending orders)
    if (order.status === 'pending') {
        actionsHtml = `
            <button class="btn btn-danger" onclick="cancelOrder(${order.id})">
                <span class="material-icons">cancel</span>
                Cancel Order
            </button>
        ` + actionsHtml;
    }
    
    // Confirm delivery button (for delivered/picked_up orders)
    if ((order.status === 'delivered' || order.status === 'picked_up') && order.customer_confirmed == 0) {
        actionsHtml = `
            <button class="btn btn-success" onclick="confirmDelivery(${order.id})">
                <span class="material-icons">check_circle</span>
                Confirm Receipt
            </button>
        ` + actionsHtml;
    }
    
    // View receipt button
    actionsHtml = `
        <button class="btn btn-primary" onclick="viewReceipt('${order.receipt_token}')">
            <span class="material-icons">receipt</span>
            View Receipt
        </button>
    ` + actionsHtml;
    
    actions.innerHTML = actionsHtml;
    
    openModal('order-details-modal');
}

/**
 * Cancel order
 */
async function cancelOrder(orderId) {
    const reason = await Swal.fire({
        title: 'Cancel Order',
        input: 'textarea',
        inputLabel: 'Reason for cancellation',
        inputPlaceholder: 'Please provide a reason...',
        showCancelButton: true,
        confirmButtonText: 'Cancel Order',
        confirmButtonColor: '#EF5350'
    });
    
    if (!reason.value) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/orders/cancel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: orderId,
                reason: reason.value,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Order cancelled successfully', 'success');
            closeModal('order-details-modal');
            loadOrders();
        } else {
            showToast(data.message || 'Failed to cancel order', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Cancel order error:', error);
        showToast('An error occurred', 'error');
    }
}

/**
 * Confirm delivery
 */
async function confirmDelivery(orderId) {
    const confirm = await Swal.fire({
        title: 'Confirm Receipt',
        text: 'Have you received your order in good condition?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Confirm',
        confirmButtonColor: '#66BB6A'
    });
    
    if (!confirm.isConfirmed) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/orders/confirm_delivery.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: orderId,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Order confirmed successfully!', 'success');
            closeModal('order-details-modal');
            loadOrders();
        } else {
            showToast(data.message || 'Failed to confirm order', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Confirm delivery error:', error);
        showToast('An error occurred', 'error');
    }
}

/**
 * View receipt
 */
function viewReceipt(token) {
    window.open(`../receipt.php?token=${token}`, '_blank');
}

/**
 * Get status label
 */
function getStatusLabel(status) {
    const labels = {
        'pending': 'Pending',
        'confirmed': 'Confirmed',
        'assigned': 'Assigned',
        'on_delivery': 'On Delivery',
        'delivered': 'Delivered',
        'accepted': 'Completed',
        'ready_for_pickup': 'Ready for Pickup',
        'picked_up': 'Picked Up',
        'cancelled': 'Cancelled'
    };
    return labels[status] || status;
}
