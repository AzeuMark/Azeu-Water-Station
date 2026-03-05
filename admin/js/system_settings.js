/**
 * ============================================================================
 * AZEU WATER STATION - SYSTEM SETTINGS JAVASCRIPT
 * ============================================================================
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    
    document.getElementById('settings-form').addEventListener('submit', saveSettings);
});

async function loadSettings() {
    try {
        const response = await fetch('../api/settings/get.php');
        const data = await response.json();
        
        if (data.success) {
            const settings = data.settings;
            
            // Populate form fields
            document.getElementById('station_name').value = settings.station_name || '';
            document.getElementById('station_address').value = settings.station_address || '';
            document.getElementById('delivery_fee').value = settings.delivery_fee || '';
            document.getElementById('low_stock_threshold').value = settings.low_stock_threshold || '';
            document.getElementById('max_cancellation').value = settings.max_cancellation || '';
            document.getElementById('pending_expiry_days').value = settings.pending_expiry_days || '';
            document.getElementById('max_login_attempts').value = settings.max_login_attempts || '';
            document.getElementById('login_lockout_minutes').value = settings.login_lockout_minutes || '';
            document.getElementById('encrypt_passwords').checked = settings.encrypt_passwords == '1';
            document.getElementById('maintenance_mode').checked = settings.maintenance_mode == '1';
            document.getElementById('default_item_names').value = settings.default_item_names || '';
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showToast('Failed to load settings', 'error');
    }
}

async function saveSettings(e) {
    e.preventDefault();
    
    const settings = {
        station_name: document.getElementById('station_name').value,
        station_address: document.getElementById('station_address').value,
        delivery_fee: document.getElementById('delivery_fee').value,
        low_stock_threshold: document.getElementById('low_stock_threshold').value,
        max_cancellation: document.getElementById('max_cancellation').value,
        pending_expiry_days: document.getElementById('pending_expiry_days').value,
        max_login_attempts: document.getElementById('max_login_attempts').value,
        login_lockout_minutes: document.getElementById('login_lockout_minutes').value,
        encrypt_passwords: document.getElementById('encrypt_passwords').checked ? '1' : '0',
        maintenance_mode: document.getElementById('maintenance_mode').checked ? '1' : '0',
        default_item_names: document.getElementById('default_item_names').value
    };
    
    showLoading();
    
    try {
        const response = await fetch('../api/settings/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                settings: settings,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Settings saved successfully', 'success');
        } else {
            showToast(data.message || 'Failed to save settings', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Error saving settings:', error);
        showToast('An error occurred', 'error');
    }
}
