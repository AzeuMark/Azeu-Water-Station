<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF INVENTORY VIEW (READ-ONLY)
 * ============================================================================
 * 
 * Purpose: View-only inventory for staff users
 * Role: STAFF
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Inventory";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$isStaff = ($_SESSION['role'] === ROLE_STAFF);
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="content-title">Inventory</h1>
            <?php if (!$isStaff): ?>
            <button class="btn btn-primary" onclick="showAddItem()">
                <span class="material-icons">add</span> Add Item
            </button>
            <?php else: ?>
            <span class="badge" style="background: var(--surface); color: var(--text-muted); padding: 8px 14px; border-radius: 8px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-icons" style="font-size: 16px;">visibility</span>
                View Only
            </span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <?php if (!$isStaff): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="inventory-tbody">
                    <tr><td colspan="<?php echo $isStaff ? '4' : '5'; ?>" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php if (!$isStaff): ?>
<!-- Add/Edit Item Modal -->
<div class="modal-overlay" id="item-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3 id="item-modal-title">Add Item</h3>
            <button class="modal-close" onclick="closeModal('item-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="item-form">
            <div class="modal-body">
                <input type="hidden" id="item-id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Item Name</label>
                    <input type="text" id="item-name" class="form-select" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Price</label>
                    <input type="number" id="item-price" class="form-select" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" id="item-stock" class="form-select" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('item-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal-overlay" id="restock-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Restock Item</h3>
            <button class="modal-close" onclick="closeModal('restock-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="restock-form">
            <div class="modal-body">
                <input type="hidden" id="restock-item-id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Quantity to Add</label>
                    <input type="number" id="restock-qty" class="form-select" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('restock-modal')">Cancel</button>
                <button type="submit" class="btn btn-success">Restock</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
const isStaffReadOnly = <?php echo $isStaff ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', function() {
    loadInventory();
    
    if (!isStaffReadOnly) {
        // Only init form handlers for admin/super_admin
        if (document.getElementById('item-form')) {
            document.getElementById('item-form').addEventListener('submit', saveItem);
        }
        if (document.getElementById('restock-form')) {
            document.getElementById('restock-form').addEventListener('submit', submitRestock);
        }
    }
});

async function loadInventory() {
    try {
        const response = await fetch('../api/inventory/list.php');
        const data = await response.json();
        
        const tbody = document.getElementById('inventory-tbody');
        
        if (data.success && data.items.length > 0) {
            let html = '';
            data.items.forEach(item => {
                const statusClass = item.stock <= 0 ? 'out_of_stock' : item.stock <= (item.low_stock_threshold || 10) ? 'low_stock' : 'in_stock';
                const statusLabel = item.stock <= 0 ? 'Out of Stock' : item.stock <= (item.low_stock_threshold || 10) ? 'Low Stock' : 'In Stock';
                
                html += `
                    <tr>
                        <td><strong>${item.item_name}</strong></td>
                        <td>${formatCurrency(item.price)}</td>
                        <td>${item.stock}</td>
                        <td><span class="badge badge-${statusClass}">${statusLabel}</span></td>
                        ${!isStaffReadOnly ? `
                        <td style="white-space: nowrap;">
                            <button class="btn-icon" onclick="editItem(${item.id})" title="Edit">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" onclick="restockItem(${item.id})" title="Restock" style="color: var(--success);">
                                <span class="material-icons">add_circle</span>
                            </button>
                            <button class="btn-icon" onclick="deleteItem(${item.id})" title="Delete" style="color: var(--danger);">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>` : ''}
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            const colspan = isStaffReadOnly ? 4 : 5;
            tbody.innerHTML = `<tr><td colspan="${colspan}"><div class="empty-state"><p>No inventory items</p></div></td></tr>`;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

<?php if (!$isStaff): ?>
// Admin-only functions
function showAddItem() {
    document.getElementById('item-modal-title').textContent = 'Add Item';
    document.getElementById('item-form').reset();
    document.getElementById('item-id').value = '';
    openModal('item-modal');
}

async function editItem(id) {
    try {
        const response = await fetch(`../api/inventory/get.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('item-modal-title').textContent = 'Edit Item';
            document.getElementById('item-id').value = data.item.id;
            document.getElementById('item-name').value = data.item.item_name;
            document.getElementById('item-price').value = data.item.price;
            document.getElementById('item-stock').value = data.item.stock;
            openModal('item-modal');
        }
    } catch (error) {
        showToast('Error loading item', 'error');
    }
}

async function saveItem(e) {
    e.preventDefault();
    
    const id = document.getElementById('item-id').value;
    const payload = {
        item_name: document.getElementById('item-name').value,
        price: parseFloat(document.getElementById('item-price').value),
        stock: parseInt(document.getElementById('item-stock').value),
        csrf_token: getCSRFToken()
    };
    
    if (id) payload.id = parseInt(id);
    
    try {
        const url = id ? '../api/inventory/update.php' : '../api/inventory/create.php';
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(id ? 'Item updated' : 'Item added', 'success');
            closeModal('item-modal');
            loadInventory();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

function restockItem(id) {
    document.getElementById('restock-item-id').value = id;
    document.getElementById('restock-qty').value = '';
    openModal('restock-modal');
}

async function submitRestock(e) {
    e.preventDefault();
    
    try {
        const response = await fetch('../api/inventory/restock.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id: parseInt(document.getElementById('restock-item-id').value),
                quantity: parseInt(document.getElementById('restock-qty').value),
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Restocked successfully', 'success');
            closeModal('restock-modal');
            loadInventory();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

async function deleteItem(id) {
    if (!confirm('Delete this item permanently?')) return;
    
    try {
        const response = await fetch('../api/inventory/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Item deleted', 'success');
            loadInventory();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
