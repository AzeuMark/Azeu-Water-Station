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
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.map(l => l.replace('_', ' ')),
            datasets: [{
                data: data,
                backgroundColor: ['#FFA726', '#42A5F5', '#7E57C2', '#26A69A', '#66BB6A', '#EF5350']
            }]
        },
        options: { responsive: true }
    });
}

function renderTopProducts(items) {
    const container = document.getElementById('top-products');
    
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No data</p></div>';
        return;
    }
    
    let html = '<div style="display: grid; gap: 12px;">';
    
    items.forEach((item, index) => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--surface); border-radius: var(--radius-sm);">
                <div>
                    <div style="font-weight: 600;">${index + 1}. ${item.item_name}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Qty: ${item.total_quantity}</div>
                </div>
                <div style="font-weight: 700; color: var(--primary);">${formatCurrency(item.total_revenue)}</div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function renderTopCustomers(customers) {
    const container = document.getElementById('top-customers');
    
    if (!customers || customers.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No data</p></div>';
        return;
    }
    
    let html = '<div style="display: grid; gap: 12px;">';
    
    customers.forEach((customer, index) => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--surface); border-radius: var(--radius-sm);">
                <div>
                    <div style="font-weight: 600;">${index + 1}. ${customer.full_name}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Orders: ${customer.order_count}</div>
                </div>
                <div style="font-weight: 700; color: var(--success);">${formatCurrency(customer.total_spent)}</div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}
