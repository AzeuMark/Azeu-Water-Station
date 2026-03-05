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

document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    initFilterButtons();
    
    // Check if specific order ID in URL
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id');
    if (orderId) {
        viewOrderDetails(parseInt(orderId));
    }
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
            loadOrders();
        });
    });
}

/**
 * Load orders
 */
async function loadOrders() {
    try {
        let url = '../api/orders/list.php';
        if (currentFilter) {
            url += `?status=${currentFilter}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderOrders(data.orders);
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
        showEmptyState();
    }
}

/**
 * Render orders table
 */
function renderOrders(orders) {
    const tbody = document.getElementById('orders-tbody');
    
    if (orders.length === 0) {
        showEmptyState();
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        html += `
            <tr>
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
                <td>
                    <button class="btn-icon" onclick="viewOrderDetails(${order.id})" title="View Details">
                        <span class="material-icons">visibility</span>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

/**
 * Show empty state
 */
function showEmptyState() {
    const tbody = document.getElementById('orders-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7">
                <div class="empty-state">
                    <span class="material-icons empty-icon">inbox</span>
                    <p class="empty-title">No orders found</p>
                    <p class="empty-message">
                        ${currentFilter ? 'No orders with this status' : 'You haven\'t placed any orders yet'}
                    </p>
                    ${!currentFilter ? '<a href="place_order.php" class="btn btn-primary" style="margin-top: 16px;"><span class="material-icons">add_shopping_cart</span> Place Order</a>' : ''}
                </div>
            </td>
        </tr>
    `;
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
    
    let html = `
        <div style="margin-bottom: 20px;">
            <h4 style="margin-bottom: 12px;">Order Information</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Order ID</div>
                    <div style="font-weight: 600;">#${order.id}</div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Status</div>
                    <span class="badge badge-${order.status}">${getStatusLabel(order.status)}</span>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Order Date</div>
                    <div>${formatDate(order.order_date)}</div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Type</div>
                    <div>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</div>
                </div>
            </div>
        </div>
        
        ${order.delivery_address ? `
            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;">Delivery Address</h4>
                <p>${order.delivery_address}</p>
            </div>
        ` : ''}
        
        ${order.rider_name ? `
            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;">Rider</h4>
                <p>${order.rider_name} - ${order.rider_phone}</p>
            </div>
        ` : ''}
        
        <div style="margin-bottom: 20px;">
            <h4 style="margin-bottom: 12px;">Order Items</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--surface);">
                    <tr>
                        <th style="padding: 8px; text-align: left;">Item</th>
                        <th style="padding: 8px; text-align: center;">Qty</th>
                        <th style="padding: 8px; text-align: right;">Price</th>
                        <th style="padding: 8px; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    items.forEach(item => {
        html += `
            <tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 8px;">${item.item_name}</td>
                <td style="padding: 8px; text-align: center;">${item.quantity}</td>
                <td style="padding: 8px; text-align: right;">${formatCurrency(item.item_price)}</td>
                <td style="padding: 8px; text-align: right;">${formatCurrency(item.subtotal)}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        
        <div style="border-top: 2px solid var(--border); padding-top: 16px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Subtotal:</span>
                <strong>${formatCurrency(order.subtotal)}</strong>
            </div>
            ${order.delivery_fee > 0 ? `
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Delivery Fee:</span>
                    <strong>${formatCurrency(order.delivery_fee)}</strong>
                </div>
            ` : ''}
            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; color: var(--primary); padding-top: 12px; border-top: 1px solid var(--border);">
                <strong>Total:</strong>
                <strong>${formatCurrency(order.total_amount)}</strong>
            </div>
        </div>
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
