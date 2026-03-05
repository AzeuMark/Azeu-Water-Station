/**
 * ============================================================================
 * AZEU WATER STATION - STAFF INVENTORY JAVASCRIPT
 * ============================================================================
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    loadInventory();
    
    document.getElementById('item-form').addEventListener('submit', saveItem);
    document.getElementById('restock-form').addEventListener('submit', restockItem);
});

async function loadInventory() {
    try {
        const response = await fetch('../api/inventory/list.php');
        const data = await response.json();
        
        const tbody = document.getElementById('inventory-tbody');
        
        if (data.success && data.items.length > 0) {
            let html = '';
            data.items.forEach(item => {
                const stockClass = item.stock_count === 0 ? 'danger' : (item.stock_count < 10 ? 'warning' : 'success');
                
                // Determine item status with low stock override
                let displayStatus = item.status;
                let statusClass = item.status;
                
                if (item.stock_count === 0) {
                    displayStatus = 'out of stock';
                    statusClass = 'out_of_stock';
                } else if (item.stock_count < 10 && item.status === 'active') {
                    displayStatus = 'low stock';
                    statusClass = 'low_stock';
                }
                
                html += `
                    <tr>
                        <td><strong>${item.item_name}</strong></td>
                        <td>${formatCurrency(item.price)}</td>
                        <td><span class="badge badge-${stockClass}">${item.stock_count}</span></td>
                        <td><span class="badge badge-${statusClass}">${displayStatus}</span></td>
                        <td>
                            <button class="btn-icon" onclick="showRestock(${item.id})" title="Restock">
                                <span class="material-icons">add_circle</span>
                            </button>
                            <button class="btn-icon" onclick="editItem(${item.id})" title="Edit">
                                <span class="material-icons">edit</span>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><p>No items</p></div></td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function showAddItem() {
    document.getElementById('item-modal-title').textContent = 'Add Item';
    document.getElementById('item-form').reset();
    document.getElementById('item-id').value = '';
    openModal('item-modal');
}

async function saveItem(e) {
    e.preventDefault();
    
    const itemId = document.getElementById('item-id').value;
    const url = itemId ? '../api/inventory/update.php' : '../api/inventory/create.php';
    
    const payload = {
        item_name: document.getElementById('item-name').value,
        price: parseFloat(document.getElementById('item-price').value),
        stock_count: parseInt(document.getElementById('item-stock').value),
        csrf_token: getCSRFToken()
    };
    
    if (itemId) payload.item_id = parseInt(itemId);
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Item saved', 'success');
            closeModal('item-modal');
            loadInventory();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('Error occurred', 'error');
    }
}

function showRestock(itemId) {
    document.getElementById('restock-item-id').value = itemId;
    document.getElementById('restock-qty').value = '';
    openModal('restock-modal');
}

async function restockItem(e) {
    e.preventDefault();
    
    const itemId = document.getElementById('restock-item-id').value;
    const qty = parseInt(document.getElementById('restock-qty').value);
    
    try {
        const response = await fetch('../api/inventory/restock.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                item_id: parseInt(itemId),
                stock_count: qty,
                mode: 'add',
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
        showToast('Error occurred', 'error');
    }
}

async function editItem(itemId) {
    try {
        const response = await fetch(`../api/inventory/get.php?id=${itemId}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('item-modal-title').textContent = 'Edit Item';
            document.getElementById('item-id').value = data.item.id;
            document.getElementById('item-name').value = data.item.item_name;
            document.getElementById('item-price').value = data.item.price;
            document.getElementById('item-stock').value = data.item.stock_count;
            openModal('item-modal');
        }
    } catch (error) {
        showToast('Error occurred', 'error');
    }
}
