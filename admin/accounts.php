<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF/ADMIN ACCOUNTS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Manage user accounts
 * Role: STAFF, ADMIN
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Manage Accounts";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Additional CSS for float-input-group -->
<link rel="stylesheet" href="../assets/css/auth.css">

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Manage Accounts</h1>
    </div>
    
    <div class="glass-card" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <button class="filter-btn active" data-role="">All Roles</button>
            <button class="filter-btn" data-role="customer">Customers</button>
            <button class="filter-btn" data-role="rider">Riders</button>
            <button class="filter-btn" data-role="staff">Staff</button>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="accounts-tbody">
                    <tr><td colspan="6" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Edit Account Modal -->
<div class="modal-overlay" id="edit-account-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Account</h3>
            <button class="modal-close" onclick="closeModal('edit-account-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="editAccountForm" onsubmit="submitEditAccount(event)">
            <div class="modal-body">
                <input type="hidden" id="edit_user_id">
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="edit_full_name" class="float-input" placeholder="Full Name" required>
                        <label for="edit_full_name" class="float-label">Full Name</label>
                        <span class="material-icons input-icon">person</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="edit_username" class="float-input" placeholder="Username" required>
                        <label for="edit_username" class="float-label">Username</label>
                        <span class="material-icons input-icon">badge</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="email" id="edit_email" class="float-input" placeholder="Email" required>
                        <label for="edit_email" class="float-label">Email</label>
                        <span class="material-icons input-icon">email</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="tel" id="edit_phone" class="float-input" placeholder="Phone" required>
                        <label for="edit_phone" class="float-label">Phone</label>
                        <span class="material-icons input-icon">phone</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="edit_role" class="float-input" placeholder="Role" disabled style="opacity: 0.6; cursor: not-allowed;">
                        <label for="edit_role" class="float-label">Role</label>
                        <span class="material-icons input-icon">admin_panel_settings</span>
                    </div>
                    <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 4px; padding-left: 12px;">Role cannot be changed here</small>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="password" id="edit_password" class="float-input" placeholder="New Password">
                        <label for="edit_password" class="float-label">New Password (Optional)</label>
                        <span class="material-icons input-icon">lock</span>
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('edit_password', this)">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                    <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 4px; padding-left: 12px;">Leave blank to keep current password</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-account-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px; vertical-align: middle;">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRoleFilter = '';
const currentUserRole = '<?php echo $_SESSION['role']; ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadAccounts();
    
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRoleFilter = this.dataset.role;
            loadAccounts();
        });
    });
});

async function loadAccounts() {
    try {
        const url = currentRoleFilter ? `../api/accounts/list.php?role=${currentRoleFilter}&status=active` : '../api/accounts/list.php?status=active';
        const response = await fetch(url);
        const data = await response.json();
        
        const tbody = document.getElementById('accounts-tbody');
        
        if (data.success && data.accounts.length > 0) {
            let html = '';
            data.accounts.forEach(acc => {
                html += `
                    <tr>
                        <td>${acc.full_name}</td>
                        <td>${acc.username}</td>
                        <td>${acc.email}</td>
                        <td><span class="badge badge-${acc.role}">${acc.role}</span></td>
                        <td><span class="badge badge-${acc.status}">${acc.status}</span></td>
                        <td>
                            ${acc.role === 'super_admin' ? 
                                `<span class="badge badge-protected" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="material-icons" style="font-size: 14px;">shield</span>
                                    PROTECTED
                                </span>` : 
                                (acc.role === 'admin' && currentUserRole === 'admin') ?
                                `<span class="badge badge-restricted" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="material-icons" style="font-size: 14px;">block</span>
                                    RESTRICTED
                                </span>` :
                                `${acc.role !== 'super_admin' ? 
                                    `<button class="btn-icon" onclick="editAccount(${acc.id})" title="Edit Account">
                                        <span class="material-icons">edit</span>
                                    </button>` : ''
                                }
                                ${(acc.role === 'customer' || acc.role === 'rider') ? 
                                    (acc.status === 'flagged' ? 
                                        `<button class="btn-icon" onclick="unflagAccount(${acc.id})" title="Unflag">
                                            <span class="material-icons">flag_circle</span>
                                        </button>` : 
                                        `<button class="btn-icon" onclick="flagAccount(${acc.id})" title="Flag">
                                            <span class="material-icons">flag</span>
                                        </button>`
                                    ) : ''
                                }
                                ${acc.role !== 'super_admin' && !(acc.role === 'admin' && currentUserRole === 'admin') ? 
                                    `<button class="btn-icon btn-danger" onclick="deleteAccount(${acc.id})" title="Delete Account">
                                        <span class="material-icons">delete</span>
                                    </button>` : ''
                                }`
                            }
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No accounts found</p></div></td></tr>';
        }
    } catch (error) {
        console.error('Failed to load accounts:', error);
    }
}

async function flagAccount(userId) {
    const reason = prompt('Reason for flagging:');
    if (!reason) return;
    
    try {
        const response = await fetch('../api/accounts/flag.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, action: 'flag', reason, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account flagged', 'success');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

async function unflagAccount(userId) {
    if (!confirm('Unflag this account?')) return;
    
    try {
        const response = await fetch('../api/accounts/flag.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, action: 'unflag', csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account unflagged', 'success');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

async function editAccount(userId) {
    try {
        // Fetch user details
        const response = await fetch(`../api/accounts/get.php?id=${userId}`);
        const data = await response.json();
        
        if (data.success && data.account) {
            const user = data.account;
            
            // Populate form
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_phone').value = user.phone;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_password').value = ''; // Clear password field
            
            // Show modal
            openModal('edit-account-modal');
        } else {
            showToast('Failed to load account details', 'error');
        }
    } catch (error) {
        console.error('Error loading account:', error);
        showToast('An error occurred', 'error');
    }
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    if (modalId === 'edit-account-modal') {
        document.getElementById('editAccountForm').reset();
    }
}

async function submitEditAccount(event) {
    event.preventDefault();
    
    const userId = document.getElementById('edit_user_id').value;
    const fullName = document.getElementById('edit_full_name').value;
    const username = document.getElementById('edit_username').value;
    const email = document.getElementById('edit_email').value;
    const phone = document.getElementById('edit_phone').value;
    const password = document.getElementById('edit_password').value;
    
    const payload = {
        user_id: parseInt(userId),
        full_name: fullName,
        username: username,
        email: email,
        phone: phone,
        csrf_token: getCSRFToken()
    };
    
    // Only include password if it's not empty
    if (password.trim() !== '') {
        payload.password = password;
    }
    
    try {
        const response = await fetch('../api/accounts/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account updated successfully', 'success');
            closeModal('edit-account-modal');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed to update account', 'error');
        }
    } catch (error) {
        console.error('Error updating account:', error);
        showToast('An error occurred', 'error');
    }
}

async function deleteAccount(userId) {
    const confirmed = await Swal.fire({
        title: 'Delete Account?',
        text: 'This action cannot be undone. All associated data will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });
    
    if (!confirmed.isConfirmed) return;
    
    try {
        const response = await fetch('../api/accounts/update.php', {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                user_id: userId,
                csrf_token: getCSRFToken() 
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account deleted successfully', 'success');
            loadAccounts();
        } else {
            showToast(data.message || 'Failed to delete account', 'error');
        }
    } catch (error) {
        console.error('Error deleting account:', error);
        showToast('An error occurred', 'error');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        const modalId = event.target.id;
        closeModal(modalId);
    }
});

// Password visibility toggle
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('.material-icons');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
