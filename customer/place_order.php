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
    
    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 24px; align-items: start;">
        <!-- Left Column: Items Selection -->
        <div>
            <!-- Delivery Type Selection -->
            <div class="glass-card" style="margin-bottom: 24px;">
                <h3 style="margin-bottom: 16px;">Delivery Method</h3>
                <div style="display: flex; gap: 12px;">
                    <label class="delivery-type-option active" data-type="delivery">
                        <input type="radio" name="delivery_type" value="delivery" checked>
                        <div class="option-content">
                            <span class="material-icons">local_shipping</span>
                            <span>Delivery</span>
                        </div>
                    </label>
                    <label class="delivery-type-option" data-type="pickup">
                        <input type="radio" name="delivery_type" value="pickup">
                        <div class="option-content">
                            <span class="material-icons">store</span>
                            <span>Pickup</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Delivery Address (shown only for delivery) -->
            <div class="glass-card" id="address-section" style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0;">Delivery Address</h3>
                    <a href="addresses.php" class="btn btn-sm btn-outline">
                        <span class="material-icons">add</span>
                        Add New
                    </a>
                </div>
                <select id="address-select" class="form-select">
                    <option value="">Select delivery address...</option>
                </select>
            </div>
            
            <!-- Payment Method -->
            <div class="glass-card" style="margin-bottom: 24px;">
                <h3 style="margin-bottom: 16px;">Payment Method</h3>
                <div id="payment-options" style="display: flex; gap: 12px;">
                    <label class="payment-option active">
                        <input type="radio" name="payment_type" value="cod" checked>
                        <div class="option-content">
                            <span class="material-icons">payments</span>
                            <span>Cash on Delivery</span>
                        </div>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_type" value="pickup">
                        <div class="option-content">
                            <span class="material-icons">point_of_sale</span>
                            <span>Pay at Pickup</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Available Items -->
            <div class="glass-card">
                <h3 style="margin-bottom: 20px;">Select Items</h3>
                <div id="items-grid" class="items-grid">
                    <div style="text-align: center; padding: 40px;">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Order Summary -->
        <div class="glass-card" style="position: sticky; top: 85px;">
            <h3 style="margin-bottom: 20px;">Order Summary</h3>
            
            <div id="cart-items" style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
                <div class="empty-state" style="padding: 40px 20px;">
                    <span class="material-icons empty-icon" style="font-size: 48px;">shopping_cart</span>
                    <p class="empty-message">No items added</p>
                </div>
            </div>
            
            <div class="order-notes" style="margin-bottom: 20px;">
                <label for="order-notes" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                    Order Notes (Optional)
                </label>
                <textarea id="order-notes" rows="3" placeholder="Special instructions..." 
                    style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: var(--radius-sm); 
                    resize: vertical; font-family: var(--font); background: var(--surface); color: var(--text-primary);"></textarea>
            </div>
            
            <div class="order-totals" style="border-top: 2px solid var(--border); padding-top: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Subtotal:</span>
                    <strong id="subtotal">₱0.00</strong>
                </div>
                <div id="delivery-fee-row" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Delivery Fee:</span>
                    <strong id="delivery-fee">₱0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.25rem; color: var(--primary); padding-top: 12px; border-top: 1px solid var(--border);">
                    <strong>Total:</strong>
                    <strong id="total">₱0.00</strong>
                </div>
            </div>
            
            <button id="place-order-btn" class="btn btn-primary w-full" disabled>
                <span class="material-icons">shopping_cart</span>
                Place Order
            </button>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
