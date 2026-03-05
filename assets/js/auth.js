/**
 * Azeu Water Station - Authentication JavaScript
 * Login, Register, Forgot Password, Reset Password form handling
 */

// Login Form Handler
function initLoginForm() {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) return;
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Disable submit button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Logging in...';
        
        // Get form data
        const formData = {
            username: document.getElementById('username').value.trim(),
            password: document.getElementById('password').value,
            csrf_token: getCSRFToken()
        };
        
        try {
            const response = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Redirect based on role
                const redirects = {
                    'customer': 'customer/dashboard.php',
                    'rider': 'rider/dashboard.php',
                    'staff': 'staff/dashboard.php',
                    'admin': 'admin/dashboard.php',
                    'super_admin': 'admin/dashboard.php'
                };
                
                const redirect = redirects[data.role] || 'index.php';
                window.location.href = redirect;
            } else {
                showError(data.message || 'Login failed. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Login error:', error);
            showError('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Register Form Handler
function initRegisterForm() {
    const registerForm = document.getElementById('register-form');
    if (!registerForm) return;
    
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate password match
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            showError('Passwords do not match!');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Registering...';
        
        // Get form data
        const formData = {
            username: document.getElementById('username').value.trim(),
            password: password,
            full_name: document.getElementById('full_name').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            csrf_token: getCSRFToken()
        };
        
        try {
            const response = await fetch('api/auth/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Registration successful! Please wait for account approval.');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showError(data.message || 'Registration failed. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Registration error:', error);
            showError('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Forgot Password Form Handler
function initForgotPasswordForm() {
    const forgotForm = document.getElementById('forgot-password-form');
    if (!forgotForm) return;
    
    forgotForm.addEventListener('submit', async function(e) {
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
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Password reset link has been sent to your email!');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showError(data.message || 'Failed to send reset link.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Forgot password error:', error);
            showError('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Reset Password Form Handler
function initResetPasswordForm() {
    const resetForm = document.getElementById('reset-password-form');
    if (!resetForm) return;
    
    resetForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate password match
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            showError('Passwords do not match!');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Resetting...';
        
        // Get token from URL
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
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Password reset successful! Redirecting to login...');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showError(data.message || 'Failed to reset password.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Reset password error:', error);
            showError('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Password Toggle
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.password-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
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

// Show Error Message
function showError(message) {
    // Remove existing messages
    const existingError = document.querySelector('.error-message');
    if (existingError) existingError.remove();
    
    const existingSuccess = document.querySelector('.success-message');
    if (existingSuccess) existingSuccess.remove();
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `
        <span class="material-icons">error</span>
        <span>${message}</span>
    `;
    
    // Insert at top of form
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(errorDiv, form.firstChild);
    }
}

// Show Success Message
function showSuccess(message) {
    // Remove existing messages
    const existingError = document.querySelector('.error-message');
    if (existingError) existingError.remove();
    
    const existingSuccess = document.querySelector('.success-message');
    if (existingSuccess) existingSuccess.remove();
    
    // Create success message
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.innerHTML = `
        <span class="material-icons">check_circle</span>
        <span>${message}</span>
    `;
    
    // Insert at top of form
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(successDiv, form.firstChild);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initLoginForm();
    initRegisterForm();
    initForgotPasswordForm();
    initResetPasswordForm();
    initPasswordToggle();
});
