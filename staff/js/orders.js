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

document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    initFilterButtons();
    loadRiders();
    
    document.getElementById('assign-rider-form').addEventListener('submit', assignRider);
});

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
            renderOrders(data.orders);
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
    }
}

function renderOrders(orders) {
    const tbody = document.getElementById('orders-tbody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><p>No orders found</p></div></td></tr>';
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        const actionButtons = getActionButtons(order);
        
        html += `
            <tr>
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
                <td style="white-space: nowrap;">
                    ${actionButtons}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Load items for all visible orders
    orders.forEach(order => {
        loadOrderItems(order.id);
    });
}

function getActionButtons(order) {
    let buttons = `
        <button class="btn-icon" onclick="viewOrder(${order.id})" title="View Details">
            <span class="material-icons">visibility</span>
        </button>
    `;
    
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
    } else if (order.status === 'assigned' && order.delivery_type === 'delivery') {
        buttons += `
            <button class="btn-icon" onclick="showAssignRider(${order.id})" title="Reassign Rider" style="color: var(--warning);">
                <span class="material-icons">swap_horiz</span>
            </button>
        `;
    }
    
    return buttons;
}

async function loadOrderItems(orderId) {
    const itemsBox = document.getElementById(`items-box-${orderId}`);
    if (!itemsBox) return;
    
    try {
        const response = await fetch(`../api/orders/get.php?id=${orderId}`);
        const data = await response.json();
        
        if (data.success && data.items) {
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
            itemsBox.innerHTML = itemsHtml;
        } else {
            itemsBox.innerHTML = '<div class="items-error">No items</div>';
        }
    } catch (error) {
        itemsBox.innerHTML = '<div class="items-error">Failed to load</div>';
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
    
    let html = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div><strong>Order ID:</strong> #${order.id}</div>
            <div><strong>Customer:</strong> ${order.customer_name}</div>
            <div><strong>Phone:</strong> ${order.customer_phone}</div>
            <div><strong>Date:</strong> ${formatDate(order.order_date)}</div>
        </div>
        ${order.delivery_address ? `<div style="margin-bottom: 20px;"><strong>Address:</strong> ${order.delivery_address}</div>` : ''}
        <h4>Items</h4>
        <table class="data-table" style="margin-bottom: 20px;">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
                ${items.map(item => `
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>${formatCurrency(item.item_price)}</td>
                        <td>${formatCurrency(item.subtotal)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div style="text-align: right;">
            <div>Subtotal: ${formatCurrency(order.subtotal)}</div>
            <div>Delivery Fee: ${formatCurrency(order.delivery_fee)}</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">Total: ${formatCurrency(order.total_amount)}</div>
        </div>
    `;
    
    document.getElementById('order-details').innerHTML = html;
    
    // Action buttons
    let actions = '<button class="btn btn-outline" onclick="closeModal(\'order-modal\')">Close</button>';
    
    if (order.status === 'pending') {
        actions = `
            <button class="btn btn-success" onclick="confirmOrder(${order.id})">Confirm</button>
            <button class="btn btn-danger" onclick="cancelOrder(${order.id})">Cancel</button>
        ` + actions;
    }
    
    if (order.status === 'confirmed' && order.delivery_type === 'delivery') {
        actions = `<button class="btn btn-primary" onclick="showAssignRider(${order.id})">Assign Rider</button>` + actions;
    }
    
    if (order.status === 'confirmed' && order.delivery_type === 'pickup') {
        actions = `<button class="btn btn-success" onclick="markReadyForPickup(${order.id})">Ready for Pickup</button>` + actions;
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
        confirmButtonText: 'Yes, Confirm',
        cancelButtonText: 'Cancel'
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
    closeModal('order-modal');
    openModal('assign-rider-modal');
}

async function loadRiders() {
    try {
        const response = await fetch('../api/riders/list.php?available_only=true');
        const data = await response.json();
        
        const select = document.getElementById('rider-select');
        
        if (data.success && data.riders.length > 0) {
            select.innerHTML = '<option value="">Select a rider...</option>' + 
                data.riders.map(r => `<option value="${r.id}">${r.full_name} (${r.active_deliveries} active)</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No available riders</option>';
        }
    } catch (error) {
        console.error('Failed to load riders:', error);
    }
}

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
        html: `Are you sure you want to confirm <strong>${pendingOrders.length}</strong> pending order(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Confirm All',
        confirmButtonColor: '#66BB6A',
        cancelButtonText: 'Cancel'
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
        cancelButtonText: 'Cancel'
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

async function cancelAllPending() {
    const pendingOrders = allOrders.filter(o => o.status === 'pending');
    
    if (pendingOrders.length === 0) {
        showToast('No pending orders to cancel', 'info');
        return;
    }
    
    const result = await Swal.fire({
        title: 'Cancel All Pending Orders',
        html: `Are you sure you want to cancel <strong>${pendingOrders.length}</strong> pending order(s)?`,
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter cancellation reason...',
        showCancelButton: true,
        confirmButtonText: 'Cancel All',
        confirmButtonColor: '#EF5350',
        cancelButtonText: 'Go Back',
        inputValidator: (value) => {
            if (!value) return 'Please provide a reason!';
        }
    });
    
    if (!result.isConfirmed) return;
    
    showLoading();
    let success = 0, failed = 0;
    
    for (const order of pendingOrders) {
        try {
            const response = await fetch('../api/orders/cancel.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order_id: order.id, reason: result.value, csrf_token: getCSRFToken() })
            });
            const data = await response.json();
            if (data.success) success++;
            else failed++;
        } catch (e) { failed++; }
    }
    
    hideLoading();
    showToast(`Cancelled: ${success}, Failed: ${failed}`, success > 0 ? 'success' : 'error');
    loadOrders();
}
