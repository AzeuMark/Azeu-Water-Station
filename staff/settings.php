<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF SETTINGS
 * ============================================================================
 * 
 * Purpose: Staff account settings
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Settings";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$user = get_user_by_id($_SESSION['user_id']);
$preferences = db_fetch("SELECT * FROM user_preferences WHERE user_id = ?", [$_SESSION['user_id']]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Settings</h1>
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
                    <input type="password" id="new_password" class="form-select" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="confirm_password" style="display: block; margin-bottom: 8px; font-weight: 600;">Confirm Password</label>
                    <input type="password" id="confirm_password" class="form-select" required>
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
    </div>
</main>

<script>
// Profile form
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    showLoading();
    
    try {
        const response = await fetch('../api/accounts/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: <?php echo $_SESSION['user_id']; ?>,
                full_name: document.getElementById('full_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        hideLoading();
        
        if (data.success) {
            showToast('Profile updated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        hideLoading();
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
        showToast('An error occurred', 'error');
    }
});

// Dark mode toggle
document.getElementById('dark-mode-toggle').addEventListener('change', function() {
    const isDark = this.checked;
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    fetch('../api/settings/update_preferences.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            dark_mode: isDark ? 1 : 0,
            csrf_token: getCSRFToken()
        })
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
