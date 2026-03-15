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
    initLogoUpload();
    
    document.getElementById('settings-form').addEventListener('submit', saveSettings);
});

async function loadSettings() {
    try {
        const response = await fetch('../api/settings/get.php');
        const data = await response.json();
        
        if (data.success) {
            const settings = data.settings;
            
            // General Settings
            document.getElementById('station_name').value = settings.station_name || '';
            document.getElementById('station_address').value = settings.station_address || '';
            document.getElementById('delivery_fee').value = settings.delivery_fee || '';
            document.getElementById('low_stock_threshold').value = settings.low_stock_threshold || '';
            
            // Order Settings
            document.getElementById('max_cancellation').value = settings.max_cancellation || '';
            document.getElementById('pending_expiry_days').value = settings.pending_expiry_days || '';
            
            // Order Toggles
            document.getElementById('auto_confirm_orders').checked = settings.auto_confirm_orders == '1';
            document.getElementById('auto_assign_rider').checked = settings.auto_assign_rider == '1';
            document.getElementById('auto_reassign_rider').checked = settings.auto_reassign_rider == '1';
            
            // Security Settings
            document.getElementById('max_login_attempts').value = settings.max_login_attempts || '';
            document.getElementById('login_lockout_minutes').value = settings.login_lockout_minutes || '';
            document.getElementById('encrypt_passwords').checked = settings.encrypt_passwords == '1';
            document.getElementById('maintenance_mode').checked = settings.maintenance_mode == '1';
            document.getElementById('force_dark_mode').checked = settings.force_dark_mode == '1';
            
            // Inventory Settings
            document.getElementById('default_item_names').value = settings.default_item_names || '';
            
            // Time Region
            if (settings.time_region && typeof setTimezoneValue === 'function') {
                setTimezoneValue(settings.time_region);
            }
            
            // Sync range sliders with loaded values
            if (typeof updateRangesFromInputs === 'function') updateRangesFromInputs();
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showToast('Failed to load settings', 'error');
    }
}

async function saveSettings(e) {
    e.preventDefault();
    
    const settings = {
        // General
        station_name: document.getElementById('station_name').value,
        station_address: document.getElementById('station_address').value,
        delivery_fee: document.getElementById('delivery_fee').value,
        low_stock_threshold: document.getElementById('low_stock_threshold').value,
        
        // Order
        max_cancellation: document.getElementById('max_cancellation').value,
        pending_expiry_days: document.getElementById('pending_expiry_days').value,
        auto_confirm_orders: document.getElementById('auto_confirm_orders').checked ? '1' : '0',
        auto_assign_rider: document.getElementById('auto_assign_rider').checked ? '1' : '0',
        auto_reassign_rider: document.getElementById('auto_reassign_rider').checked ? '1' : '0',
        
        // Security
        max_login_attempts: document.getElementById('max_login_attempts').value,
        login_lockout_minutes: document.getElementById('login_lockout_minutes').value,
        encrypt_passwords: document.getElementById('encrypt_passwords').checked ? '1' : '0',
        maintenance_mode: document.getElementById('maintenance_mode').checked ? '1' : '0',
        force_dark_mode: document.getElementById('force_dark_mode').checked ? '1' : '0',
        
        // Inventory
        default_item_names: document.getElementById('default_item_names').value,
        
        // Time Region
        time_region: document.getElementById('time_region').value
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

// Logo Upload
function initLogoUpload() {
    const fileInput = document.getElementById('logo-file-input');
    const uploadBtn = document.getElementById('logo-upload-btn');
    const previewImg = document.getElementById('logo-preview-img');
    
    if (!fileInput || !uploadBtn) return;
    
    let selectedFile = null;

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Validate size (1MB)
        if (file.size > 1 * 1024 * 1024) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            showToast(`Logo is too large (${sizeMB}MB). Maximum size is 1MB.`, 'error');
            this.value = '';
            return;
        }

        // Validate type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Invalid file type. Only PNG, JPG, GIF, and WEBP are allowed.', 'error');
            this.value = '';
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);

        selectedFile = file;
        uploadBtn.style.display = 'inline-flex';
    });

    uploadBtn.addEventListener('click', async function() {
        if (!selectedFile) return;

        const formData = new FormData();
        formData.append('logo', selectedFile);
        formData.append('csrf_token', getCSRFToken());

        showLoading();

        try {
            const response = await fetch('../api/settings/upload_logo.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showToast('Logo updated successfully', 'success');
                uploadBtn.style.display = 'none';
                fileInput.value = '';
                selectedFile = null;

                // Update sidebar logo
                const sidebarLogo = document.querySelector('.sidebar-logo img');
                if (sidebarLogo && data.logo_path) {
                    sidebarLogo.src = '../' + data.logo_path + '?v=' + Date.now();
                }
                // Update preview
                if (data.logo_path) {
                    previewImg.src = '../' + data.logo_path + '?v=' + Date.now();
                }
            } else {
                showToast(data.message || 'Failed to upload logo', 'error');
            }
        } catch (error) {
            hideLoading();
            console.error('Error uploading logo:', error);
            showToast('An error occurred while uploading', 'error');
        }
    });
}
