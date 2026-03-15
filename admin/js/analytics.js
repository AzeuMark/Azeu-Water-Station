/**
 * ============================================================================
 * AZEU WATER STATION - ANALYTICS JAVASCRIPT
 * ============================================================================
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

let currentPeriod = 'month';
let revenueChart, statusChart;

document.addEventListener('DOMContentLoaded', function() {
    initPeriodFilter();
    loadAnalytics();
});

function initPeriodFilter() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            loadAnalytics();
        });
    });
}

async function loadAnalytics() {
    await Promise.all([
        loadRevenue(),
        loadOrderAnalytics()
    ]);
}

async function loadRevenue() {
    try {
        const response = await fetch(`../api/analytics/revenue.php?period=${currentPeriod}`);
        const data = await response.json();
        
        if (data.success) {
            const analytics = data.analytics;
            
            document.getElementById('total-revenue').textContent = formatCurrency(analytics.total_revenue);
            document.getElementById('avg-order-value').textContent = formatCurrency(analytics.average_order_value);
            document.getElementById('delivery-fees').textContent = formatCurrency(analytics.total_delivery_fees);
            
            renderRevenueChart(analytics.revenue_trends);
            renderTopProducts(analytics.top_items);
            renderTopCustomers(analytics.top_customers);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function loadOrderAnalytics() {
    try {
        const response = await fetch(`../api/analytics/orders.php?period=${currentPeriod}`);
        const data = await response.json();
        
        if (data.success) {
            const analytics = data.analytics;
            
            document.getElementById('total-orders').textContent = analytics.total_orders;
            document.getElementById('completed-orders').textContent = analytics.completed_orders;
            document.getElementById('active-orders').textContent = analytics.active_orders;
            document.getElementById('pending-orders').textContent = analytics.pending_orders;
            document.getElementById('cancelled-orders').textContent = analytics.cancelled_orders;
            
            renderStatusChart(analytics.status_breakdown);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function renderRevenueChart(trends) {
    const ctx = document.getElementById('revenue-chart').getContext('2d');
    
    if (revenueChart) revenueChart.destroy();
    
    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trends.map(t => t.date),
            datasets: [{
                label: 'Revenue',
                data: trends.map(t => t.revenue),
                borderColor: '#1565C0',
                backgroundColor: 'rgba(21, 101, 192, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
}

function renderStatusChart(breakdown) {
    const ctx = document.getElementById('status-chart').getContext('2d');
    
    if (statusChart) statusChart.destroy();
    
    const labels = Object.keys(breakdown);
    const data = Object.values(breakdown);
    
    // Unique colors per status for clear visual distinction
    const statusColors = {
        'pending':          '#FFA726',  // Orange
        'confirmed':        '#42A5F5',  // Blue
        'assigned':         '#7E57C2',  // Purple
        'on_delivery':      '#26C6DA',  // Cyan
        'delivered':        '#66BB6A',  // Green
        'ready_for_pickup': '#29B6F6',  // Light Blue
        'picked_up':        '#9CCC65',  // Light Green
        'cancelled':        '#EF5350',  // Red
        'accepted':         '#26A69A',  // Teal
        'reassigning':      '#FFCA28',  // Amber
        'flagged':          '#EC407A',  // Pink
    };
    
    const colors = labels.map(label => statusColors[label] || '#9E9E9E');
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.map(l => l.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())),
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: 'var(--surface-card, #ffffff)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}

function getRankLimit() {
    return window.innerWidth <= 1024 ? 5 : 10;
}

function renderTopProducts(items) {
    const container = document.getElementById('top-products');
    
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="empty-state" style="padding: 32px;"><p>No data</p></div>';
        return;
    }
    
    const limited = items.slice(0, getRankLimit());
    let html = '';
    limited.forEach((item, index) => {
        html += `
            <div class="analytics-rank-item">
                <div class="analytics-rank-number">${index + 1}</div>
                <div class="analytics-rank-info">
                    <div class="analytics-rank-name">${item.item_name}</div>
                    <div class="analytics-rank-sub">Qty sold: ${item.total_quantity}</div>
                </div>
                <div class="analytics-rank-value revenue">${formatCurrency(item.total_revenue)}</div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function renderTopCustomers(customers) {
    const container = document.getElementById('top-customers');
    
    if (!customers || customers.length === 0) {
        container.innerHTML = '<div class="empty-state" style="padding: 32px;"><p>No data</p></div>';
        return;
    }
    
    const limited = customers.slice(0, getRankLimit());
    let html = '';
    limited.forEach((customer, index) => {
        html += `
            <div class="analytics-rank-item">
                <div class="analytics-rank-number">${index + 1}</div>
                <div class="analytics-rank-info">
                    <div class="analytics-rank-name">${customer.full_name}</div>
                    <div class="analytics-rank-sub">Orders: ${customer.order_count}</div>
                </div>
                <div class="analytics-rank-value spent">${formatCurrency(customer.total_spent)}</div>
            </div>
        `;
    });
    container.innerHTML = html;
}
