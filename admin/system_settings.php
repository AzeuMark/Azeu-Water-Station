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
        <div class="settings-layout">
            <!-- General Settings -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon" style="background: rgba(21, 101, 192, 0.1); color: var(--primary);">
                        <span class="material-icons">store</span>
                    </div>
                    <div>
                        <h3 class="settings-panel-title">General Settings</h3>
                        <p class="settings-panel-desc">Basic station information and configuration</p>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-fields-grid">
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
                            <div class="range-input-group">
                                <input type="range" class="settings-range" id="delivery_fee_range" min="0" max="200" step="0.5" value="0">
                                <input type="number" id="delivery_fee" class="form-select range-number" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Low Stock Threshold</label>
                            <div class="range-input-group">
                                <input type="range" class="settings-range" id="low_stock_threshold_range" min="0" max="100" step="1" value="0">
                                <input type="number" id="low_stock_threshold" class="form-select range-number" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Settings -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon" style="background: rgba(255, 152, 0, 0.1); color: #FF9800;">
                        <span class="material-icons">receipt_long</span>
                    </div>
                    <div>
                        <h3 class="settings-panel-title">Order Settings</h3>
                        <p class="settings-panel-desc">Cancellation limits and order automation</p>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-fields-grid">
                        <div class="form-group">
                            <label>Max Cancellation per Month</label>
                            <div class="range-input-group">
                                <input type="range" class="settings-range" id="max_cancellation_range" min="0" max="20" step="1" value="0">
                                <input type="number" id="max_cancellation" class="form-select range-number" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Pending Account Expiry (days)</label>
                            <div class="range-input-group">
                                <input type="range" class="settings-range" id="pending_expiry_days_range" min="1" max="90" step="1" value="7">
                                <input type="number" id="pending_expiry_days" class="form-select range-number" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="settings-toggles">
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
            </div>

            <!-- Security Settings -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon" style="background: rgba(239, 83, 80, 0.1); color: #EF5350;">
                        <span class="material-icons">security</span>
                    </div>
                    <div>
                        <h3 class="settings-panel-title">Security Settings</h3>
                        <p class="settings-panel-desc">Login protection and system access controls</p>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-fields-grid">
                        <div class="form-group">
                            <label>Max Login Attempts</label>
                            <div class="range-input-group">
                                <input type="range" class="settings-range" id="max_login_attempts_range" min="1" max="20" step="1" value="5">
                                <input type="number" id="max_login_attempts" class="form-select range-number" min="1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Login Lockout Minutes</label>
                            <div class="range-input-group">
                                <input type="range" class="settings-range" id="login_lockout_minutes_range" min="1" max="120" step="1" value="15">
                                <input type="number" id="login_lockout_minutes" class="form-select range-number" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="settings-toggles">
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
            </div>

            <!-- Inventory Settings -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon" style="background: rgba(102, 187, 106, 0.1); color: #66BB6A;">
                        <span class="material-icons">inventory_2</span>
                    </div>
                    <div>
                        <h3 class="settings-panel-title">Inventory Settings</h3>
                        <p class="settings-panel-desc">Default product names for quick inventory management</p>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="form-group">
                        <label class="settings-textarea-label">
                            Default Item Names
                        </label>
                        <small class="settings-textarea-hint">
                            Enter each item name on a new line. These will appear in the dropdown when adding inventory items.
                        </small>
                        <textarea id="default_item_names" class="form-select settings-textarea" rows="10" placeholder="10L Nature Spring Water&#10;5L Absolute Water&#10;1L Coca Cola Soda&#10;1L Sprite Soda&#10;...."></textarea>
                        <small class="settings-textarea-tip">
                            <span class="material-icons">lightbulb</span>
                            One item per line. Staff can still enter custom item names using the "Custom/Other" option.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="settings-save-bar">
                <button type="submit" class="btn btn-primary settings-save-btn">
                    <span class="material-icons">save</span>
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</main>

<style>
    /* Range + Number Input Group */
    .range-input-group {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .settings-range {
        flex: 1;
        -webkit-appearance: none;
        appearance: none;
        height: 6px;
        border-radius: 3px;
        background: var(--border);
        outline: none;
        transition: background 0.2s;
    }

    .settings-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(21, 101, 192, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .settings-range::-webkit-slider-thumb:hover {
        transform: scale(1.15);
        box-shadow: 0 3px 10px rgba(21, 101, 192, 0.45);
    }

    .settings-range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(21, 101, 192, 0.35);
    }

    .settings-range::-moz-range-track {
        height: 6px;
        border-radius: 3px;
        background: var(--border);
    }

    .range-number {
        width: 90px !important;
        min-width: 90px;
        flex-shrink: 0;
        text-align: center;
        font-weight: 600;
    }

    /* Responsive: stack range on very small screens */
    @media (max-width: 480px) {
        .range-input-group {
            flex-direction: column;
            gap: 8px;
        }

        .settings-range {
            width: 100%;
        }

        .range-number {
            width: 100% !important;
        }
    }

    /* ============================================================================
   SYSTEM SETTINGS — Revamped Layout
   ============================================================================ */

    .settings-layout {
        display: grid;
        gap: 24px;
    }

    /* Panel Card */
    .settings-panel {
        background: var(--surface-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .settings-panel:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }

    /* Panel Header */
    .settings-panel-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }

    .settings-panel-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .settings-panel-icon .material-icons {
        font-size: 22px;
    }

    .settings-panel-title {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .settings-panel-desc {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 2px 0 0 0;
    }

    /* Panel Body */
    .settings-panel-body {
        padding: 24px;
    }

    /* Fields Grid */
    .settings-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Toggles Section */
    .settings-toggles {
        border-top: 1px solid var(--border);
        margin-top: 20px;
        padding-top: 16px;
    }

    /* Textarea area */
    .settings-textarea-label {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .settings-textarea-hint {
        display: block;
        font-size: 0.82rem;
        color: var(--text-secondary);
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .settings-textarea {
        font-family: 'Courier New', monospace;
        resize: vertical;
        min-height: 120px;
    }

    .settings-textarea-tip {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 10px;
    }

    .settings-textarea-tip .material-icons {
        font-size: 16px;
        color: #FFA726;
    }

    /* Save Bar */
    .settings-save-bar {
        display: flex;
        justify-content: flex-end;
        padding: 4px 0;
    }

    .settings-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .settings-save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(21, 101, 192, 0.3);
    }

    .settings-save-btn .material-icons {
        font-size: 20px;
    }

    /* ============================================================================
   RESPONSIVE
   ============================================================================ */

    @media (max-width: 768px) {
        .settings-panel-header {
            padding: 16px 18px;
        }

        .settings-panel-body {
            padding: 18px;
        }

        .settings-fields-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .settings-panel-icon {
            width: 40px;
            height: 40px;
        }

        .settings-panel-icon .material-icons {
            font-size: 20px;
        }

        .settings-panel-title {
            font-size: 0.95rem;
        }

        .settings-panel-desc {
            font-size: 0.78rem;
        }

        .settings-save-bar {
            justify-content: stretch;
        }

        .settings-save-btn {
            width: 100%;
            justify-content: center;
            padding: 14px;
        }
    }

    @media (max-width: 480px) {
        .settings-panel-header {
            padding: 14px 16px;
            gap: 12px;
        }

        .settings-panel-body {
            padding: 16px;
        }

        .settings-panel-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }

        .settings-panel-icon .material-icons {
            font-size: 18px;
        }
    }
</style>

<script>
    // Bidirectional sync: range slider ↔ number input
    const rangePairs = [{
            range: 'delivery_fee_range',
            input: 'delivery_fee'
        },
        {
            range: 'low_stock_threshold_range',
            input: 'low_stock_threshold'
        },
        {
            range: 'max_cancellation_range',
            input: 'max_cancellation'
        },
        {
            range: 'pending_expiry_days_range',
            input: 'pending_expiry_days'
        },
        {
            range: 'max_login_attempts_range',
            input: 'max_login_attempts'
        },
        {
            range: 'login_lockout_minutes_range',
            input: 'login_lockout_minutes'
        }
    ];

    function syncRangeSliders() {
        rangePairs.forEach(pair => {
            const range = document.getElementById(pair.range);
            const input = document.getElementById(pair.input);
            if (!range || !input) return;

            // Sync range → input
            range.addEventListener('input', () => {
                input.value = range.value;
            });
            // Sync input → range (clamp to range min/max)
            input.addEventListener('input', () => {
                let val = parseFloat(input.value) || 0;
                val = Math.min(Math.max(val, parseFloat(range.min)), parseFloat(range.max));
                range.value = val;
            });
        });
    }

    // Update ranges when settings load (called after system_settings.js populates inputs)
    function updateRangesFromInputs() {
        rangePairs.forEach(pair => {
            const range = document.getElementById(pair.range);
            const input = document.getElementById(pair.input);
            if (!range || !input) return;
            range.value = parseFloat(input.value) || range.min;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncRangeSliders();
        // Wait for settings to load, then sync ranges
        const origLoad = window.loadSettings || null;
        if (origLoad) return; // system_settings.js handles it
        setTimeout(updateRangesFromInputs, 500);
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>