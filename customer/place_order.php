<?php
/**
 * ============================================================================
 * AZEU WATER STATION - PLACE ORDER PAGE
 * ============================================================================
 * 
 * Purpose: Order placement interface for customers
 * Role: CUSTOMER
 * 
 * Features:
 * - Browse available inventory items
 * - Add items to cart with quantity
 * - Select delivery type (delivery/pickup)
 * - Choose delivery address
 * - Select payment method
 * - View order summary and total
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Place Order";
$page_css = "place_order.css";
$page_js = "place_order.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Place Order</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>Place Order</span>
        </p>
    </div>
    
    <div class="order-layout">
        <!-- Left Column: Order Configuration -->
        <div class="order-main">
            <!-- Step 1: Select Items -->
            <section class="order-section">
                <div class="section-header">
                    <div class="section-step">1</div>
                    <div class="section-info">
                        <h3 class="section-title">Select Items</h3>
                        <p class="section-subtitle">Choose from our available products</p>
                    </div>
                </div>
                <div class="glass-card">
                    <div class="items-search">
                        <span class="material-icons items-search__icon">search</span>
                        <input type="text" id="items-search-input" class="items-search__input" placeholder="Search items..." autocomplete="off">
                        <button type="button" id="items-search-clear" class="items-search__clear" style="display:none;" title="Clear search">
                            <span class="material-icons">close</span>
                        </button>
                    </div>
                    <div class="items-grid-wrapper">
                        <div id="items-grid" class="items-grid">
                            <div class="items-loading">
                                <div class="spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Step 2: Delivery Method -->
            <section class="order-section">
                <div class="section-header">
                    <div class="section-step">2</div>
                    <div class="section-info">
                        <h3 class="section-title">Delivery Method</h3>
                        <p class="section-subtitle">How would you like to receive your order?</p>
                    </div>
                </div>
                <div class="glass-card">
                    <div class="delivery-options">
                        <label class="delivery-option active" data-type="delivery">
                            <input type="radio" name="delivery_type" value="delivery" checked>
                            <div class="option-card">
                                <span class="material-icons option-icon">local_shipping</span>
                                <div class="option-text">
                                    <span class="option-title">Delivery</span>
                                    <span class="option-desc">We'll bring it to your door</span>
                                </div>
                            </div>
                        </label>
                        <label class="delivery-option" data-type="pickup">
                            <input type="radio" name="delivery_type" value="pickup">
                            <div class="option-card">
                                <span class="material-icons option-icon">store</span>
                                <div class="option-text">
                                    <span class="option-title">Pickup</span>
                                    <span class="option-desc">Get it at our station</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Address (shown only for delivery) -->
                    <div id="address-section" class="address-block">
                        <div class="address-header">
                            <div class="address-label">
                                <span class="material-icons">location_on</span>
                                <span>Delivery Address</span>
                            </div>
                            <a href="addresses.php" class="btn btn-sm btn-outline">
                                <span class="material-icons">add</span>
                                Manage
                            </a>
                        </div>
                        <input type="hidden" id="address-select" value="">
                        <div class="custom-select-wrapper" id="address-select-wrapper">
                            <div class="custom-select-trigger" id="address-select-trigger">
                                <span class="material-icons" style="margin-right: 8px; font-size: 20px; color: var(--primary);">location_on</span>
                                <span class="selected-text placeholder">Select delivery address...</span>
                                <span class="material-icons arrow">expand_more</span>
                            </div>
                            <div class="custom-select-options" id="address-select-options">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Step 3: Payment Method -->
            <section class="order-section">
                <div class="section-header">
                    <div class="section-step">3</div>
                    <div class="section-info">
                        <h3 class="section-title">Payment Method</h3>
                        <p class="section-subtitle">Choose how you'd like to pay</p>
                    </div>
                </div>
                <div class="glass-card">
                    <div id="payment-options" class="payment-options">
                        <label class="payment-option active" data-for="delivery" id="payment-cod">
                            <input type="radio" name="payment_type" value="cod" checked>
                            <div class="option-card">
                                <span class="material-icons option-icon">payments</span>
                                <div class="option-text">
                                    <span class="option-title">Cash on Delivery</span>
                                    <span class="option-desc">Pay when you receive</span>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option" data-for="pickup" id="payment-pickup" style="display: none;">
                            <input type="radio" name="payment_type" value="pickup" disabled>
                            <div class="option-card">
                                <span class="material-icons option-icon">point_of_sale</span>
                                <div class="option-text">
                                    <span class="option-title">Pay at Pickup</span>
                                    <span class="option-desc">Pay when you collect</span>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option payment-option--disabled" id="payment-online">
                            <input type="radio" name="payment_type" value="online" disabled>
                            <div class="option-card">
                                <span class="material-icons option-icon">credit_card</span>
                                <div class="option-text">
                                    <span class="option-title">Online Payment</span>
                                    <span class="option-desc">Coming Soon</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </section>
            
            <!-- Order Notes -->
            <section class="order-section">
                <div class="section-header">
                    <span class="material-icons section-icon">edit_note</span>
                    <div class="section-info">
                        <h3 class="section-title">Order Notes</h3>
                        <p class="section-subtitle">Any special instructions? (Optional)</p>
                    </div>
                </div>
                <div class="glass-card">
                    <textarea id="order-notes" class="order-notes-input" rows="3" 
                        placeholder="E.g., Leave at the gate, call upon arrival..."></textarea>
                </div>
            </section>
        </div>
        
        <!-- Right Column: Order Summary -->
        <aside class="order-sidebar">
            <div class="glass-card summary-card">
                <div class="summary-header">
                    <h3 class="summary-title">Order Summary</h3>
                    <span class="cart-badge" id="cart-count">0 items</span>
                </div>
                
                <div id="cart-items" class="cart-items">
                    <div class="cart-empty">
                        <span class="material-icons cart-empty__icon">shopping_cart</span>
                        <p class="cart-empty__title">Your cart is empty</p>
                        <p class="cart-empty__text">Add items to get started</p>
                    </div>
                </div>
                
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal" class="summary-amount">₱0.00</span>
                    </div>
                    <div class="summary-row" id="delivery-fee-row">
                        <span>Delivery Fee</span>
                        <span id="delivery-fee" class="summary-amount">₱0.00</span>
                    </div>
                    <div class="summary-row summary-row--total">
                        <span>Total</span>
                        <span id="total" class="summary-total">₱0.00</span>
                    </div>
                </div>
                
                <button id="place-order-btn" class="place-order-btn" disabled>
                    <span class="material-icons">shopping_cart_checkout</span>
                    Place Order
                </button>
            </div>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
