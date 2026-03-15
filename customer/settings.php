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
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Settings";
$page_css = "settings.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

$user = get_user_by_id($_SESSION['user_id']);
$preferences = db_fetch("SELECT * FROM user_preferences WHERE user_id = ?", [$_SESSION['user_id']]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$maxCancellation = get_setting('max_cancellation') ?? 5;
$percentage = ($user['cancellation_count'] / $maxCancellation) * 100;
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

    <!-- Profile Header Card -->
    <div class="settings-profile-header">
        <div class="settings-avatar">
            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
        </div>
        <div class="settings-profile-info">
            <div class="settings-profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
            <div class="settings-profile-meta">
                <span class="settings-profile-email">
                    <span class="material-icons">mail</span>
                    <?php echo htmlspecialchars($user['email']); ?>
                </span>
            </div>
            <div class="settings-profile-joined">
                <span class="material-icons">calendar_today</span>
                Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
            </div>
        </div>
        <div class="settings-profile-role role-<?php echo htmlspecialchars($user['role']); ?>">
            <span class="material-icons">person</span>
            <?php echo htmlspecialchars(get_role_display_name($user['role'])); ?>
        </div>
    </div>

    <!-- Settings Panels Grid -->
    <div class="settings-grid">

        <!-- Profile Information -->
        <div class="settings-panel">
            <div class="settings-panel-header">
                <div class="settings-panel-icon" style="background: rgba(21, 101, 192, 0.1); color: var(--primary);">
                    <span class="material-icons">person</span>
                </div>
                <div>
                    <h3 class="settings-panel-title">Profile Information</h3>
                    <p class="settings-panel-desc">Update your personal details</p>
                </div>
            </div>
            <div class="settings-panel-body">
                <form id="profile-form">
                    <div class="settings-form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" class="form-select" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="settings-form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" class="form-select" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="settings-form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" class="form-select" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="material-icons">save</span>
                        Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="settings-panel">
            <div class="settings-panel-header">
                <div class="settings-panel-icon" style="background: rgba(239, 83, 80, 0.1); color: var(--danger);">
                    <span class="material-icons">lock</span>
                </div>
                <div>
                    <h3 class="settings-panel-title">Security</h3>
                    <p class="settings-panel-desc">Change your account password</p>
                </div>
            </div>
            <div class="settings-panel-body">
                <form id="password-form">
                    <div class="settings-form-group">
                        <label for="new_password">New Password</label>
                        <div class="settings-password-wrapper">
                            <input type="password" id="new_password" class="form-select" placeholder="Enter new password" required>
                            <button type="button" class="settings-password-toggle" onclick="togglePassword('new_password')">
                                <span class="material-icons">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="settings-form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="settings-password-wrapper">
                            <input type="password" id="confirm_password" class="form-select" placeholder="Confirm new password" required>
                            <button type="button" class="settings-password-toggle" onclick="togglePassword('confirm_password')">
                                <span class="material-icons">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="settings-password-hint">
                        <span class="material-icons">info</span>
                        Password must be at least 6 characters long
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="material-icons">vpn_key</span>
                        Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Cancellation Status -->
        <div class="settings-panel settings-grid-full">
            <div class="settings-panel-header">
                <div class="settings-panel-icon" style="background: <?php echo $user['status'] === 'flagged' ? 'rgba(239, 83, 80, 0.1)' : 'rgba(255, 152, 0, 0.1)'; ?>; color: <?php echo $user['status'] === 'flagged' ? 'var(--danger)' : 'var(--warning)'; ?>;">
                    <span class="material-icons"><?php echo $user['status'] === 'flagged' ? 'flag' : 'cancel'; ?></span>
                </div>
                <div>
                    <h3 class="settings-panel-title">Cancellation Status</h3>
                    <p class="settings-panel-desc">Track your monthly order cancellations</p>
                </div>
            </div>
            <div class="settings-panel-body">
                <div class="cancellation-status-container">
                    <div class="cancellation-circle">
                        <svg viewBox="0 0 130 130" width="100%" height="100%">
                            <circle cx="65" cy="65" r="54" fill="none" stroke="var(--border)" stroke-width="10"></circle>
                            <circle cx="65" cy="65" r="54" fill="none" 
                                stroke="<?php echo $user['status'] === 'flagged' ? 'var(--danger)' : 'var(--primary)'; ?>" 
                                stroke-width="10" stroke-dasharray="339.292" 
                                stroke-dashoffset="<?php echo 339.292 * (1 - $percentage / 100); ?>"
                                stroke-linecap="round"></circle>
                        </svg>
                        <div class="cancellation-circle-text">
                            <div class="cancellation-count"><?php echo $user['cancellation_count']; ?></div>
                            <div class="cancellation-max">of <?php echo $maxCancellation; ?></div>
                        </div>
                    </div>

                    <div class="cancellation-label">
                        <?php if ($user['status'] === 'flagged'): ?>
                            <span class="badge badge-flagged">Account Flagged</span>
                        <?php else: ?>
                            Cancellations This Month
                        <?php endif; ?>
                    </div>

                    <p class="cancellation-info">
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
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const btn = input.parentElement.querySelector('.settings-password-toggle');
    const icon = btn.querySelector('.material-icons');
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
