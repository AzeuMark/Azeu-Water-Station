<?php
/**
 * ============================================================================
 * AZEU WATER STATION - SYSTEM SETTINGS (MODERNIZED)
 * ============================================================================
 * 
 * Purpose: Configure system-wide settings
 * Role: ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "System Settings";
$page_css = "main.css";
$page_js = "system_settings.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">System Settings</h1>
    </div>
    
    <form id="settings-form">
        <div style="display: grid; gap: 24px;">
            <!-- General Settings -->
            <div class="glass-card">
                <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--primary);">store</span>
                    General Settings
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Station Name</label>
                        <input type="text" id="station_name" class="form-select">
                    </div>
                    <div class="form-group">
                        <label>Station Address</label>
                        <input type="text" id="station_address" class="form-select">
                    </div>
                    <div class="form-group">
                        <label>Delivery Fee (₱)</label>
                        <input type="number" id="delivery_fee" class="form-select" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Low Stock Threshold</label>
                        <input type="number" id="low_stock_threshold" class="form-select">
                    </div>
                </div>
            </div>
            
            <!-- Order Settings -->
            <div class="glass-card">
                <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--primary);">receipt_long</span>
                    Order Settings
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Max Cancellation per Month</label>
                        <input type="number" id="max_cancellation" class="form-select">
                    </div>
                    <div class="form-group">
                        <label>Pending Account Expiry (days)</label>
                        <input type="number" id="pending_expiry_days" class="form-select">
                    </div>
                </div>
                
                <!-- Order Toggles -->
                <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                    <div class="toggle-setting">
                        <div class="toggle-setting-info">
                            <label for="auto_confirm_orders">Auto Confirm Orders</label>
                            <small>Automatically confirm incoming orders without staff review</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="auto_confirm_orders">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-setting">
                        <div class="toggle-setting-info">
                            <label for="auto_assign_rider">Auto Assign Rider</label>
                            <small>Automatically assign the least-busy available rider to delivery orders</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="auto_assign_rider">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-setting">
                        <div class="toggle-setting-info">
                            <label for="auto_reassign_rider">Auto Reassign Rider</label>
                            <small>Automatically reassign orders when a rider becomes unavailable</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="auto_reassign_rider">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="glass-card">
                <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--primary);">security</span>
                    Security Settings
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Max Login Attempts</label>
                        <input type="number" id="max_login_attempts" class="form-select">
                    </div>
                    <div class="form-group">
                        <label>Login Lockout Minutes</label>
                        <input type="number" id="login_lockout_minutes" class="form-select">
                    </div>
                </div>
                
                <!-- Security Toggles -->
                <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                    <div class="toggle-setting">
                        <div class="toggle-setting-info">
                            <label for="encrypt_passwords">Encrypt Passwords</label>
                            <small>Hash passwords using bcrypt for enhanced security</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="encrypt_passwords">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-setting">
                        <div class="toggle-setting-info">
                            <label for="maintenance_mode">Maintenance Mode</label>
                            <small>Restrict access to admin users only during maintenance</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="maintenance_mode">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-setting">
                        <div class="toggle-setting-info">
                            <label for="force_dark_mode">Force Dark Mode</label>
                            <small>Override user preferences and force dark mode for all users</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="force_dark_mode">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Inventory Settings -->
            <div class="glass-card">
                <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--primary);">inventory_2</span>
                    Inventory Settings
                </h3>
                <div class="form-group">
                    <label style="margin-bottom: 8px; display: block; font-weight: 600;">
                        Default Item Names
                        <small style="display: block; font-weight: normal; color: var(--text-secondary); margin-top: 4px;">
                            Enter each item name on a new line. These will appear in the dropdown when adding inventory items.
                        </small>
                    </label>
                    <textarea 
                        id="default_item_names" 
                        class="form-select" 
                        rows="10" 
                        placeholder="30L Water Refill&#10;20L Water Refill&#10;10L Water Refill&#10;5L Water Refill&#10;1L Bottled Water&#10;..."
                        style="font-family: monospace; resize: vertical;"
                    ></textarea>
                    <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 8px;">
                        💡 Tip: One item per line. Staff can still enter custom item names using the "Custom/Other" option.
                    </small>
                </div>
            </div>
            
            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">save</span>
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
