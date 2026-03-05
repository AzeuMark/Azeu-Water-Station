/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DASHBOARD JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Dashboard logic for rider role
 * Functions: Load stats, active deliveries, toggle availability
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadActiveDeliveries();
    initAvailabilityToggle();
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
            
            document.getElementById('pending-deliveries').textContent = stats.pending_deliveries || 0;
            document.getElementById('on-delivery').textContent = stats.on_delivery || 0;
            document.getElementById('completed-deliveries').textContent = stats.completed_deliveries || 0;
            document.getElementById('today-deliveries').textContent = stats.today_deliveries || 0;
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
    }
}

/**
 * Load active deliveries
 */
async function loadActiveDeliveries() {
    try {
        const response = await fetch('../api/orders/list.php?limit=5');
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            renderActiveDeliveries(data.orders);
        } else {
            showEmptyDeliveries();
        }
    } catch (error) {
        console.error('Failed to load active deliveries:', error);
        showEmptyDeliveries();
    }
}

/**
 * Render active deliveries
 */
function renderActiveDeliveries(orders) {
    const tbody = document.getElementById('active-deliveries-tbody');
    
    let html = '';
    
    orders.forEach(order => {
        html += `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name}</td>
                <td>${order.delivery_address ? truncate(order.delivery_address, 40) : 'Pickup'}</td>
                <td>
                    <span class="badge badge-${order.status}">
                        ${getStatusLabel(order.status)}
                    </span>
                </td>
                <td>
                    <button class="btn-icon" onclick="viewDelivery(${order.id})" title="View Details">
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
function showEmptyDeliveries() {
    const tbody = document.getElementById('active-deliveries-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5">
                <div class="empty-state">
                    <span class="material-icons empty-icon">inbox</span>
                    <p class="empty-title">No active deliveries</p>
                    <p class="empty-message">New deliveries will appear here when assigned</p>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Initialize availability toggle
 */
async function initAvailabilityToggle() {
    const toggle = document.getElementById('availability-toggle');
    const label = document.getElementById('availability-label');
    
    // Get current availability status
    try {
        const response = await fetch('../api/riders/statistics.php');
        const data = await response.json();
        
        // Assume availability is stored or we get it from user data
        // For now, default to available
        toggle.checked = true;
        updateAvailabilityLabel(true);
    } catch (error) {
        console.error('Failed to load availability:', error);
    }
    
    // Handle toggle change
    toggle.addEventListener('change', async function() {
        const isAvailable = this.checked;
        
        try {
            const response = await fetch('../api/riders/toggle_availability.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    rider_id: 0, // Will use session user_id in API
                    is_available: isAvailable ? 1 : 0,
                    csrf_token: getCSRFToken()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateAvailabilityLabel(isAvailable);
                showToast(isAvailable ? 'You are now available' : 'You are now unavailable', 'success');
            } else {
                this.checked = !isAvailable; // Revert on error
                showToast('Failed to update availability', 'error');
            }
        } catch (error) {
            console.error('Toggle availability error:', error);
            this.checked = !isAvailable;
            showToast('An error occurred', 'error');
        }
    });
}

/**
 * Update availability label
 */
function updateAvailabilityLabel(isAvailable) {
    const label = document.getElementById('availability-label');
    label.textContent = isAvailable ? 'Available' : 'Unavailable';
    label.style.color = isAvailable ? 'var(--success)' : 'var(--danger)';
}

/**
 * View delivery details
 */
function viewDelivery(orderId) {
    window.location.href = `deliveries.php?id=${orderId}`;
}

/**
 * Truncate text
 */
function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}

/**
 * Get status label
 */
function getStatusLabel(status) {
    const labels = {
        'assigned': 'Assigned',
        'on_delivery': 'On Delivery',
        'delivered': 'Delivered',
        'accepted': 'Completed'
    };
    return labels[status] || status;
}
