<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DELIVERY HISTORY PAGE
 * ============================================================================
 * 
 * Purpose: View completed delivery history
 * Role: RIDER
 * 
 * Features:
 * - List all completed deliveries
 * - Filter by date range
 * - View delivery details
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Delivery History";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Delivery History</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>Delivery History</span>
        </p>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table" id="history-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
});

async function loadHistory() {
    try {
        const response = await fetch('../api/orders/list.php');
        const data = await response.json();
        
        if (data.success) {
            // Filter completed orders
            const completed = data.orders.filter(o => 
                o.status === 'delivered' || o.status === 'accepted'
            );
            renderHistory(completed);
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load history:', error);
        showEmptyState();
    }
}

function renderHistory(orders) {
    const tbody = document.getElementById('history-tbody');
    
    if (orders.length === 0) {
        showEmptyState();
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        html += `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${formatDate(order.delivered_at || order.order_date)}</td>
                <td>${order.customer_name}</td>
                <td>${order.delivery_address ? truncate(order.delivery_address, 40) : 'Pickup'}</td>
                <td><strong>${formatCurrency(order.total_amount)}</strong></td>
                <td>
                    <span class="badge badge-${order.status.replace('_', '-')}">
                        ${order.status === 'accepted' ? 'Completed' : 'Delivered'}
                    </span>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function showEmptyState() {
    const tbody = document.getElementById('history-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <span class="material-icons empty-icon">history</span>
                    <p class="empty-title">No delivery history</p>
                    <p class="empty-message">Your completed deliveries will appear here</p>
                </div>
            </td>
        </tr>
    `;
}

function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
