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

            <!-- Station Logo -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon" style="background: rgba(38, 166, 154, 0.1); color: #26A69A;">
                        <span class="material-icons">image</span>
                    </div>
                    <div>
                        <h3 class="settings-panel-title">Station Logo</h3>
                        <p class="settings-panel-desc">Upload a logo displayed in the sidebar (max 1MB)</p>
                    </div>
                </div>
                <div class="settings-panel-body" style="padding: 20px 24px;">
                    <div class="logo-upload-section">
                        <div class="logo-preview-area">
                            <div class="logo-preview" id="logo-preview">
                                <img id="logo-preview-img" src="../<?php echo htmlspecialchars(get_setting('station_logo') ?? 'images/system/logo-1.png'); ?>" alt="Station Logo">
                            </div>
                            <div class="logo-preview-info">
                                <span class="logo-preview-name">Current Logo</span>
                                <span class="logo-preview-hint">
                                    <span class="material-icons">info</span>
                                    PNG, JPG, GIF, or WEBP — Max 1MB
                                </span>
                            </div>
                        </div>
                        <div class="logo-upload-controls">
                            <label class="logo-upload-btn" for="logo-file-input">
                                <span class="material-icons">upload</span>
                                Choose Logo
                            </label>
                            <input type="file" id="logo-file-input" accept="image/png,image/jpeg,image/gif,image/webp" style="display:none;">
                            <button type="button" class="btn btn-primary logo-save-btn" id="logo-upload-btn" style="display:none;">
                                <span class="material-icons">cloud_upload</span>
                                Upload Logo
                            </button>
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

            <!-- Time Region Settings -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon" style="background: rgba(171, 71, 188, 0.1); color: #AB47BC;">
                        <span class="material-icons">language</span>
                    </div>
                    <div>
                        <h3 class="settings-panel-title">Time Region</h3>
                        <p class="settings-panel-desc">Set the timezone displayed in the header clock</p>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="tz-region-layout">
                        <div class="tz-select-group">
                            <label>Timezone Region</label>
                            <input type="hidden" id="time_region" value="Asia/Manila">
                            <div class="custom-select-wrapper" id="tz-select-wrapper">
                                <div class="custom-select-trigger" id="tz-select-trigger">
                                    <img class="tz-option-flag" id="tz-trigger-flag" src="https://flagcdn.com/w40/ph.png" alt="Flag">
                                    <span class="selected-text">Philippines (Manila)</span>
                                    <span class="material-icons arrow">expand_more</span>
                                </div>
                                <div class="custom-select-options" id="tz-select-options">
                                    <div class="custom-select-option selected" data-value="Asia/Manila" data-cc="ph" data-label="Philippines"><img class="tz-option-flag" src="https://flagcdn.com/w40/ph.png" alt="PH"> Philippines (Manila)</div>
                                    <div class="custom-select-option" data-value="Asia/Tokyo" data-cc="jp" data-label="Japan"><img class="tz-option-flag" src="https://flagcdn.com/w40/jp.png" alt="JP"> Japan (Tokyo)</div>
                                    <div class="custom-select-option" data-value="Asia/Seoul" data-cc="kr" data-label="South Korea"><img class="tz-option-flag" src="https://flagcdn.com/w40/kr.png" alt="KR"> South Korea (Seoul)</div>
                                    <div class="custom-select-option" data-value="Asia/Shanghai" data-cc="cn" data-label="China"><img class="tz-option-flag" src="https://flagcdn.com/w40/cn.png" alt="CN"> China (Shanghai)</div>
                                    <div class="custom-select-option" data-value="Asia/Singapore" data-cc="sg" data-label="Singapore"><img class="tz-option-flag" src="https://flagcdn.com/w40/sg.png" alt="SG"> Singapore</div>
                                    <div class="custom-select-option" data-value="Asia/Kolkata" data-cc="in" data-label="India"><img class="tz-option-flag" src="https://flagcdn.com/w40/in.png" alt="IN"> India (Kolkata)</div>
                                    <div class="custom-select-option" data-value="Asia/Dubai" data-cc="ae" data-label="UAE"><img class="tz-option-flag" src="https://flagcdn.com/w40/ae.png" alt="AE"> UAE (Dubai)</div>
                                    <div class="custom-select-option" data-value="Europe/London" data-cc="gb" data-label="United Kingdom"><img class="tz-option-flag" src="https://flagcdn.com/w40/gb.png" alt="GB"> United Kingdom (London)</div>
                                    <div class="custom-select-option" data-value="Europe/Paris" data-cc="fr" data-label="France"><img class="tz-option-flag" src="https://flagcdn.com/w40/fr.png" alt="FR"> France (Paris)</div>
                                    <div class="custom-select-option" data-value="Europe/Berlin" data-cc="de" data-label="Germany"><img class="tz-option-flag" src="https://flagcdn.com/w40/de.png" alt="DE"> Germany (Berlin)</div>
                                    <div class="custom-select-option" data-value="America/New_York" data-cc="us" data-label="US Eastern"><img class="tz-option-flag" src="https://flagcdn.com/w40/us.png" alt="US"> US Eastern (New York)</div>
                                    <div class="custom-select-option" data-value="America/Chicago" data-cc="us" data-label="US Central"><img class="tz-option-flag" src="https://flagcdn.com/w40/us.png" alt="US"> US Central (Chicago)</div>
                                    <div class="custom-select-option" data-value="America/Denver" data-cc="us" data-label="US Mountain"><img class="tz-option-flag" src="https://flagcdn.com/w40/us.png" alt="US"> US Mountain (Denver)</div>
                                    <div class="custom-select-option" data-value="America/Los_Angeles" data-cc="us" data-label="US Pacific"><img class="tz-option-flag" src="https://flagcdn.com/w40/us.png" alt="US"> US Pacific (Los Angeles)</div>
                                    <div class="custom-select-option" data-value="Australia/Sydney" data-cc="au" data-label="Australia"><img class="tz-option-flag" src="https://flagcdn.com/w40/au.png" alt="AU"> Australia (Sydney)</div>
                                    <div class="custom-select-option" data-value="Pacific/Auckland" data-cc="nz" data-label="New Zealand"><img class="tz-option-flag" src="https://flagcdn.com/w40/nz.png" alt="NZ"> New Zealand (Auckland)</div>
                                </div>
                            </div>
                        </div>
                        <div class="tz-preview-card" id="tz-preview-card">
                            <img class="tz-preview-flag" id="tz-preview-flag" src="https://flagcdn.com/w80/ph.png" alt="Flag">
                            <div class="tz-preview-details">
                                <span class="tz-preview-label" id="tz-preview-label">Philippines</span>
                                <span class="tz-preview-clock" id="tz-preview-clock">--:--:-- --</span>
                            </div>
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
        overflow: visible;
        transition: box-shadow 0.3s ease;
    }

    .settings-panel-header {
        border-radius: 16px 16px 0 0;
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

    /* Time Region */
    .tz-region-layout {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .tz-select-group {
        width: 100%;
    }

    .tz-select-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-primary);
        font-size: 0.9rem;
    }

    /* Custom select overrides for timezone */
    #tz-select-wrapper .custom-select-trigger {
        gap: 10px;
    }

    #tz-select-wrapper .custom-select-trigger .selected-text {
        flex: 1;
    }

    #tz-select-wrapper .custom-select-options {
        max-height: 260px;
    }

    .tz-option-flag {
        width: 24px;
        height: 16px;
        object-fit: cover;
        border-radius: 3px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
        flex-shrink: 0;
        vertical-align: middle;
    }

    #tz-select-wrapper .custom-select-option {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tz-preview-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        background: linear-gradient(135deg, var(--surface) 0%, var(--surface-card) 100%);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.3s ease;
    }

    .tz-preview-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .tz-preview-flag {
        width: 48px;
        height: 33px;
        object-fit: cover;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }

    .tz-preview-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .tz-preview-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tz-preview-clock {
        font-family: 'Inter', monospace;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: 0.5px;
    }

    @media (max-width: 480px) {
        .tz-preview-card {
            padding: 14px 18px;
            gap: 14px;
        }

        .tz-preview-flag {
            width: 40px;
            height: 28px;
        }

        .tz-preview-clock {
            font-size: 1.1rem;
        }
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

    /* Logo Upload Section */
    .logo-upload-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .logo-preview-area {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .logo-preview {
        width: 88px;
        height: 88px;
        border-radius: 18px;
        overflow: hidden;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--surface);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .logo-preview:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 16px rgba(21, 101, 192, 0.15);
    }

    .logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .logo-preview-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .logo-preview-name {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .logo-preview-hint {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .logo-preview-hint .material-icons {
        font-size: 13px;
        opacity: 0.5;
    }

    .logo-upload-controls {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }

    .logo-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .logo-upload-btn:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 3px 10px rgba(21, 101, 192, 0.25);
    }

    .logo-upload-btn .material-icons {
        font-size: 17px;
    }

    .logo-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 10px;
        white-space: nowrap;
    }

    .logo-save-btn .material-icons {
        font-size: 17px;
    }

    @media (max-width: 600px) {
        .logo-upload-section {
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .logo-preview-area {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .logo-upload-controls {
            align-items: center;
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

    // Timezone custom select + preview logic
    const tzTimezoneData = {};
    document.querySelectorAll('#tz-select-options .custom-select-option').forEach(opt => {
        tzTimezoneData[opt.dataset.value] = { cc: opt.dataset.cc, label: opt.dataset.label };
    });

    // Custom select toggle
    const tzTrigger = document.getElementById('tz-select-trigger');
    const tzOptions = document.getElementById('tz-select-options');

    if (tzTrigger && tzOptions) {
        tzTrigger.addEventListener('click', () => {
            tzTrigger.classList.toggle('active');
            tzOptions.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#tz-select-wrapper')) {
                tzTrigger.classList.remove('active');
                tzOptions.classList.remove('active');
            }
        });

        tzOptions.addEventListener('click', (e) => {
            const opt = e.target.closest('.custom-select-option');
            if (!opt) return;

            const value = opt.dataset.value;
            const cc = opt.dataset.cc;
            const label = opt.dataset.label;

            // Update hidden input
            document.getElementById('time_region').value = value;

            // Update trigger display
            const triggerFlag = document.getElementById('tz-trigger-flag');
            triggerFlag.src = `https://flagcdn.com/w40/${cc}.png`;
            tzTrigger.querySelector('.selected-text').textContent = opt.textContent.trim();

            // Mark selected
            tzOptions.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');

            // Close dropdown
            tzTrigger.classList.remove('active');
            tzOptions.classList.remove('active');

            // Trigger preview update immediately
            updateTimezonePreview();
        });
    }

    function updateTimezonePreview() {
        const hiddenInput = document.getElementById('time_region');
        if (!hiddenInput) return;

        const tz = hiddenInput.value;
        const data = tzTimezoneData[tz] || { cc: 'ph', label: 'Philippines' };

        // Update preview card
        const previewFlag = document.getElementById('tz-preview-flag');
        const previewLabel = document.getElementById('tz-preview-label');
        const previewClock = document.getElementById('tz-preview-clock');

        if (previewFlag) previewFlag.src = `https://flagcdn.com/w80/${data.cc}.png`;
        if (previewLabel) previewLabel.textContent = data.label;

        if (previewClock) {
            const now = new Date();
            previewClock.textContent = now.toLocaleString('en-US', {
                timeZone: tz,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        }

        // Live-update header clock timezone and flag
        const headerClock = document.getElementById('manila-time');
        if (headerClock) headerClock.setAttribute('data-timezone', tz);

        const headerFlag = document.getElementById('header-time-flag');
        if (headerFlag) {
            headerFlag.src = `https://flagcdn.com/w40/${data.cc}.png`;
            headerFlag.alt = data.cc.toUpperCase();
        }
    }

    // Programmatic select (called from system_settings.js on load)
    window.setTimezoneValue = function(value) {
        const hiddenInput = document.getElementById('time_region');
        if (!hiddenInput) return;
        hiddenInput.value = value;

        const opt = document.querySelector(`#tz-select-options .custom-select-option[data-value="${value}"]`);
        if (opt) {
            const cc = opt.dataset.cc;
            document.getElementById('tz-trigger-flag').src = `https://flagcdn.com/w40/${cc}.png`;
            document.querySelector('#tz-select-trigger .selected-text').textContent = opt.textContent.trim();
            document.querySelectorAll('#tz-select-options .custom-select-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
        }
        updateTimezonePreview();
    };

    document.addEventListener('DOMContentLoaded', () => {
        updateTimezonePreview();
        setInterval(updateTimezonePreview, 1000);
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>