/**
 * ============================================================================
 * AZEU WATER STATION - STAFF ORDERS JAVASCRIPT
 * ============================================================================
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

let currentFilter = '';
let currentOrderId = null;
let allOrders = [];
let currentPage = 1;
let itemsPerPage = window.innerWidth <= 1024 ? 10 : 15;
let sortCol = 'order_date';
let sortDir = 'desc';

function getItemsPerPage() {
    return window.innerWidth <= 1024 ? 10 : 15;
}

document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    initFilterButtons();
    initSortHeaders();
    loadRiders();
    
    document.getElementById('assign-rider-form').addEventListener('submit', assignRider);
    document.getElementById('bulk-assign-rider-form').addEventListener('submit', assignRiderBulk);
    
    window.addEventListener('resize', function() {
        const newPerPage = getItemsPerPage();
        if (newPerPage !== itemsPerPage) {
            itemsPerPage = newPerPage;
            currentPage = 1;
        }
        if (allOrders.length > 0) {
            renderOrders();
        }
    });
});

function initSortHeaders() {
    document.querySelectorAll('.sortable-th').forEach(th => {
        th.addEventListener('click', function() {
            const col = this.dataset.col;
            if (sortCol === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortCol = col;
                sortDir = col === 'order_date' ? 'desc' : 'asc';
            }
            updateSortIcons();
            sortOrders();
            currentPage = 1;
            renderOrders();
        });
    });
    updateSortIcons();
}

function initFilterButtons() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.status;
            loadOrders();
        });
    });
}

async function loadOrders() {
    try {
        const url = currentFilter ? `../api/orders/list.php?status=${currentFilter}` : '../api/orders/list.php';
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            allOrders = data.orders;
            currentPage = 1;
            sortOrders();
            renderOrders();
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
    }
}

function sortOrders() {
    const statusOrder = { pending:1, confirmed:2, reassign_requested:3, assigned:4, on_delivery:5, delivered:6, accepted:7, ready_for_pickup:8, picked_up:9, cancelled:10 };
    allOrders.sort((a, b) => {
        let valA = a[sortCol] ?? '';
        let valB = b[sortCol] ?? '';
        if (sortCol === 'order_date') {
            valA = new Date(valA); valB = new Date(valB);
        } else if (sortCol === 'total_amount' || sortCol === 'id') {
            valA = parseFloat(valA); valB = parseFloat(valB);
        } else if (sortCol === 'status') {
            valA = statusOrder[valA] ?? 99; valB = statusOrder[valB] ?? 99;
        } else {
            valA = valA.toString().toLowerCase(); valB = valB.toString().toLowerCase();
        }
        if (valA < valB) return sortDir === 'asc' ? -1 : 1;
        if (valA > valB) return sortDir === 'asc' ? 1 : -1;
        return 0;
    });
}

function updateSortIcons() {
    document.querySelectorAll('.sortable-th').forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (!icon) return;
        if (th.dataset.col === sortCol) {
            icon.textContent = sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward';
            th.classList.add('th-sorted');
        } else {
            icon.textContent = 'unfold_more';
            th.classList.remove('th-sorted');
        }
    });
}

function renderOrders() {
    const tbody = document.getElementById('orders-tbody');
    const cardsContainer = document.getElementById('orders-cards');
    
    if (allOrders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state"><p>No orders found</p></div></td></tr>';
        updatePaginationControls(0);
        if (cardsContainer) {
            cardsContainer.innerHTML = '<div class="order-cards-empty"><span class="material-icons">receipt_long</span><p>No orders found</p></div>';
        }
        return;
    }
    
    // Pagination
    const totalPages = Math.ceil(allOrders.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedOrders = allOrders.slice(startIndex, endIndex);
    
    // Table view
    let html = '';
    paginatedOrders.forEach((order, index) => {
        const rowNumber = startIndex + index + 1;
        const actionButtons = getActionButtons(order);
        
        html += `
            <tr>
                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name}</td>
                <td>
                    <div class="items-box" id="items-box-${order.id}">
                        <div class="items-loading">Loading...</div>
                    </div>
                </td>
                <td>${formatDate(order.order_date)}</td>
                <td>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</td>
                <td><strong>${formatCurrency(order.total_amount)}</strong></td>
                <td><span class="badge badge-${order.status}">${order.status.replace(/_/g, ' ')}</span></td>
                <td>${order.rider_name || '<span style="color:var(--text-muted)">Nothing</span>'}</td>
                <td style="white-space: nowrap;">
                    ${actionButtons}
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
    
    // Load items for all visible orders
    paginatedOrders.forEach(order => {
        loadOrderItems(order.id);
    });
}

function renderOrderCards(orders, container, startIndex = 0) {
    let cardsHtml = '<div class="order-cards-grid">';
    orders.forEach((order, index) => {
        const cardNumber = startIndex + index + 1;
        const actionButtons = getActionButtons(order);
        cardsHtml += `
            <div class="order-card">
                <div class="order-card-header">
                    <div class="order-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    <div class="order-card-actions">
                        ${actionButtons}
                    </div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">receipt</span> Order ID</div>
                    <div class="order-card-value"><strong>#${order.id}</strong></div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">person</span> Customer</div>
                    <div class="order-card-value">${order.customer_name}</div>
                </div>
                <div class="order-card-items">
                    <div class="order-card-items-label"><span class="material-icons">inventory_2</span> Items</div>
                    <div class="order-card-items-list" id="card-items-${order.id}">
                        <span style="color:var(--text-muted)">Loading...</span>
                    </div>
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
                    <div class="order-card-label"><span class="material-icons">payments</span> Total</div>
                    <div class="order-card-value total-highlight">${formatCurrency(order.total_amount)}</div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">info</span> Status</div>
                    <div class="order-card-value"><span class="badge badge-${order.status}">${order.status.replace(/_/g, ' ')}</span></div>
                </div>
                <div class="order-card-row">
                    <div class="order-card-label"><span class="material-icons">sports_motorsports</span> Rider</div>
                    <div class="order-card-value">${order.rider_name || '<span style="color:var(--text-muted)">Nothing</span>'}</div>
                </div>
            </div>
        `;
    });
    cardsHtml += '</div>';
    container.innerHTML = cardsHtml;
}

function getActionButtons(order) {
    let buttons = `
        <button class="btn-icon" onclick="viewOrder(${order.id})" title="View Details">
            <span class="material-icons">visibility</span>
        </button>
    `;
    
    const completed = ['delivered', 'accepted', 'picked_up', 'cancelled'];
    
    // Status-specific action buttons
    if (order.status === 'pending') {
        buttons += `
            <button class="btn-icon" onclick="confirmOrder(${order.id})" title="Confirm Order" style="color: var(--success);">
                <span class="material-icons">check_circle</span>
            </button>
            <button class="btn-icon" onclick="cancelOrder(${order.id})" title="Cancel Order" style="color: var(--danger);">
                <span class="material-icons">cancel</span>
            </button>
        `;
    } else if (order.status === 'confirmed') {
        if (order.delivery_type === 'delivery') {
            buttons += `
                <button class="btn-icon" onclick="showAssignRider(${order.id})" title="Assign Rider" style="color: var(--primary);">
                    <span class="material-icons">delivery_dining</span>
                </button>
            `;
        } else {
            buttons += `
                <button class="btn-icon" onclick="markReadyForPickup(${order.id})" title="Ready for Pickup" style="color: var(--success);">
                    <span class="material-icons">done_all</span>
                </button>
            `;
        }
    } else if (order.status === 'ready_for_pickup') {
        buttons += `
            <button class="btn-icon" onclick="confirmPickup(${order.id})" title="Confirm Pickup" style="color: var(--success);">
                <span class="material-icons">check_circle</span>
            </button>
        `;
    } else if (order.status === 'reassign_requested') {
        buttons += `
            <button class="btn-icon" onclick="showAssignRider(${order.id})" title="Assign Rider (Reassign)" style="color: var(--warning);">
                <span class="material-icons">swap_horiz</span>
            </button>
        `;
    }
    
    // Cancel button: only for active non-completed statuses
    if (!completed.includes(order.status) && order.status !== 'pending') {
        buttons += `
            <button class="btn-icon" onclick="cancelOrder(${order.id})" title="Cancel Order" style="color: var(--danger);">
                <span class="material-icons">cancel</span>
            </button>
        `;
    }
    
    return buttons;
}

async function loadOrderItems(orderId) {
    const itemsBox = document.getElementById(`items-box-${orderId}`);
    const cardItems = document.getElementById(`card-items-${orderId}`);
    if (!itemsBox && !cardItems) return;
    
    try {
        const response = await fetch(`../api/orders/get.php?id=${orderId}`);
        const data = await response.json();
        
        if (data.success && data.items) {
            // Table items
            let itemsHtml = '';
            data.items.forEach((item, index) => {
                itemsHtml += `
                    <div class="item-entry">
                        <span class="item-num">${index + 1}.</span>
                        <span class="item-info">${item.item_name} × ${item.quantity}</span>
                        <span class="item-amount">${formatCurrency(item.subtotal)}</span>
                    </div>
                `;
            });
            if (itemsBox) itemsBox.innerHTML = itemsHtml;
            
            // Card items
            if (cardItems) {
                let cardHtml = '';
                data.items.forEach(item => {
                    cardHtml += `
                        <div class="order-card-item">
                            <span class="order-card-item-name">${item.item_name} × ${item.quantity}</span>
                            <span class="order-card-item-amount">${formatCurrency(item.subtotal)}</span>
                        </div>
                    `;
                });
                cardItems.innerHTML = cardHtml;
            }
        } else {
            if (itemsBox) itemsBox.innerHTML = '<div class="items-error">No items</div>';
            if (cardItems) cardItems.innerHTML = '<span style="color:var(--text-muted)">No items</span>';
        }
    } catch (error) {
        if (itemsBox) itemsBox.innerHTML = '<div class="items-error">Failed to load</div>';
        if (cardItems) cardItems.innerHTML = '<span style="color:var(--text-muted)">Failed to load</span>';
    }
}

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
    
    if (totalPages <= 1) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
        return;
    }
    
    // Show only the correct one for current viewport
    if (window.innerWidth <= 1024) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'flex';
    } else {
        if (paginationWrapper) paginationWrapper.style.display = 'flex';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
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
        renderOrders();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allOrders.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderOrders();
    }
}

async function viewOrder(orderId) {
    try {
        const response = await fetch(`../api/orders/get.php?id=${orderId}`);
        const data = await response.json();
        
        if (data.success) {
            showOrderModal(data.order, data.items);
        }
    } catch (error) {
        showToast('Failed to load order', 'error');
    }
}

function showOrderModal(order, items) {
    currentOrderId = order.id;
    
    const statusLabel = order.status.replace(/_/g, ' ');
    const typeIcon = order.delivery_type === 'delivery' ? 'local_shipping' : 'storefront';
    const typeLabel = order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup';
    
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
                <div class="odm-info-icon" style="background: rgba(21,101,192,0.1); color: var(--primary);">
                    <span class="material-icons">person</span>
                </div>
                <div class="odm-info-content">
                    <span class="odm-info-label">Customer</span>
                    <span class="odm-info-value">${order.customer_name}</span>
                </div>
            </div>
            <div class="odm-info-card">
                <div class="odm-info-icon" style="background: rgba(102,187,106,0.1); color: #66BB6A;">
                    <span class="material-icons">phone</span>
                </div>
                <div class="odm-info-content">
                    <span class="odm-info-label">Phone</span>
                    <span class="odm-info-value">${order.customer_phone || '—'}</span>
                </div>
            </div>
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
        </div>
        
        ${order.delivery_type === 'delivery' ? `
        <!-- Rider & Address -->
        <div class="odm-detail-rows">
            <div class="odm-detail-row">
                <span class="material-icons" style="color: var(--primary); font-size: 18px;">two_wheeler</span>
                <span class="odm-detail-label">Rider</span>
                <span class="odm-detail-value">${order.rider_name || '<span style="color:var(--text-muted);font-style:italic;">Not assigned</span>'}</span>
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
    
    document.getElementById('order-details').innerHTML = html;
    
    // Action buttons
    let actions = '<button class="btn btn-outline" onclick="closeModal(\'order-modal\')">Close</button>';
    
    const completedStatuses = ['delivered', 'accepted', 'picked_up', 'cancelled'];
    
    if (!completedStatuses.includes(order.status) && order.status !== 'pending') {
        actions = `<button class="btn btn-danger" onclick="cancelOrder(${order.id})">Cancel Order</button>` + actions;
    }
    
    if (order.status === 'pending') {
        actions = `<button class="btn btn-success" onclick="confirmOrder(${order.id})">Confirm</button>
                   <button class="btn btn-danger" onclick="cancelOrder(${order.id})">Cancel</button>` + actions;
    }
    
    if (order.status === 'confirmed' && order.delivery_type === 'delivery') {
        actions = `<button class="btn btn-primary" onclick="showAssignRider(${order.id})">Assign Rider</button>` + actions;
    }
    
    if (order.status === 'confirmed' && order.delivery_type === 'pickup') {
        actions = `<button class="btn btn-success" onclick="markReadyForPickup(${order.id})">Ready for Pickup</button>` + actions;
    }
    
    if (order.status === 'ready_for_pickup') {
        actions = `<button class="btn btn-success" onclick="confirmPickup(${order.id})">
                        <span class="material-icons" style="font-size: 18px; vertical-align: middle;">check_circle</span>
                        Confirm Pickup
                   </button>` + actions;
    }
    
    if (order.status === 'reassign_requested') {
        actions = `<button class="btn btn-warning" onclick="showAssignRider(${order.id})">Reassign Rider</button>` + actions;
    }
    
    document.getElementById('order-actions').innerHTML = actions;
    openModal('order-modal');
}

async function confirmOrder(orderId) {
    const result = await Swal.fire({
        title: 'Confirm Order',
        text: 'Are you sure you want to confirm this order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--success)',
        cancelButtonColor: 'var(--text-muted)',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Close'
    });
    
    if (!result.isConfirmed) return;
    
    await updateOrderStatus(orderId, 'confirmed');
}

async function cancelOrder(orderId) {
    const result = await Swal.fire({
        title: 'Cancel Order',
        text: 'Please provide a reason for cancellation:',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter cancellation reason...',
        inputAttributes: {
            'aria-label': 'Enter cancellation reason'
        },
        showCancelButton: true,
        confirmButtonColor: 'var(--danger)',
        cancelButtonColor: 'var(--text-muted)',
        confirmButtonText: 'Cancel Order',
        cancelButtonText: 'Close',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!';
            }
        }
    });
    
    if (!result.isConfirmed) return;
    
    try {
        const response = await fetch('../api/orders/cancel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, reason: result.value, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Order Cancelled',
                text: 'The order has been cancelled successfully.',
                timer: 2000,
                showConfirmButton: false
            });
            closeModal('order-modal');
            loadOrders();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message || 'Failed to cancel order'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while cancelling the order'
        });
    }
}

async function updateOrderStatus(orderId, status) {
    try {
        const response = await fetch('../api/orders/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, status, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Status updated', 'success');
            closeModal('order-modal');
            loadOrders();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

function showAssignRider(orderId) {
    document.getElementById('assign-order-id').value = orderId;
    
    // Check if this order has a reassignment reason
    const order = allOrders.find(o => o.id == orderId);
    const reassignNote = document.getElementById('reassign-reason-note');
    
    if (reassignNote) {
        if (order && order.staff_comment && order.staff_comment.includes('[REASSIGN REQUEST')) {
            // Extract the reason from the comment
            const match = order.staff_comment.match(/\[REASSIGN REQUEST[^\]]*\]\s*(.*)/);
            const reason = match ? match[1] : order.staff_comment;
            
            reassignNote.innerHTML = `
                <div style="background: rgba(255,167,38,0.1); border: 1px solid rgba(255,167,38,0.3); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-weight: 600; color: #F57C00;">
                        <span class="material-icons" style="font-size: 18px;">swap_horiz</span>
                        Reassignment Requested
                    </div>
                    <div style="font-size: 13px; color: var(--text-secondary);">
                        <strong>Reason:</strong> ${reason}
                    </div>
                </div>
            `;
            reassignNote.style.display = 'block';
        } else {
            reassignNote.innerHTML = '';
            reassignNote.style.display = 'none';
        }
    }
    
    closeModal('order-modal');
    openModal('assign-rider-modal');
}

async function loadRiders() {
    try {
        const response = await fetch('../api/riders/list.php');
        const data = await response.json();
        
        const selectOptions = document.getElementById('rider-options');
        const bulkSelectOptions = document.getElementById('bulk-rider-options');
        const hiddenInput = document.getElementById('rider-select');
        const triggerText = document.querySelector('#rider-trigger .selected-text');
        
        if (data.success && data.riders.length > 0) {
            let html = '<div class="custom-select-option selected" data-value="">Select a rider...</div>';
            html += data.riders.map(r => {
                const busy = r.on_delivery_count > 0;
                const unavailable = !r.is_available;
                const disabled = busy || unavailable;
                let label;
                if (unavailable) {
                    label = `${r.full_name} <span style="color:#9e9e9e;font-size:12px;">(unavailable)</span>`;
                } else if (busy) {
                    label = `${r.full_name} <span style="color:#E65100;font-size:12px;">(on delivery — busy)</span>`;
                } else {
                    label = `${r.full_name} <span style="color:#888;font-size:12px;">(${r.active_deliveries > 0 ? r.active_deliveries + ' assigned' : 'available'})</span>`;
                }
                return `<div class="custom-select-option${disabled ? ' disabled' : ''}" data-value="${disabled ? '' : r.id}" data-rider-id="${r.id}" ${disabled ? 'title="' + (unavailable ? 'This rider is currently unavailable' : 'This rider is currently on delivery') + '"' : ''}>${label}</div>`;
            }).join('');
            if (selectOptions) selectOptions.innerHTML = html;
            
            // Bulk assign — only available riders selectable
            if (bulkSelectOptions) {
                let bulkHtml = '<div class="custom-select-option selected" data-value="">Select a rider...</div>';
                bulkHtml += data.riders.filter(r => r.is_available).map(r => {
                    const label = r.active_deliveries > 0
                        ? `${r.full_name} <span style="color:#E65100;font-size:12px;">(${r.active_deliveries} active)</span>`
                        : `${r.full_name} <span style="color:#388E3C;font-size:12px;">(available)</span>`;
                    return `<div class="custom-select-option" data-value="${r.id}">${label}</div>`;
                }).join('');
                bulkSelectOptions.innerHTML = bulkHtml;
            }
        } else {
            if (selectOptions) selectOptions.innerHTML = '<div class="custom-select-option selected" data-value="">No riders found</div>';
            if (bulkSelectOptions) bulkSelectOptions.innerHTML = '<div class="custom-select-option selected" data-value="">No riders found</div>';
        }
        if (hiddenInput) hiddenInput.value = '';
        if (triggerText) triggerText.textContent = 'Select a rider...';
    } catch (error) {
        console.error('Failed to load riders:', error);
    }
}

function initRiderSelect() {
    const trigger = document.getElementById('rider-trigger');
    const optionsCont = document.getElementById('rider-options');
    const hiddenInput = document.getElementById('rider-select');
    
    if (trigger && optionsCont) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            trigger.classList.toggle('active');
            optionsCont.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !optionsCont.contains(e.target)) {
                trigger.classList.remove('active');
                optionsCont.classList.remove('active');
            }
        });
        
        optionsCont.addEventListener('click', function(e) {
            const opt = e.target.closest('.custom-select-option');
            if (!opt) return;
            
            // Block selection of disabled (busy) riders
            if (opt.classList.contains('disabled')) return;
            
            const value = opt.dataset.value;
            if (value !== undefined) {
                optionsCont.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                
                hiddenInput.value = value;
                trigger.querySelector('.selected-text').textContent = opt.textContent;
                trigger.querySelector('.selected-text').classList.remove('placeholder');
            }
            
            trigger.classList.remove('active');
            optionsCont.classList.remove('active');
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initRiderSelect();
    initBulkRiderSelect();
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.bulk-dropdown')) {
            document.querySelectorAll('.bulk-dropdown.open').forEach(d => d.classList.remove('open'));
        }
    });
});

async function assignRider(e) {
    e.preventDefault();
    
    const orderId = document.getElementById('assign-order-id').value;
    const riderId = document.getElementById('rider-select').value;
    
    if (!riderId) {
        showToast('Please select a rider', 'warning');
        return;
    }
    
    try {
        const response = await fetch('../api/orders/assign_rider.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, rider_id: riderId, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Rider assigned', 'success');
            closeModal('assign-rider-modal');
            loadOrders();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

async function markReadyForPickup(orderId) {
    await updateOrderStatus(orderId, 'ready_for_pickup');
}

async function confirmPickup(orderId) {
    const result = await Swal.fire({
        title: 'Confirm Pickup',
        text: 'Confirm that the customer has picked up this order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Picked Up',
        confirmButtonColor: '#66BB6A',
        cancelButtonText: 'Close'
    });
    if (!result.isConfirmed) return;
    await updateOrderStatus(orderId, 'picked_up');
}

// ============================================================================
// BULK ACTIONS
// ============================================================================

async function confirmAllPending() {
    const pendingOrders = allOrders.filter(o => o.status === 'pending');
    
    if (pendingOrders.length === 0) {
        showToast('No pending orders to confirm', 'info');
        return;
    }
    
    const result = await Swal.fire({
        title: 'Confirm All Pending Orders',
        html: `Are you sure you want to confirm <strong>${pendingOrders.length}</strong> pending order(s) on this page?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm All',
        confirmButtonColor: '#66BB6A',
        cancelButtonText: 'Close'
    });
    
    if (!result.isConfirmed) return;
    
    showLoading();
    let success = 0, failed = 0;
    
    for (const order of pendingOrders) {
        try {
            const response = await fetch('../api/orders/update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: order.id, status: 'confirmed', csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (e) { failed++; }
    }
    
    hideLoading();
    showToast(`Confirmed: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadOrders();
}

async function autoAssignRiders() {
    const confirmedDelivery = allOrders.filter(o => o.status === 'confirmed' && o.delivery_type === 'delivery');
    
    if (confirmedDelivery.length === 0) {
        showToast('No confirmed delivery orders to assign', 'info');
        return;
    }
    
    const result = await Swal.fire({
        title: 'Auto Assign Riders',
        html: `Auto-assign riders to <strong>${confirmedDelivery.length}</strong> confirmed delivery order(s)?<br><small>The least-busy available rider will be assigned.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Auto Assign',
        confirmButtonColor: '#42A5F5',
        cancelButtonText: 'Close'
    });
    
    if (!result.isConfirmed) return;
    
    showLoading();
    let success = 0, failed = 0;
    
    for (const order of confirmedDelivery) {
        try {
            const response = await fetch('../api/orders/auto_assign.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: order.id, csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (e) { failed++; }
    }
    
    hideLoading();
    showToast(`Assigned: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadOrders();
    loadRiders();
}

async function cancelAllCancellable() {
    const cancellableStatuses = ['pending', 'confirmed', 'assigned', 'reassign_requested', 'on_delivery'];
    const cancellableOrders = allOrders.filter(o => cancellableStatuses.includes(o.status));

    if (cancellableOrders.length === 0) {
        showToast('No cancellable orders found', 'info');
        return;
    }

    const result = await Swal.fire({
        title: 'Cancel All Cancellable Orders',
        html: `Are you sure you want to cancel <strong>${cancellableOrders.length}</strong> order(s)?<br><small style="color:var(--text-muted)">Includes: pending, confirmed, assigned, reassign requested, on delivery</small>`,
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter cancellation reason...',
        showCancelButton: true,
        confirmButtonText: 'Cancel All',
        confirmButtonColor: '#EF5350',
        cancelButtonText: 'Close',
        inputValidator: (value) => {
            if (!value) return 'Please provide a reason!';
        }
    });

    if (!result.isConfirmed) return;

    showLoading();
    let success = 0, failed = 0;

    for (const order of cancellableOrders) {
        try {
            const response = await fetch('../api/orders/cancel.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: order.id, reason: result.value, csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (err) { failed++; }
    }

    hideLoading();
    showToast(`Cancelled: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadOrders();
}

async function cancelByStatus(status) {
    const statusOrders = allOrders.filter(o => o.status === status);
    const statusLabel = status.replace(/_/g, ' ');
    const statusTitle = statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1);
    
    if (statusOrders.length === 0) {
        showToast(`No ${statusLabel} orders to cancel`, 'info');
        return;
    }
    
    const result = await Swal.fire({
        title: `Cancel All ${statusTitle} Orders`,
        html: `Are you sure you want to cancel <strong>${statusOrders.length}</strong> ${statusLabel} order(s)?`,
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter cancellation reason...',
        showCancelButton: true,
        confirmButtonText: 'Cancel All',
        confirmButtonColor: '#EF5350',
        cancelButtonText: 'Close',
        inputValidator: (value) => {
            if (!value) return 'Please provide a reason!';
        }
    });
    
    if (!result.isConfirmed) return;
    
    showLoading();
    let success = 0, failed = 0;
    
    for (const order of statusOrders) {
        try {
            const response = await fetch('../api/orders/cancel.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: order.id, reason: result.value, csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (err) { failed++; }
    }
    
    hideLoading();
    showToast(`Cancelled: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadOrders();
}

// ============================================================================
// BULK DROPDOWN TOGGLE
// ============================================================================

function toggleBulkDropdown(id) {
    const dropdown = document.getElementById(id);
    const isOpen = dropdown.classList.contains('open');
    document.querySelectorAll('.bulk-dropdown.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) dropdown.classList.add('open');
}

function closeBulkDropdown(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

// ============================================================================
// Assign to Rider (BULK)
// ============================================================================

function assignSpecificRider() {
    const confirmedDelivery = allOrders.filter(o => o.status === 'confirmed' && o.delivery_type === 'delivery');
    
    if (confirmedDelivery.length === 0) {
        showToast('No confirmed delivery orders to assign', 'info');
        return;
    }
    
    const countText = document.getElementById('bulk-assign-count-text');
    if (countText) {
        countText.textContent = `This will assign all ${confirmedDelivery.length} confirmed delivery order(s) to the selected rider.`;
    }
    
    // Reset rider selection
    const hiddenInput = document.getElementById('bulk-rider-select');
    const triggerText = document.querySelector('#bulk-rider-trigger .selected-text');
    if (hiddenInput) hiddenInput.value = '';
    if (triggerText) {
        triggerText.textContent = 'Select a rider...';
        triggerText.classList.add('placeholder');
    }
    document.querySelectorAll('#bulk-rider-options .custom-select-option').forEach(o => o.classList.remove('selected'));
    const firstOpt = document.querySelector('#bulk-rider-options .custom-select-option');
    if (firstOpt) firstOpt.classList.add('selected');
    
    openModal('bulk-assign-rider-modal');
}

async function assignRiderBulk(e) {
    e.preventDefault();
    
    const riderId = document.getElementById('bulk-rider-select').value;
    if (!riderId) {
        showToast('Please select a rider', 'warning');
        return;
    }
    
    const confirmedDelivery = allOrders.filter(o => o.status === 'confirmed' && o.delivery_type === 'delivery');
    if (confirmedDelivery.length === 0) {
        showToast('No confirmed delivery orders to assign', 'info');
        closeModal('bulk-assign-rider-modal');
        return;
    }
    
    closeModal('bulk-assign-rider-modal');
    showLoading();
    let success = 0, failed = 0;
    
    for (const order of confirmedDelivery) {
        try {
            const response = await fetch('../api/orders/assign_rider.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: order.id, rider_id: riderId, csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (err) { failed++; }
    }
    
    hideLoading();
    showToast(`Assigned: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadOrders();
    loadRiders();
}

function initBulkRiderSelect() {
    const trigger = document.getElementById('bulk-rider-trigger');
    const optionsCont = document.getElementById('bulk-rider-options');
    const hiddenInput = document.getElementById('bulk-rider-select');
    
    if (!trigger || !optionsCont) return;
    
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        trigger.classList.toggle('active');
        optionsCont.classList.toggle('active');
    });
    
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !optionsCont.contains(e.target)) {
            trigger.classList.remove('active');
            optionsCont.classList.remove('active');
        }
    });
    
    optionsCont.addEventListener('click', function(e) {
        const opt = e.target.closest('.custom-select-option');
        if (!opt || opt.classList.contains('disabled')) return;
        
        const value = opt.dataset.value;
        if (value !== undefined) {
            optionsCont.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            hiddenInput.value = value;
            trigger.querySelector('.selected-text').textContent = opt.textContent;
            trigger.querySelector('.selected-text').classList.remove('placeholder');
        }
        
        trigger.classList.remove('active');
        optionsCont.classList.remove('active');
    });
}
