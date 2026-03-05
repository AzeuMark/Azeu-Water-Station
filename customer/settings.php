<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER SETTINGS PAGE
 * ============================================================================
 * 
 * Purpose: Account settings and preferences for customer
 * Role: CUSTOMER
 * 
 * Features:
 * - Update profile information (name, email, phone)
 * - Change password
 * - View cancellation status
 * - Submit cancellation appeal (if flagged)
 * - Dark mode toggle
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Settings";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

// Get user data
$user = get_user_by_id($_SESSION['user_id']);
$preferences = db_fetch("SELECT * FROM user_preferences WHERE user_id = ?", [$_SESSION['user_id']]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Settings</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>Settings</span>
        </p>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Profile Information -->
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Profile Information</h3>
            <form id="profile-form">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="full_name" style="display: block; margin-bottom: 8px; font-weight: 600;">Full Name</label>
                    <input type="text" id="full_name" class="form-select" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600;">Email</label>
                    <input type="email" id="email" class="form-select" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 600;">Phone</label>
                    <input type="tel" id="phone" class="form-select" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">
                    <span class="material-icons">save</span>
                    Update Profile
                </button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Change Password</h3>
            <form id="password-form">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="new_password" style="display: block; margin-bottom: 8px; font-weight: 600;">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" class="form-select" placeholder="Enter new password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="confirm_password" style="display: block; margin-bottom: 8px; font-weight: 600;">Confirm Password</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" class="form-select" placeholder="Confirm new password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">
                    <span class="material-icons">vpn_key</span>
                    Change Password
                </button>
            </form>
        </div>
        
        <!-- Preferences -->
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Preferences</h3>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--surface); border-radius: var(--radius-sm);">
                <div>
                    <div style="font-weight: 600; margin-bottom: 4px;">Dark Mode</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Use dark theme</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="dark-mode-toggle" <?php echo $preferences['dark_mode'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        
        <!-- Cancellation Status -->
        <div class="glass-card">
            <h3 style="margin-bottom: 20px;">Cancellation Status</h3>
            
            <div style="text-align: center; padding: 20px;">
                <?php 
                $maxCancellation = get_setting('max_cancellation') ?? 5;
                $percentage = ($user['cancellation_count'] / $maxCancellation) * 100;
                ?>
                
                <div style="width: 120px; height: 120px; margin: 0 auto 16px; position: relative;">
                    <svg width="120" height="120" style="transform: rotate(-90deg);">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="var(--border)" stroke-width="10"></circle>
                        <circle cx="60" cy="60" r="50" fill="none" 
                            stroke="<?php echo $user['status'] === 'flagged' ? 'var(--danger)' : 'var(--primary)'; ?>" 
                            stroke-width="10" stroke-dasharray="314.159" 
                            stroke-dashoffset="<?php echo 314.159 * (1 - $percentage / 100); ?>"
                            style="transition: stroke-dashoffset 0.3s ease;"></circle>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                            <?php echo $user['cancellation_count']; ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">of <?php echo $maxCancellation; ?></div>
                    </div>
                </div>
                
                <div style="font-weight: 600; margin-bottom: 8px;">
                    <?php if ($user['status'] === 'flagged'): ?>
                        <span class="badge badge-flagged">Account Flagged</span>
                    <?php else: ?>
                        Cancellations This Month
                    <?php endif; ?>
                </div>
                
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                    <?php if ($user['status'] === 'flagged'): ?>
                        Your account is flagged due to excessive cancellations.
                    <?php else: ?>
                        Resets on <?php echo date('M d, Y', strtotime($user['cancellation_reset_date'])); ?>
                    <?php endif; ?>
                </p>
                
                <?php if ($user['status'] === 'flagged'): ?>
                    <button class="btn btn-warning" onclick="submitAppeal()">
                        <span class="material-icons">gavel</span>
                        Submit Appeal
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Appeal Modal -->
<div class="modal-overlay" id="appeal-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Submit Cancellation Appeal</h3>
            <button class="modal-close" onclick="closeModal('appeal-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="appeal-form">
            <div class="modal-body">
                <p style="margin-bottom: 16px; color: var(--text-secondary);">
                    Please explain why your account should be unflagged. Your appeal will be reviewed by staff.
                </p>
                
                <div class="form-group">
                    <label for="appeal-reason" style="display: block; margin-bottom: 8px; font-weight: 600;">Reason</label>
                    <textarea id="appeal-reason" class="form-select" rows="5" placeholder="Enter your reason..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('appeal-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">send</span>
                    Submit Appeal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Profile form
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        user_id: <?php echo $_SESSION['user_id']; ?>,
        full_name: document.getElementById('full_name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        csrf_token: getCSRFToken()
    };
    
    showLoading();
    
    try {
        const response = await fetch('../api/accounts/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Profile updated successfully', 'success');
            // Update session
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Update profile error:', error);
        showToast('An error occurred', 'error');
    }
});

// Password form
document.getElementById('password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showToast('Password must be at least 6 characters', 'error');
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch('../api/accounts/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: <?php echo $_SESSION['user_id']; ?>,
                password: newPassword,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Password changed successfully', 'success');
            this.reset();
        } else {
            showToast(data.message || 'Failed to change password', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Change password error:', error);
        showToast('An error occurred', 'error');
    }
});

// Dark mode toggle
document.getElementById('dark-mode-toggle').addEventListener('change', function() {
    const isDark = this.checked;
    
    // Update theme immediately
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Save to database
    fetch('../api/settings/update_preferences.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            dark_mode: isDark ? 1 : 0,
            csrf_token: getCSRFToken()
        })
    });
});

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('.material-icons');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

// Submit appeal
function submitAppeal() {
    openModal('appeal-modal');
}

document.getElementById('appeal-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const reason = document.getElementById('appeal-reason').value;
    
    showLoading();
    
    try {
        const response = await fetch('../api/appeals/create.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                reason: reason,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast('Appeal submitted successfully', 'success');
            closeModal('appeal-modal');
            this.reset();
        } else {
            showToast(data.message || 'Failed to submit appeal', 'error');
        }
    } catch (error) {
        hideLoading();
        console.error('Submit appeal error:', error);
        showToast('An error occurred', 'error');
    }
});
</script>

<style>
.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--text-primary);
    font-size: 1rem;
    font-family: var(--font);
    transition: var(--transition);
}

.form-select:focus {
    outline: none;
    border-color: var(--primary);
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
}

.password-toggle:hover {
    color: var(--primary);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
