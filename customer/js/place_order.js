/**
 * ============================================================================
 * AZEU WATER STATION - PLACE ORDER JAVASCRIPT
 * ============================================================================
 * 
 * Purpose: Order placement logic
 * Functions: Item selection, cart management, order submission
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

let cart = [];
let deliveryFee = 50;
let availableItems = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadItems();
    loadAddresses();
    initDeliveryTypeToggle();
    initPaymentToggle();
    initPlaceOrderButton();
});

/**
 * Load available inventory items
 */
async function loadItems() {
    try {
        const response = await fetch('../api/inventory/list.php?available_only=true');
        const data = await response.json();
        
        if (data.success) {
            availableItems = data.items;
            renderItems(data.items);
        }
    } catch (error) {
        console.error('Failed to load items:', error);
        showToast('Failed to load items', 'error');
    }
}

/**
 * Render inventory items
 */
function renderItems(items) {
    const grid = document.getElementById('items-grid');
    
    if (items.length === 0) {
        grid.innerHTML = '<div class="empty-state"><p>No items available</p></div>';
        return;
    }
    
    let html = '';
    
    items.forEach(item => {
        const inCart = cart.find(c => c.inventory_id === item.id);
        const quantity = inCart ? inCart.quantity : 0;
        
        html += `
            <div class="item-card ${quantity > 0 ? 'selected' : ''}" data-item-id="${item.id}">
                <div class="item-icon-container">
                    ${item.item_icon ? 
                        `<img src="../${item.item_icon}" alt="${item.item_name}">` : 
                        '<span class="material-icons" style="font-size: 48px; color: var(--primary);">water_drop</span>'
                    }
                </div>
                <div class="item-name">${item.item_name}</div>
                <div class="item-price">${formatCurrency(item.price)}</div>
                <div class="item-stock">Stock: ${item.stock_count}</div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)" ${quantity === 0 ? 'disabled' : ''}>-</button>
                    <input type="number" class="qty-input" value="${quantity}" min="0" max="${item.stock_count}" 
                        onchange="setQuantity(${item.id}, this.value)" readonly>
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)" ${quantity >= item.stock_count ? 'disabled' : ''}>+</button>
                </div>
            </div>
        `;
    });
    
    grid.innerHTML = html;
}

/**
 * Update item quantity
 */
function updateQuantity(itemId, delta) {
    const item = availableItems.find(i => i.id === itemId);
    if (!item) return;
    
    const cartItem = cart.find(c => c.inventory_id === itemId);
    let newQuantity = (cartItem ? cartItem.quantity : 0) + delta;
    
    if (newQuantity < 0) newQuantity = 0;
    if (newQuantity > item.stock_count) newQuantity = item.stock_count;
    
    setQuantity(itemId, newQuantity);
}

/**
 * Set item quantity
 */
function setQuantity(itemId, quantity) {
    quantity = parseInt(quantity) || 0;
    const item = availableItems.find(i => i.id === itemId);
    if (!item) return;
    
    if (quantity > item.stock_count) quantity = item.stock_count;
    if (quantity < 0) quantity = 0;
    
    const existingIndex = cart.findIndex(c => c.inventory_id === itemId);
    
    if (quantity === 0) {
        if (existingIndex !== -1) {
            cart.splice(existingIndex, 1);
        }
    } else {
        if (existingIndex !== -1) {
            cart[existingIndex].quantity = quantity;
        } else {
            cart.push({
                inventory_id: itemId,
                quantity: quantity
            });
        }
    }
    
    renderItems(availableItems);
    updateCartSummary();
}

/**
 * Update cart summary
 */
function updateCartSummary() {
    const cartItemsDiv = document.getElementById('cart-items');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="empty-state" style="padding: 40px 20px;">
                <span class="material-icons empty-icon" style="font-size: 48px;">shopping_cart</span>
                <p class="empty-message">No items added</p>
            </div>
        `;
        document.getElementById('subtotal').textContent = '₱0.00';
        document.getElementById('total').textContent = '₱0.00';
        document.getElementById('place-order-btn').disabled = true;
        return;
    }
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach(cartItem => {
        const item = availableItems.find(i => i.id === cartItem.inventory_id);
        if (!item) return;
        
        const itemTotal = item.price * cartItem.quantity;
        subtotal += itemTotal;
        
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text-primary);">${item.item_name}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">${formatCurrency(item.price)} × ${cartItem.quantity}</div>
                </div>
                <div style="font-weight: 700; color: var(--primary);">${formatCurrency(itemTotal)}</div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
    const finalDeliveryFee = deliveryType === 'delivery' ? deliveryFee : 0;
    const total = subtotal + finalDeliveryFee;
    
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('delivery-fee').textContent = formatCurrency(finalDeliveryFee);
    document.getElementById('total').textContent = formatCurrency(total);
    
    document.getElementById('place-order-btn').disabled = false;
}

/**
 * Load customer addresses
 */
async function loadAddresses() {
    try {
        const response = await fetch('../api/addresses/list.php');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('address-select');
            let html = '<option value="">Select delivery address...</option>';
            
            data.addresses.forEach(addr => {
                html += `<option value="${addr.id}" ${addr.is_default ? 'selected' : ''}>
                    ${addr.label} - ${addr.full_address}
                </option>`;
            });
            
            select.innerHTML = html;
        }
    } catch (error) {
        console.error('Failed to load addresses:', error);
    }
}

/**
 * Initialize delivery type toggle
 */
function initDeliveryTypeToggle() {
    const options = document.querySelectorAll('.delivery-type-option');
    const addressSection = document.getElementById('address-section');
    const deliveryFeeRow = document.getElementById('delivery-fee-row');
    
    options.forEach(option => {
        option.addEventListener('click', function() {
            options.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            
            const type = this.dataset.type;
            
            if (type === 'delivery') {
                addressSection.style.display = 'block';
                deliveryFeeRow.style.display = 'flex';
            } else {
                addressSection.style.display = 'none';
                deliveryFeeRow.style.display = 'none';
            }
            
            updateCartSummary();
        });
    });
}

/**
 * Initialize payment toggle
 */
function initPaymentToggle() {
    const options = document.querySelectorAll('.payment-option');
    
    options.forEach(option => {
        option.addEventListener('click', function() {
            options.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/**
 * Initialize place order button
 */
function initPlaceOrderButton() {
    document.getElementById('place-order-btn').addEventListener('click', placeOrder);
}

/**
 * Place order
 */
async function placeOrder() {
    if (cart.length === 0) {
        showToast('Please add items to your order', 'warning');
        return;
    }
    
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
    const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
    const addressId = parseInt(document.getElementById('address-select').value);
    const orderNotes = document.getElementById('order-notes').value.trim();
    
    if (deliveryType === 'delivery' && !addressId) {
        showToast('Please select a delivery address', 'warning');
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch('../api/orders/create.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                items: cart,
                delivery_type: deliveryType,
                payment_type: paymentType,
                address_id: addressId,
                order_notes: orderNotes,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Order placed successfully!', 'success');
            setTimeout(() => {
                window.location.href = `orders.php?id=${data.order_id}`;
            }, 1500);
        } else {
            showToast(data.message || 'Failed to place order', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Place order error:', error);
        showToast('An error occurred. Please try again', 'error');
    }
}
