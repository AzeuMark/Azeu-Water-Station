/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DELIVERIES JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Active deliveries management logic
 * Functions: Load deliveries, update status, view details, pagination
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

let allDeliveryOrders = [];
let deliveriesPage = 1;
const deliveriesPerPage = 10;

document.addEventListener('DOMContentLoaded', function() {
    loadDeliveries();
});

/**
 * Load active deliveries
 */
async function loadDeliveries() {
    try {
        const response = await fetch('../api/orders/list.php?status=on_delivery&limit=100');
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            allDeliveryOrders = data.orders;
            deliveriesPage = 1;
            renderDeliveries();
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
 * Render deliveries with pagination
 */
function renderDeliveries() {
    const container = document.getElementById('deliveries-container');
    
    if (allDeliveryOrders.length === 0) {
        showEmptyState();
        return;
    }
    
    // Pagination
    const totalPages = Math.ceil(allDeliveryOrders.length / deliveriesPerPage);
    const startIdx = (deliveriesPage - 1) * deliveriesPerPage;
    const pageOrders = allDeliveryOrders.slice(startIdx, startIdx + deliveriesPerPage);
    
    let html = '<div style="display: grid; gap: 20px;">';
    
    pageOrders.forEach(order => {
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
                
                <div class="delivery-footer" style="display: flex; gap: 8px;">
                    <button class="btn btn-success" onclick="markAsDelivered(${order.id})" style="flex: 1;">
                        <span class="material-icons">check_circle</span>
                        Mark as Delivered
                    </button>
                    <button class="btn btn-warning" onclick="requestReassign(${order.id})" title="Request Reassignment" style="flex: 0;">
                        <span class="material-icons">swap_horiz</span>
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    updateDeliveriesPagination(totalPages);
}

function updateDeliveriesPagination(totalPages) {
    const wrapper = document.getElementById('deliveries-pagination');
    if (!wrapper) return;
    
    const info = document.getElementById('del-page-info');
    const prevBtn = document.getElementById('del-prev-btn');
    const nextBtn = document.getElementById('del-next-btn');
    
    if (totalPages <= 1) {
        wrapper.style.display = 'none';
        return;
    }
    
    wrapper.style.display = 'flex';
    info.textContent = `Page ${deliveriesPage} of ${totalPages}`;
    prevBtn.disabled = deliveriesPage <= 1;
    nextBtn.disabled = deliveriesPage >= totalPages;
}

function prevDeliveriesPage() {
    if (deliveriesPage > 1) {
        deliveriesPage--;
        renderDeliveries();
    }
}

function nextDeliveriesPage() {
    const totalPages = Math.ceil(allDeliveryOrders.length / deliveriesPerPage);
    if (deliveriesPage < totalPages) {
        deliveriesPage++;
        renderDeliveries();
    }
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
    
    const pagination = document.getElementById('deliveries-pagination');
    if (pagination) pagination.style.display = 'none';
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
            // Remove from local list
            allDeliveryOrders = allDeliveryOrders.filter(o => o.id != orderId);
            if (allDeliveryOrders.length === 0) {
                showEmptyState();
            } else {
                renderDeliveries();
            }
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Update status error:', error);
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
            allDeliveryOrders = allDeliveryOrders.filter(o => o.id != orderId);
            if (allDeliveryOrders.length === 0) {
                showEmptyState();
            } else {
                renderDeliveries();
            }
        } else {
            showToast(data.message || 'Failed to request reassignment', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('An error occurred', 'error');
    }
}
