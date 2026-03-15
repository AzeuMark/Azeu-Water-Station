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
    initItemSearch();
    initAddressSelect();
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
 * Render inventory items (supports filtered subset)
 */
function renderItems(items) {
    const grid = document.getElementById('items-grid');
    const searchQuery = document.getElementById('items-search-input')?.value.trim().toLowerCase() || '';
    const filtered = searchQuery 
        ? items.filter(i => i.item_name.toLowerCase().includes(searchQuery))
        : items;
    
    if (items.length === 0) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p>No items available</p></div>';
        return;
    }
    
    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="items-no-results">
                <span class="material-icons">search_off</span>
                <p>No items match "<strong>${searchQuery}</strong>"</p>
            </div>`;
        return;
    }
    
    let html = '';
    
    filtered.forEach(item => {
        const inCart = cart.find(c => c.inventory_id === item.id);
        const quantity = inCart ? inCart.quantity : 0;
        const isLowStock = item.stock_count > 0 && item.stock_count <= 10;
        
        html += `
            <div class="item-card ${quantity > 0 ? 'selected' : ''}" data-item-id="${item.id}">
                <div class="item-icon-container">
                    ${item.item_icon ? 
                        `<img src="../${item.item_icon}" alt="${item.item_name}">` : 
                        '<span class="material-icons">water_drop</span>'
                    }
                </div>
                <div class="item-name">${item.item_name}</div>
                <div class="item-price">${formatCurrency(item.price)}</div>
                <div class="item-stock">
                    <span class="stock-dot ${isLowStock ? 'low' : ''}"></span>
                    ${item.stock_count} available
                </div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)" ${quantity === 0 ? 'disabled' : ''}>−</button>
                    <input type="number" class="qty-value" value="${quantity}" min="0" max="${item.stock_count}" step="1"
                        onchange="setQuantity(${item.id}, this.value)"
                        onkeydown="return !['e','E','.','+','-'].includes(event.key)"
                        onfocus="this.select()">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)" ${quantity >= item.stock_count ? 'disabled' : ''}>+</button>
                </div>
            </div>
        `;
    });
    
    grid.innerHTML = html;
}

/**
 * Initialize item search
 */
function initItemSearch() {
    const input = document.getElementById('items-search-input');
    const clearBtn = document.getElementById('items-search-clear');
    let debounceTimer;
    
    input.addEventListener('input', function() {
        clearBtn.style.display = this.value ? 'flex' : 'none';
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => renderItems(availableItems), 200);
    });
    
    clearBtn.addEventListener('click', function() {
        input.value = '';
        this.style.display = 'none';
        input.focus();
        renderItems(availableItems);
    });
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
    const cartCount = document.getElementById('cart-count');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="cart-empty">
                <span class="material-icons cart-empty__icon">shopping_cart</span>
                <p class="cart-empty__title">Your cart is empty</p>
                <p class="cart-empty__text">Add items to get started</p>
            </div>
        `;
        cartCount.textContent = '0 items';
        document.getElementById('subtotal').textContent = '₱0.00';
        document.getElementById('total').textContent = '₱0.00';
        document.getElementById('place-order-btn').disabled = true;
        return;
    }
    
    let html = '';
    let subtotal = 0;
    let totalItems = 0;
    
    cart.forEach(cartItem => {
        const item = availableItems.find(i => i.id === cartItem.inventory_id);
        if (!item) return;
        
        const itemTotal = item.price * cartItem.quantity;
        subtotal += itemTotal;
        totalItems += cartItem.quantity;
        
        html += `
            <div class="cart-item">
                <div class="cart-item__info">
                    <span class="cart-item__name">${item.item_name}</span>
                    <span class="cart-item__meta">${formatCurrency(item.price)} × ${cartItem.quantity}</span>
                </div>
                <div class="cart-item__actions">
                    <span class="cart-item__total">${formatCurrency(itemTotal)}</span>
                    <button class="cart-item__remove" onclick="setQuantity(${cartItem.inventory_id}, 0)" title="Remove item">
                        <span class="material-icons">close</span>
                    </button>
                </div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    cartCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
    
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
            const hiddenInput = document.getElementById('address-select');
            const optionsContainer = document.getElementById('address-select-options');
            const trigger = document.getElementById('address-select-trigger');
            const selectedText = trigger.querySelector('.selected-text');
            
            let html = '';
            let defaultAddr = null;
            
            data.addresses.forEach(addr => {
                const label = `${addr.label} - ${addr.full_address}`;
                if (addr.is_default) defaultAddr = { id: addr.id, label };
                html += `<div class="custom-select-option${addr.is_default ? ' selected' : ''}" data-value="${addr.id}">${label}</div>`;
            });
            
            optionsContainer.innerHTML = html;
            
            if (defaultAddr) {
                hiddenInput.value = defaultAddr.id;
                selectedText.textContent = defaultAddr.label;
                selectedText.classList.remove('placeholder');
            }
        }
    } catch (error) {
        console.error('Failed to load addresses:', error);
    }
}

/**
 * Initialize custom address select
 */
function initAddressSelect() {
    const wrapper = document.getElementById('address-select-wrapper');
    const trigger = document.getElementById('address-select-trigger');
    const optionsContainer = document.getElementById('address-select-options');
    const hiddenInput = document.getElementById('address-select');
    const selectedText = trigger.querySelector('.selected-text');
    
    trigger.addEventListener('click', function() {
        const isOpen = trigger.classList.contains('active');
        closeAllCustomSelects();
        if (!isOpen) {
            trigger.classList.add('active');
            optionsContainer.classList.add('active');
        }
    });
    
    optionsContainer.addEventListener('click', function(e) {
        const option = e.target.closest('.custom-select-option');
        if (!option) return;
        
        optionsContainer.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        
        hiddenInput.value = option.dataset.value;
        selectedText.textContent = option.textContent;
        selectedText.classList.remove('placeholder');
        
        trigger.classList.remove('active');
        optionsContainer.classList.remove('active');
    });
    
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            trigger.classList.remove('active');
            optionsContainer.classList.remove('active');
        }
    });
}

function closeAllCustomSelects() {
    document.querySelectorAll('.custom-select-trigger.active').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.custom-select-options.active').forEach(o => o.classList.remove('active'));
}

/**
 * Initialize delivery type toggle
 */
function initDeliveryTypeToggle() {
    const options = document.querySelectorAll('.delivery-option');
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
            
            updatePaymentOptions(type);
            updateCartSummary();
        });
    });
}

/**
 * Update payment options based on delivery type
 */
function updatePaymentOptions(deliveryType) {
    const codOption = document.getElementById('payment-cod');
    const pickupOption = document.getElementById('payment-pickup');
    
    if (deliveryType === 'delivery') {
        codOption.style.display = '';
        codOption.querySelector('input').disabled = false;
        codOption.querySelector('input').checked = true;
        codOption.classList.add('active');
        
        pickupOption.style.display = 'none';
        pickupOption.querySelector('input').disabled = true;
        pickupOption.querySelector('input').checked = false;
        pickupOption.classList.remove('active');
    } else {
        pickupOption.style.display = '';
        pickupOption.querySelector('input').disabled = false;
        pickupOption.querySelector('input').checked = true;
        pickupOption.classList.add('active');
        
        codOption.style.display = 'none';
        codOption.querySelector('input').disabled = true;
        codOption.querySelector('input').checked = false;
        codOption.classList.remove('active');
    }
}

/**
 * Initialize payment toggle
 */
function initPaymentToggle() {
    const container = document.getElementById('payment-options');
    
    container.addEventListener('click', function(e) {
        const option = e.target.closest('.payment-option');
        if (!option || option.classList.contains('payment-option--disabled')) return;
        if (option.querySelector('input').disabled) return;
        
        container.querySelectorAll('.payment-option').forEach(o => o.classList.remove('active'));
        option.classList.add('active');
        option.querySelector('input').checked = true;
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
