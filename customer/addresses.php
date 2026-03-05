<?php
/**
 * ============================================================================
 * AZEU WATER STATION - MANAGE ADDRESSES PAGE
 * ============================================================================
 * 
 * Purpose: Manage delivery addresses for customer
 * Role: CUSTOMER
 * 
 * Features:
 * - List all saved addresses
 * - Add new address
 * - Edit existing address
 * - Delete address
 * - Set default address
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "My Addresses";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 class="content-title">My Addresses</h1>
                <p class="content-breadcrumb">
                    <span>Home</span>
                    <span class="breadcrumb-separator">/</span>
                    <span>Addresses</span>
                </p>
            </div>
            <button class="btn btn-primary" onclick="showAddAddressModal()">
                <span class="material-icons">add</span>
                Add Address
            </button>
        </div>
    </div>
    
    <div id="addresses-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <div class="glass-card" style="text-align: center; padding: 40px;">
            <div class="spinner"></div>
        </div>
    </div>
</main>

<!-- Add/Edit Address Modal -->
<div class="modal-overlay" id="address-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-title">Add Address</h3>
            <button class="modal-close" onclick="closeModal('address-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="address-form">
            <div class="modal-body">
                <input type="hidden" id="address-id" value="">
                
                <div class="form-group">
                    <label for="address-label">Label</label>
                    <input type="text" id="address-label" class="form-select" placeholder="e.g., Home, Office" required>
                </div>
                
                <div class="form-group">
                    <label for="address-full">Full Address</label>
                    <textarea id="address-full" class="form-select" rows="3" placeholder="Enter complete delivery address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="address-default" class="custom-checkbox">
                        <span>Set as default address</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('address-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">save</span>
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let addresses = [];

document.addEventListener('DOMContentLoaded', function() {
    loadAddresses();
    
    document.getElementById('address-form').addEventListener('submit', saveAddress);
});

async function loadAddresses() {
    try {
        const response = await fetch('../api/addresses/list.php');
        const data = await response.json();
        
        if (data.success) {
            addresses = data.addresses;
            renderAddresses(data.addresses);
        }
    } catch (error) {
        console.error('Failed to load addresses:', error);
    }
}

function renderAddresses(addresses) {
    const container = document.getElementById('addresses-container');
    
    if (addresses.length === 0) {
        container.innerHTML = `
            <div class="glass-card" style="grid-column: 1/-1;">
                <div class="empty-state">
                    <span class="material-icons empty-icon">location_off</span>
                    <p class="empty-title">No addresses saved</p>
                    <p class="empty-message">Add your first delivery address</p>
                    <button class="btn btn-primary" onclick="showAddAddressModal()" style="margin-top: 16px;">
                        <span class="material-icons">add</span>
                        Add Address
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    addresses.forEach(addr => {
        html += `
            <div class="glass-card" style="position: relative;">
                ${addr.is_default ? '<div class="badge badge-success" style="position: absolute; top: 16px; right: 16px;">Default</div>' : ''}
                <h4 style="margin-bottom: 8px;">${addr.label}</h4>
                <p style="color: var(--text-secondary); margin-bottom: 16px;">${addr.full_address}</p>
                <div style="display: flex; gap: 8px;">
                    <button class="btn btn-sm btn-outline" onclick="editAddress(${addr.id})">
                        <span class="material-icons">edit</span>
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteAddress(${addr.id})">
                        <span class="material-icons">delete</span>
                        Delete
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function showAddAddressModal() {
    document.getElementById('modal-title').textContent = 'Add Address';
    document.getElementById('address-form').reset();
    document.getElementById('address-id').value = '';
    openModal('address-modal');
}

function editAddress(id) {
    const addr = addresses.find(a => a.id === id);
    if (!addr) return;
    
    document.getElementById('modal-title').textContent = 'Edit Address';
    document.getElementById('address-id').value = addr.id;
    document.getElementById('address-label').value = addr.label;
    document.getElementById('address-full').value = addr.full_address;
    document.getElementById('address-default').checked = addr.is_default == 1;
    
    openModal('address-modal');
}

async function saveAddress(e) {
    e.preventDefault();
    
    const addressId = document.getElementById('address-id').value;
    const label = document.getElementById('address-label').value;
    const fullAddress = document.getElementById('address-full').value;
    const isDefault = document.getElementById('address-default').checked ? 1 : 0;
    
    showLoading();
    
    try {
        const url = addressId ? '../api/addresses/update.php' : '../api/addresses/create.php';
        const payload = addressId ? 
            { address_id: parseInt(addressId), label, full_address: fullAddress, is_default: isDefault, csrf_token: getCSRFToken() } :
            { label, full_address: fullAddress, is_default: isDefault, csrf_token: getCSRFToken() };
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast(addressId ? 'Address updated' : 'Address added', 'success');
            closeModal('address-modal');
            loadAddresses();
        } else {
            showToast(data.message || 'Failed to save address', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Save address error:', error);
        showToast('An error occurred', 'error');
    }
}

async function deleteAddress(id) {
    const confirm = await Swal.fire({
        title: 'Delete Address',
        text: 'Are you sure you want to delete this address?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        confirmButtonColor: '#EF5350'
    });
    
    if (!confirm.isConfirmed) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/addresses/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ address_id: id, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Address deleted', 'success');
            loadAddresses();
        } else {
            showToast(data.message || 'Failed to delete address', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Delete address error:', error);
        showToast('An error occurred', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
