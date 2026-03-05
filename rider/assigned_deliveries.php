<?php
/**
 * ============================================================================
 * AZEU WATER STATION - ASSIGNED DELIVERIES PAGE
 * ============================================================================
 * 
 * Purpose: View and manage assigned deliveries (not yet on delivery)
 * Role: RIDER
 * 
 * Features:
 * - List all assigned deliveries
 * - Reorder delivery priority (drag & drop)
 * - Start delivery (change status to on_delivery)
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Assigned Deliveries";
$page_css = "deliveries.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Assigned Deliveries</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>Assigned Deliveries</span>
        </p>
    </div>
    
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Delivery Queue</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0;">
                <span class="material-icons" style="font-size: 18px; vertical-align: middle;">info</span>
                Drag to reorder priority
            </p>
        </div>
        
        <div id="assigned-deliveries-list">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
</main>

<script>
let assignedOrders = [];

document.addEventListener('DOMContentLoaded', function() {
    loadAssignedDeliveries();
});

async function loadAssignedDeliveries() {
    try {
        const response = await fetch('../api/orders/list.php?status=assigned');
        const data = await response.json();
        
        if (data.success) {
            assignedOrders = data.orders;
            renderAssignedDeliveries(data.orders);
            initSortable();
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load assigned deliveries:', error);
        showEmptyState();
    }
}

function renderAssignedDeliveries(orders) {
    const container = document.getElementById('assigned-deliveries-list');
    
    if (orders.length === 0) {
        showEmptyState();
        return;
    }
    
    let html = '<div id="sortable-list">';
    
    orders.forEach((order, index) => {
        html += `
            <div class="delivery-card sortable-item" data-order-id="${order.id}">
                <div class="drag-handle">
                    <span class="material-icons">drag_indicator</span>
                </div>
                <div style="flex: 1;">
                    <div class="delivery-header">
                        <div>
                            <h4>Order #${order.id}</h4>
                            <p style="font-size: 0.9rem; color: var(--text-muted);">${order.customer_name}</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Priority</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">#${index + 1}</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 20px; margin-top: 12px;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Address</div>
                            <div style="font-size: 0.95rem;">${order.delivery_address}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Amount</div>
                            <div style="font-weight: 700; color: var(--primary);">${formatCurrency(order.total_amount)}</div>
                        </div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="startDelivery(${order.id})" style="white-space: nowrap;">
                        <span class="material-icons">play_arrow</span>
                        Start
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    container.innerHTML = html;
}

function showEmptyState() {
    const container = document.getElementById('assigned-deliveries-list');
    container.innerHTML = `
        <div class="empty-state">
            <span class="material-icons empty-icon">assignment</span>
            <p class="empty-title">No assigned deliveries</p>
            <p class="empty-message">New deliveries will appear here when assigned to you</p>
        </div>
    `;
}

function initSortable() {
    const list = document.getElementById('sortable-list');
    if (!list || typeof Sortable === 'undefined') return;
    
    Sortable.create(list, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: savePriority
    });
}

async function savePriority() {
    const items = document.querySelectorAll('.sortable-item');
    const priorities = [];
    
    items.forEach((item, index) => {
        priorities.push({
            order_id: parseInt(item.dataset.orderId),
            priority: index + 1
        });
    });
    
    try {
        await fetch('../api/riders/update_priority.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                priorities: priorities,
                csrf_token: getCSRFToken()
            })
        });
        
        // Update priority numbers in UI
        items.forEach((item, index) => {
            const priorityNum = item.querySelector('.delivery-header > div:last-child > div:last-child');
            if (priorityNum) {
                priorityNum.textContent = '#' + (index + 1);
            }
        });
    } catch (error) {
        console.error('Failed to save priority:', error);
    }
}

async function startDelivery(orderId) {
    const confirm = await Swal.fire({
        title: 'Start Delivery',
        text: 'Begin this delivery?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Start',
        confirmButtonColor: '#1565C0'
    });
    
    if (!confirm.isConfirmed) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/orders/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: orderId,
                status: 'on_delivery',
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Delivery started!', 'success');
            setTimeout(() => {
                window.location.href = 'deliveries.php';
            }, 1000);
        } else {
            showToast(data.message || 'Failed to start delivery', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Start delivery error:', error);
        showToast('An error occurred', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
