/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER DASHBOARD JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Dashboard page logic for customer role
 * Functions: Load statistics, recent orders
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentOrders();
});

/**
 * Load dashboard statistics
 */
async function loadDashboardStats() {
    try {
        const response = await fetch('../api/analytics/dashboard.php');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            
            document.getElementById('total-orders').textContent = stats.total_orders || 0;
            document.getElementById('pending-orders').textContent = stats.pending_orders || 0;
            document.getElementById('active-orders').textContent = stats.active_orders || 0;
            document.getElementById('completed-orders').textContent = stats.completed_orders || 0;
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
    }
}

/**
 * Load recent orders
 */
async function loadRecentOrders() {
    try {
        const response = await fetch('../api/orders/list.php?limit=5');
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            renderRecentOrders(data.orders);
        } else {
            showEmptyOrders();
        }
    } catch (error) {
        console.error('Failed to load recent orders:', error);
        showEmptyOrders();
    }
}

/**
 * Render recent orders table
 */
function renderRecentOrders(orders) {
    const tbody = document.getElementById('recent-orders-tbody');
    
    let html = '';
    
    orders.forEach(order => {
        html += `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${formatDate(order.order_date)}</td>
                <td>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</td>
                <td>${formatCurrency(order.total_amount)}</td>
                <td>
                    <span class="badge badge-${order.status}">
                        ${getStatusLabel(order.status)}
                    </span>
                </td>
                <td>
                    <button class="btn-icon" onclick="viewOrder(${order.id})" title="View Details">
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
function showEmptyOrders() {
    const tbody = document.getElementById('recent-orders-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <span class="material-icons empty-icon">inbox</span>
                    <p class="empty-title">No orders yet</p>
                    <p class="empty-message">Start by placing your first order!</p>
                    <a href="place_order.php" class="btn btn-primary" style="margin-top: 16px;">
                        <span class="material-icons">add_shopping_cart</span>
                        Place Order
                    </a>
                </div>
            </td>
        </tr>
    `;
}

/**
 * View order details
 */
function viewOrder(orderId) {
    window.location.href = `orders.php?id=${orderId}`;
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
