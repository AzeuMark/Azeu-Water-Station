/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER DASHBOARD JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Dashboard page logic for customer role
 * Functions: Greeting, animated stats, recent orders (table + mobile cards)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    setGreeting();
    loadDashboardStats();
    loadRecentOrders();
});

/**
 * Set dynamic greeting based on time of day
 */
function setGreeting() {
    const hour = new Date().getHours();
    let greeting = 'Good evening!';
    if (hour < 12) greeting = 'Good morning!';
    else if (hour < 18) greeting = 'Good afternoon!';
    document.getElementById('greeting-text').textContent = greeting;
}

/**
 * Animate a counter from 0 to target value
 */
function animateValue(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    target = parseInt(target) || 0;
    if (target === 0) { el.textContent = '0'; return; }
    
    let current = 0;
    const duration = 600;
    const step = Math.max(1, Math.ceil(target / (duration / 16)));
    const timer = setInterval(() => {
        current += step;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = current.toLocaleString();
    }, 16);
}

/**
 * Load dashboard statistics with animated counters
 */
async function loadDashboardStats() {
    try {
        const response = await fetch('../api/analytics/dashboard.php');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            animateValue('total-orders', stats.total_orders || 0);
            animateValue('pending-orders', stats.pending_orders || 0);
            animateValue('active-orders', stats.active_orders || 0);
            animateValue('completed-orders', stats.completed_orders || 0);
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
    }
}

/**
 * Load recent orders with table + mobile card views
 */
async function loadRecentOrders() {
    try {
        const response = await fetch('../api/orders/list.php?limit=5');
        const data = await response.json();
        
        const tbody = document.getElementById('recent-orders-tbody');
        const cardsContainer = document.getElementById('recent-orders-cards');
        
        if (data.success && data.orders.length > 0) {
            let tableHtml = '';
            let cardsHtml = '';
            
            data.orders.forEach(order => {
                const statusLabel = getStatusLabel(order.status);
                
                tableHtml += `
                    <tr>
                        <td><strong>#${order.id}</strong></td>
                        <td>${formatDate(order.order_date)}</td>
                        <td>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</td>
                        <td>${formatCurrency(order.total_amount)}</td>
                        <td><span class="badge badge-${order.status}">${statusLabel}</span></td>
                        <td>
                            <button class="btn-icon" onclick="viewOrder(${order.id})" title="View Details">
                                <span class="material-icons">visibility</span>
                            </button>
                        </td>
                    </tr>
                `;
                
                cardsHtml += `
                    <div class="dash-mini-card" onclick="viewOrder(${order.id})" style="cursor:pointer;">
                        <div class="dash-mini-card-left">
                            <span class="dash-mini-card-title">#${order.id} — ${formatDate(order.order_date)}</span>
                            <span class="dash-mini-card-sub">${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'} · ${formatCurrency(order.total_amount)}</span>
                        </div>
                        <div class="dash-mini-card-right">
                            <span class="badge badge-${order.status}">${statusLabel}</span>
                        </div>
                    </div>
                `;
            });
            
            tbody.innerHTML = tableHtml;
            if (cardsContainer) cardsContainer.innerHTML = cardsHtml;
        } else {
            showEmptyOrders(tbody, cardsContainer);
        }
    } catch (error) {
        console.error('Failed to load recent orders:', error);
        const tbody = document.getElementById('recent-orders-tbody');
        const cardsContainer = document.getElementById('recent-orders-cards');
        showEmptyOrders(tbody, cardsContainer);
    }
}

/**
 * Show empty state for both table and card views
 */
function showEmptyOrders(tbody, cardsContainer) {
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6">
                    <div class="dash-empty-state">
                        <span class="material-icons">inbox</span>
                        <p>No orders yet — place your first order!</p>
                    </div>
                </td>
            </tr>
        `;
    }
    if (cardsContainer) {
        cardsContainer.innerHTML = `
            <div class="dash-empty-state">
                <span class="material-icons">inbox</span>
                <p>No orders yet — place your first order!</p>
            </div>
        `;
    }
}

/**
 * View order details
 */
function viewOrder(orderId) {
    window.location.href = `orders.php?id=${orderId}`;
}

/**
 * Get human-readable status label
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
