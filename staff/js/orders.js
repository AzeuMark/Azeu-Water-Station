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
            renderOrders(data.orders);
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
    }
}

function renderOrders(orders) {
    const tbody = document.getElementById('orders-tbody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No orders found</p></div></td></tr>';
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        html += `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name}</td>
                <td>${formatDate(order.order_date)}</td>
                <td>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</td>
                <td><strong>${formatCurrency(order.total_amount)}</strong></td>
                <td><span class="badge badge-${order.status}">${order.status.replace(/_/g, ' ')}</span></td>
                <td>
                    <button class="btn-icon" onclick="viewOrder(${order.id})" title="View">
                        <span class="material-icons">visibility</span>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
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
    if (!confirm('Confirm this order?')) return;
    
    await updateOrderStatus(orderId, 'confirmed');
}

async function cancelOrder(orderId) {
    const reason = prompt('Reason for cancellation:');
    if (!reason) return;
    
    try {
        const response = await fetch('../api/orders/cancel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, reason, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Order cancelled', 'success');
            closeModal('order-modal');
            loadOrders();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
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
