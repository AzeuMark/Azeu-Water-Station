/**
 * Azeu Water Station - Authentication JavaScript
 * Combined login/register with tab switching, forgot/reset password
 */

/* ============================
   TAB SWITCHING
   ============================ */
function initTabSwitching() {
    const tabs = document.querySelectorAll('.auth-tab');
    const track = document.querySelector('.auth-panels-track');
    const indicator = document.querySelector('.auth-tab-indicator');
    if (!tabs.length || !track) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.dataset.tab;
            switchTab(target);
        });
    });

    // Set initial indicator position
    const activeTab = document.querySelector('.auth-tab.active');
    if (activeTab && activeTab.dataset.tab === 'register') {
        if (indicator) indicator.style.transform = 'translateX(100%)';
    }
}

function switchTab(target) {
    const tabs = document.querySelectorAll('.auth-tab');
    const track = document.querySelector('.auth-panels-track');
    const indicator = document.querySelector('.auth-tab-indicator');
    if (!track) return;

    // Clear messages on switch
    document.querySelectorAll('.error-message, .success-message').forEach(el => el.remove());

    tabs.forEach(t => t.classList.remove('active'));
    const activeBtn = document.querySelector(`.auth-tab[data-tab="${target}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    if (target === 'register') {
        track.classList.add('show-register');
        if (indicator) indicator.style.transform = 'translateX(100%)';
    } else {
        track.classList.remove('show-register');
        if (indicator) indicator.style.transform = 'translateX(0)';
    }

    updateViewportHeight();

    // Update URL without reload
    const url = new URL(window.location);
    if (target === 'register') {
        url.searchParams.set('mode', 'register');
    } else {
        url.searchParams.delete('mode');
    }
    history.replaceState(null, '', url);
}

function updateViewportHeight() {
    const viewport = document.querySelector('.auth-panels-viewport');
    const track = document.querySelector('.auth-panels-track');
    if (!viewport || !track) return;

    const isRegister = track.classList.contains('show-register');
    const activePanel = document.getElementById(isRegister ? 'register-panel' : 'login-panel');
    if (activePanel) {
        viewport.style.height = activePanel.scrollHeight + 'px';
    }
}

/* ============================
   LOGIN FORM
   ============================ */
function initLoginForm() {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) return;

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Signing in...';

        const formData = {
            username: document.getElementById('username').value.trim(),
            password: document.getElementById('password').value,
            csrf_token: getCSRFToken()
        };

        try {
            const response = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                const redirects = {
                    'customer': 'customer/dashboard.php',
                    'rider': 'rider/dashboard.php',
                    'staff': 'staff/dashboard.php',
                    'admin': 'admin/dashboard.php',
                    'super_admin': 'admin/dashboard.php'
                };
                window.location.href = redirects[data.role] || 'login.php';
            } else {
                showError(data.message || 'Login failed. Please try again.', loginForm);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Login error:', error);
            showError('An error occurred. Please try again.', loginForm);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/* ============================
   REGISTER FORM (reg_ prefixed IDs)
   ============================ */
function initRegisterForm() {
    const registerForm = document.getElementById('register-form');
    if (!registerForm) return;

    registerForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const password = document.getElementById('reg_password').value;
        const confirmPassword = document.getElementById('reg_confirm_password').value;

        if (password !== confirmPassword) {
            showError('Passwords do not match!', registerForm);
            return;
        }

        // Validate required fields
        const firstName = document.getElementById('reg_first_name').value.trim();
        const lastName = document.getElementById('reg_last_name').value.trim();
        const address = document.getElementById('reg_address').value.trim();

        if (!firstName) {
            showError('First name is required!', registerForm);
            return;
        }

        if (!lastName) {
            showError('Last name is required!', registerForm);
            return;
        }

        if (!address) {
            showError('Address is required!', registerForm);
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Creating account...';

        // Construct full name from first, middle initial, and last name
        const middleInitial = document.getElementById('reg_middle_initial').value.trim();
        let fullName = firstName;
        if (middleInitial) {
            fullName += ' ' + middleInitial + '.';
        }
        fullName += ' ' + lastName;

        const formData = {
            username: document.getElementById('reg_username').value.trim(),
            password: password,
            first_name: firstName,
            middle_initial: middleInitial,
            last_name: lastName,
            full_name: fullName,
            email: document.getElementById('reg_email').value.trim(),
            phone: document.getElementById('reg_phone').value.trim(),
            address: address,
            csrf_token: getCSRFToken()
        };

        try {
            const response = await fetch('api/auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                // Show success then switch to login tab
                showSuccess('Registration successful! Please wait for account approval.', registerForm);
                registerForm.reset();
                const strengthWrap = document.getElementById('password-strength');
                if (strengthWrap) strengthWrap.classList.remove('active');

                setTimeout(() => {
                    switchTab('login');
                    showSuccess('Account created! You can sign in once approved.', document.getElementById('login-form'));
                }, 2000);
            } else {
                showError(data.message || 'Registration failed. Please try again.', registerForm);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Registration error:', error);
            showError('An error occurred. Please try again.', registerForm);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/* ============================
   FORGOT PASSWORD FORM
   ============================ */
function initForgotPasswordForm() {
    const forgotForm = document.getElementById('forgot-password-form');
    if (!forgotForm) return;

    forgotForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Sending...';

        const formData = {
            email: document.getElementById('email').value.trim(),
            csrf_token: getCSRFToken()
        };

        try {
            const response = await fetch('api/auth/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showSuccess('Password reset link has been sent to your email!', forgotForm);
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                showError(data.message || 'Failed to send reset link.', forgotForm);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Forgot password error:', error);
            showError('An error occurred. Please try again.', forgotForm);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/* ============================
   RESET PASSWORD FORM
   ============================ */
function initResetPasswordForm() {
    const resetForm = document.getElementById('reset-password-form');
    if (!resetForm) return;

    resetForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            showError('Passwords do not match!', resetForm);
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Resetting...';

        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        const formData = {
            token: token,
            password: password,
            csrf_token: getCSRFToken()
        };

        try {
            const response = await fetch('api/auth/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showSuccess('Password reset successful! Redirecting to login...', resetForm);
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                showError(data.message || 'Failed to reset password.', resetForm);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Reset password error:', error);
            showError('An error occurred. Please try again.', resetForm);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/* ============================
   PASSWORD TOGGLE
   ============================ */
function initPasswordToggle() {
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function () {
            const input = this.closest('.float-input-group').querySelector('input');
            const icon = this.querySelector('.material-icons');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        });
    });
}

/* ============================
   PASSWORD STRENGTH METER
   ============================ */
function initPasswordStrength() {
    const passwordInput = document.getElementById('reg_password');
    const strengthWrap = document.getElementById('password-strength');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    if (!passwordInput || !strengthWrap) return;

    passwordInput.addEventListener('input', function () {
        const val = this.value;
        if (!val) {
            strengthWrap.classList.remove('active');
            return;
        }
        strengthWrap.classList.add('active');

        let score = 0;
        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { cls: 'weak', text: 'Weak' },
            { cls: 'weak', text: 'Weak' },
            { cls: 'fair', text: 'Fair' },
            { cls: 'good', text: 'Good' },
            { cls: 'strong', text: 'Strong' },
            { cls: 'strong', text: 'Very Strong' }
        ];

        const level = levels[Math.min(score, 5)];
        strengthBar.className = 'strength-bar-fill ' + level.cls;
        strengthText.className = 'strength-text ' + level.cls;
        strengthText.textContent = level.text;
    });
}

/* ============================
   MESSAGES (target specific form)
   ============================ */
function getActiveForm() {
    // On combined page, find the visible panel's form
    const track = document.querySelector('.auth-panels-track');
    if (track) {
        const isRegister = track.classList.contains('show-register');
        return document.getElementById(isRegister ? 'register-form' : 'login-form');
    }
    // Fallback: first form on page (forgot password, reset password pages)
    return document.querySelector('form');
}

function showError(message, targetForm) {
    const form = targetForm || getActiveForm();
    if (!form) return;

    // Clear existing messages in this form
    form.querySelectorAll('.error-message, .success-message').forEach(el => el.remove());

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `
        <span class="material-icons">error</span>
        <span>${message}</span>
    `;
    form.insertBefore(errorDiv, form.firstChild);
}

function showSuccess(message, targetForm) {
    const form = targetForm || getActiveForm();
    if (!form) return;

    // Clear existing messages in this form
    form.querySelectorAll('.error-message, .success-message').forEach(el => el.remove());

    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.innerHTML = `
        <span class="material-icons">check_circle</span>
        <span>${message}</span>
    `;
    form.insertBefore(successDiv, form.firstChild);
}

/* ============================
   INIT
   ============================ */
document.addEventListener('DOMContentLoaded', function () {
    initTabSwitching();
    initLoginForm();
    initRegisterForm();
    initForgotPasswordForm();
    initResetPasswordForm();
    initPasswordToggle();
    initPasswordStrength();
    updateViewportHeight();
});
