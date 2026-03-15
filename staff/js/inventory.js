/**
 * ============================================================================
 * AZEU WATER STATION - STAFF INVENTORY JAVASCRIPT
 * ============================================================================
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    loadInventory();

    if (!isStaffReadOnly) {
        loadDefaultItems();
        document.getElementById('item-form').addEventListener('submit', saveItem);
        document.getElementById('restock-form').addEventListener('submit', restockItem);
    }

    // Responsive items per page on resize
    window.addEventListener('resize', function() {
        const newPerPage = getItemsPerPage();
        if (newPerPage !== itemsPerPage) {
            itemsPerPage = newPerPage;
            currentPage = 1;
        }
        if (inventoryData.length > 0) {
            renderInventory(inventoryData);
        }
    });

    // Sortable column header clicks
    document.querySelectorAll('th.sortable-th[data-sort]').forEach(th => {
        th.addEventListener('click', function() {
            const asc = this.dataset.sort;
            const desc = this.dataset.sortDesc;
            const icon = this.querySelector('.sort-icon');
            const isCurrentAsc = this.classList.contains('th-asc');

            // Reset all headers
            document.querySelectorAll('th.sortable-th').forEach(h => {
                h.classList.remove('th-sorted', 'th-asc', 'th-desc');
                const i = h.querySelector('.sort-icon');
                if (i) i.textContent = 'unfold_more';
            });

            let sortKey;
            if (!isCurrentAsc) {
                sortKey = asc;
                this.classList.add('th-sorted', 'th-asc');
                if (icon) icon.textContent = 'arrow_upward';
            } else {
                sortKey = desc;
                this.classList.add('th-sorted', 'th-desc');
                if (icon) icon.textContent = 'arrow_downward';
            }

            const matchBtn = document.querySelector(`.filter-btn[data-sort="${sortKey}"]`);
            applySortFilter(matchBtn || this, sortKey);
        });
    });
});

async function loadSettings() {
    try {
        const response = await fetch('../api/settings/get.php');
        const data = await response.json();

        if (data.success && data.settings) {
            lowStockThreshold = parseInt(data.settings.low_stock_threshold) || 10;
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
}

async function loadDefaultItems() {
    try {
        const response = await fetch('../api/settings/get.php');
        const data = await response.json();

        const optionsContainer = document.getElementById('item-select-options');
        if (!optionsContainer) return;

        if (data.success && data.settings && data.settings.default_item_names) {
            const items = data.settings.default_item_names.split('\n').filter(item => item.trim() !== '');

            optionsContainer.innerHTML = '';

            items.forEach(item => {
                const option = document.createElement('div');
                option.className = 'custom-select-option';
                option.dataset.value = item.trim();
                option.textContent = item.trim();
                optionsContainer.appendChild(option);
            });

            const customOption = document.createElement('div');
            customOption.className = 'custom-select-option custom-option';
            customOption.dataset.value = '__custom__';
            customOption.textContent = 'Custom/Other';
            optionsContainer.appendChild(customOption);
        } else {
            optionsContainer.innerHTML = '';
            const customOption = document.createElement('div');
            customOption.className = 'custom-select-option custom-option';
            customOption.dataset.value = '__custom__';
            customOption.textContent = 'Custom/Other';
            optionsContainer.appendChild(customOption);
        }
    } catch (error) {
        console.error('Error loading default items:', error);
    }
}

let inventoryData = [];
let currentPage = 1;
let itemsPerPage = window.innerWidth <= 1024 ? 10 : 20;
let lowStockThreshold = 10;

function getItemsPerPage() {
    return window.innerWidth <= 1024 ? 10 : 20;
}

async function loadInventory() {
    try {
        const response = await fetch('../api/inventory/list.php');
        const data = await response.json();

        const tbody = document.getElementById('inventory-tbody');
        const colSpan = isStaffReadOnly ? 5 : 6;

        if (data.success && data.items.length > 0) {
            inventoryData = data.items;
            renderInventory(inventoryData);
        } else {
            inventoryData = [];
            tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align: center; padding: 40px; color: var(--text-secondary);">No items found</td></tr>`;
            const cardsContainer = document.getElementById('inventory-cards');
            if (cardsContainer) {
                cardsContainer.innerHTML = '<div class="inventory-cards-empty"><span class="material-icons">inventory_2</span><p>No items found</p></div>';
            }
            updateInventoryPagination(0);
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
        showToast('Failed to load inventory', 'error');
    }
}

function renderInventory(items) {
    const tbody = document.getElementById('inventory-tbody');
    const cardsContainer = document.getElementById('inventory-cards');
    const colSpan = isStaffReadOnly ? 5 : 6;

    const totalPages = Math.ceil(items.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedItems = items.slice(startIndex, endIndex);

    updateInventoryPagination(totalPages);

    if (paginatedItems.length > 0) {
        let html = '';
        paginatedItems.forEach((item, index) => {
            const rowNumber = startIndex + index + 1;
            const stockClass = item.stock_count === 0 ? 'danger' : (item.stock_count <= lowStockThreshold ? 'warning' : 'success');

            let displayStatus = item.status;
            let statusClass = item.status;

            if (item.stock_count === 0) {
                displayStatus = 'out of stock';
                statusClass = 'out_of_stock';
            } else if (item.stock_count <= lowStockThreshold && item.status === 'active') {
                displayStatus = 'low stock';
                statusClass = 'low_stock';
            }

            html += `
                <tr>
                    <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">${rowNumber}</td>
                    <td><strong>${item.item_name}</strong></td>
                    <td>${formatCurrency(item.price)}</td>
                    <td><span class="badge badge-${stockClass}">${item.stock_count}</span></td>
                    <td><span class="badge badge-${statusClass}">${displayStatus}</span></td>
                    ${!isStaffReadOnly ? `
                    <td>
                        <button class="btn-icon" onclick="showRestock(${item.id})" title="Restock">
                            <span class="material-icons">add_circle</span>
                        </button>
                        <button class="btn-icon" onclick="editItem(${item.id})" title="Edit">
                            <span class="material-icons">edit</span>
                        </button>
                        <button class="btn-icon" onclick="confirmDeleteItem(${item.id}, '${item.item_name.replace(/'/g, "\\'")}')" title="Delete">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>` : ''}
                </tr>
            `;
        });
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align: center; padding: 40px; color: var(--text-secondary);">No items found</td></tr>`;
    }

    if (cardsContainer) {
        if (paginatedItems.length > 0) {
            renderInventoryCards(paginatedItems, cardsContainer, startIndex);
        } else {
            cardsContainer.innerHTML = '<div class="inventory-cards-empty"><span class="material-icons">inventory_2</span><p>No items found</p></div>';
        }
    }
}

function renderInventoryCards(items, container, startIndex = 0) {
    let cardsHtml = '<div class="inventory-cards-grid">';
    items.forEach((item, index) => {
        const cardNumber = startIndex + index + 1;
        const stockClass = item.stock_count === 0 ? 'danger' : (item.stock_count <= lowStockThreshold ? 'warning' : 'success');

        let displayStatus = item.status;
        let statusClass = item.status;
        if (item.stock_count === 0) {
            displayStatus = 'out of stock';
            statusClass = 'out_of_stock';
        } else if (item.stock_count <= lowStockThreshold && item.status === 'active') {
            displayStatus = 'low stock';
            statusClass = 'low_stock';
        }

        const actionButtons = !isStaffReadOnly ? `
            <div class="inventory-card-actions">
                <button class="btn-icon" onclick="showRestock(${item.id})" title="Restock">
                    <span class="material-icons">add_circle</span>
                </button>
                <button class="btn-icon" onclick="editItem(${item.id})" title="Edit">
                    <span class="material-icons">edit</span>
                </button>
                <button class="btn-icon" onclick="confirmDeleteItem(${item.id}, '${item.item_name.replace(/'/g, "\\'")}')" title="Delete">
                    <span class="material-icons">delete</span>
                </button>
            </div>` : '';

        cardsHtml += `
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <div class="inventory-card-header-left">
                        <span class="material-icons">tag</span>
                        <span>${cardNumber}</span>
                    </div>
                    ${actionButtons}
                </div>
                <div class="inventory-card-row">
                    <div class="inventory-card-label"><span class="material-icons">inventory_2</span> Item</div>
                    <div class="inventory-card-value"><strong>${item.item_name}</strong></div>
                </div>
                <div class="inventory-card-row">
                    <div class="inventory-card-label"><span class="material-icons">payments</span> Price</div>
                    <div class="inventory-card-value price-highlight">${formatCurrency(item.price)}</div>
                </div>
                <div class="inventory-card-row">
                    <div class="inventory-card-label"><span class="material-icons">warehouse</span> Stock</div>
                    <div class="inventory-card-value"><span class="badge badge-${stockClass}">${item.stock_count}</span></div>
                </div>
                <div class="inventory-card-row">
                    <div class="inventory-card-label"><span class="material-icons">info</span> Status</div>
                    <div class="inventory-card-value"><span class="badge badge-${statusClass}">${displayStatus}</span></div>
                </div>
            </div>
        `;
    });
    cardsHtml += '</div>';
    container.innerHTML = cardsHtml;
}

function updateInventoryPagination(totalPages) {
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const paginationWrapper = document.getElementById('pagination-wrapper');

    const pageInfoMobile = document.getElementById('page-info-mobile');
    const prevBtnMobile = document.getElementById('prev-btn-mobile');
    const nextBtnMobile = document.getElementById('next-btn-mobile');
    const paginationWrapperMobile = document.getElementById('pagination-wrapper-mobile');

    if (!pageInfo) return;

    if (totalPages <= 1) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
        return;
    }

    if (window.innerWidth <= 1024) {
        if (paginationWrapper) paginationWrapper.style.display = 'none';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'flex';
    } else {
        if (paginationWrapper) paginationWrapper.style.display = 'flex';
        if (paginationWrapperMobile) paginationWrapperMobile.style.display = 'none';
    }

    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    if (pageInfoMobile) pageInfoMobile.textContent = `Page ${currentPage} of ${totalPages}`;

    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
    if (prevBtnMobile) prevBtnMobile.disabled = currentPage <= 1;
    if (nextBtnMobile) nextBtnMobile.disabled = currentPage >= totalPages;
}

function nextPage() {
    const totalPages = Math.ceil(inventoryData.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderInventory(inventoryData);
    }
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderInventory(inventoryData);
    }
}

function applySortFilter(button, sortBy) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    currentPage = 1;

    let sortedItems = [...inventoryData];

    switch (sortBy) {
        case 'name':
            sortedItems.sort((a, b) => a.item_name.localeCompare(b.item_name));
            break;
        case 'name-desc':
            sortedItems.sort((a, b) => b.item_name.localeCompare(a.item_name));
            break;
        case 'stock-asc':
            sortedItems.sort((a, b) => a.stock_count - b.stock_count);
            break;
        case 'stock-desc':
            sortedItems.sort((a, b) => b.stock_count - a.stock_count);
            break;
        case 'price-asc':
            sortedItems.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
            break;
        case 'price-desc':
            sortedItems.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
            break;
        case 'status':
            const statusOrder = { 'active': 1, 'out_of_stock': 2, 'inactive': 3 };
            sortedItems.sort((a, b) => statusOrder[a.status] - statusOrder[b.status]);
            break;
    }

    renderInventory(sortedItems);
}

// ============================================================================
// NON-STAFF (ADMIN) FUNCTIONS
// ============================================================================

function showAddItem() {
    document.getElementById('item-modal-title').textContent = 'Add Item';
    document.getElementById('item-form').reset();
    document.getElementById('item-id').value = '';
    document.getElementById('custom-item-name-group').style.display = 'none';
    document.getElementById('item-name-custom').required = false;
    document.getElementById('item-name-select').required = true;

    const selectedText = document.querySelector('#item-select-trigger .selected-text');
    selectedText.textContent = 'Select an item...';
    selectedText.classList.add('placeholder');
    document.getElementById('item-name-select').value = '';

    loadDefaultItems();
    openModal('item-modal');
}

async function saveItem(e) {
    e.preventDefault();

    const itemId = document.getElementById('item-id').value;
    const url = itemId ? '../api/inventory/update.php' : '../api/inventory/create.php';

    const selectValue = document.getElementById('item-name-select').value;
    const itemName = selectValue === '__custom__'
        ? document.getElementById('item-name-custom').value
        : selectValue;

    if (!itemName || itemName.trim() === '') {
        showToast('Please enter an item name', 'error');
        return;
    }

    const payload = {
        item_name: itemName.trim(),
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

        if (data.success && data.item) {
            document.getElementById('item-modal-title').textContent = 'Edit Item';
            document.getElementById('item-id').value = data.item.id;

            await loadDefaultItems();

            const optionsContainer = document.getElementById('item-select-options');
            const options = optionsContainer.querySelectorAll('.custom-select-option');
            const selectedText = document.querySelector('#item-select-trigger .selected-text');
            const hiddenInput = document.getElementById('item-name-select');
            let optionExists = false;

            for (let option of options) {
                if (option.dataset.value === data.item.item_name) {
                    option.classList.add('selected');
                    selectedText.textContent = data.item.item_name;
                    selectedText.classList.remove('placeholder');
                    hiddenInput.value = data.item.item_name;
                    optionExists = true;
                    break;
                }
            }

            if (!optionExists) {
                const customOpt = Array.from(options).find(opt => opt.dataset.value === '__custom__');
                if (customOpt) {
                    customOpt.classList.add('selected');
                    selectedText.textContent = 'Custom/Other';
                    selectedText.classList.remove('placeholder');
                }

                hiddenInput.value = '__custom__';
                const customGroup = document.getElementById('custom-item-name-group');
                const customInput = document.getElementById('item-name-custom');
                if (customGroup && customInput) {
                    customGroup.style.display = 'block';
                    customInput.value = data.item.item_name;
                    customInput.required = true;
                    hiddenInput.required = false;
                }
            }

            document.getElementById('item-price').value = data.item.price;
            document.getElementById('item-stock').value = data.item.stock_count;
            openModal('item-modal');
        } else {
            showToast(data.message || 'Failed to load item', 'error');
        }
    } catch (error) {
        console.error('Edit item error:', error);
        showToast('Error loading item: ' + error.message, 'error');
    }
}

function confirmDeleteItem(itemId, itemName) {
    Swal.fire({
        title: 'Delete Item?',
        html: `Are you sure you want to delete <strong>${itemName}</strong>?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            deleteItem(itemId, itemName);
        }
    });
}

async function deleteItem(itemId, itemName) {
    try {
        const response = await fetch('../api/inventory/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                item_id: parseInt(itemId),
                csrf_token: getCSRFToken()
            })
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: 'Deleted!',
                text: `${itemName} has been deleted successfully.`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            loadInventory();
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to delete item',
                icon: 'error',
                confirmButtonColor: '#007bff'
            });
        }
    } catch (error) {
        console.error('Delete item error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while deleting the item',
            icon: 'error',
            confirmButtonColor: '#007bff'
        });
    }
}
