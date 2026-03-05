/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DELIVERIES JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Active deliveries management logic
 * Functions: Load deliveries, update status, view details
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDeliveries();
});

/**
 * Load active deliveries
 */
async function loadDeliveries() {
    try {
        const response = await fetch('../api/orders/list.php?status=on_delivery');
        const data = await response.json();
        
        if (data.success) {
            renderDeliveries(data.orders);
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load deliveries:', error);
        showEmptyState();
    }
}

/**
 * Render deliveries
 */
function renderDeliveries(orders) {
    const container = document.getElementById('deliveries-container');
    
    if (orders.length === 0) {
        showEmptyState();
        return;
    }
    
    let html = '<div style="display: grid; gap: 20px;">';
    
    orders.forEach(order => {
        html += `
            <div class="delivery-card">
                <div class="delivery-header">
                    <div>
                        <h4>Order #${order.id}</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted);">${formatDate(order.order_date)}</p>
                    </div>
                    <span class="badge badge-on_delivery">On Delivery</span>
                </div>
                
                <div class="delivery-body">
                    <div class="delivery-info">
                        <div class="info-item">
                            <span class="material-icons">person</span>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Customer</div>
                                <div style="font-weight: 600;">${order.customer_name}</div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <span class="material-icons">phone</span>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Phone</div>
                                <div style="font-weight: 600;">${order.customer_phone}</div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <span class="material-icons">location_on</span>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Address</div>
                                <div style="font-weight: 600;">${order.delivery_address}</div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <span class="material-icons">payments</span>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Amount</div>
                                <div style="font-weight: 600; color: var(--primary);">${formatCurrency(order.total_amount)}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="delivery-footer">
                    <button class="btn btn-success" onclick="markAsDelivered(${order.id})">
                        <span class="material-icons">check_circle</span>
                        Mark as Delivered
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    container.innerHTML = html;
}

/**
 * Show empty state
 */
function showEmptyState() {
    const container = document.getElementById('deliveries-container');
    container.innerHTML = `
        <div class="empty-state">
            <span class="material-icons empty-icon">local_shipping</span>
            <p class="empty-title">No active deliveries</p>
            <p class="empty-message">Your active deliveries will appear here</p>
        </div>
    `;
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
            body: JSON.stringify({
                order_id: orderId,
                status: 'delivered',
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Order marked as delivered!', 'success');
            loadDeliveries();
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Update status error:', error);
        showToast('An error occurred', 'error');
    }
}
