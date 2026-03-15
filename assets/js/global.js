/**
 * Azeu Water Station - Global JavaScript
 * Theme toggle, Manila time clock, CSRF helper, role icon mapping
 */

// Theme Management
function initTheme() {
    // Check if force dark mode is enabled system-wide
    const forceDark = document.querySelector('meta[name="force-dark-mode"]');
    
    if (forceDark && forceDark.getAttribute('content') === '1') {
        // Force dark mode — override user preference
        document.documentElement.setAttribute('data-theme', 'dark');
        updateThemeIcon('dark');
        
        // Disable the toggle button visually
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.style.opacity = '0.4';
            themeToggle.style.cursor = 'not-allowed';
            themeToggle.title = 'Dark mode is enforced by admin';
        }
        return;
    }
    
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
}

function toggleTheme() {
    // Block toggle if force dark mode is enabled
    const forceDark = document.querySelector('meta[name="force-dark-mode"]');
    if (forceDark && forceDark.getAttribute('content') === '1') {
        showToast('Dark mode is enforced by the administrator', 'info');
        return;
    }
    
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
    
    // Update user preference in database via AJAX
    updateThemePreference(newTheme);
}

function updateThemeIcon(theme) {
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('.material-icons');
        if (icon) {
            icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
        }
    }
}

function updateThemePreference(theme) {
    const isDark = theme === 'dark' ? 1 : 0;
    
    fetch('../api/settings/update_preferences.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            dark_mode: isDark,
            csrf_token: getCSRFToken()
        })
    }).catch(err => console.error('Failed to update theme preference:', err));
}

// Header Time Clock
function updateManilaClock() {
    const clockElement = document.getElementById('manila-time');
    if (!clockElement) return;
    
    const tz = clockElement.getAttribute('data-timezone') || 'Asia/Manila';
    const now = new Date();
    const options = {
        timeZone: tz,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    
    const manilaTime = now.toLocaleString('en-US', options);
    clockElement.textContent = manilaTime;
}

function initClock() {
    updateManilaClock();
    setInterval(updateManilaClock, 1000);
}

// CSRF Token Helper
function getCSRFToken() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (tokenMeta) {
        return tokenMeta.getAttribute('content');
    }
    
    // Fallback: try to get from a hidden input
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    if (tokenInput) {
        return tokenInput.value;
    }
    
    return '';
}

// Role Icon Mapping
const roleIcons = {
    'customer': 'person',
    'rider': 'directions_bike',
    'staff': 'badge',
    'admin': 'admin_panel_settings',
    'super_admin': 'shield'
};

function getRoleIcon(role) {
    return roleIcons[role] || 'account_circle';
}

// Status Icon Mapping
const statusIcons = {
    'pending': 'schedule',
    'confirmed': 'check_circle',
    'assigned': 'assignment',
    'on_delivery': 'local_shipping',
    'delivered': 'done_all',
    'accepted': 'verified',
    'ready_for_pickup': 'store',
    'picked_up': 'shopping_bag',
    'cancelled': 'cancel'
};

function getStatusIcon(status) {
    return statusIcons[status] || 'info';
}

// Format Currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Format Date
function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    const options = {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    };
    
    return date.toLocaleString('en-US', options);
}

// Time Ago
function timeAgo(dateString) {
    if (!dateString) return '';
    
    const now = new Date();
    const past = new Date(dateString);
    const diff = Math.floor((now - past) / 1000); // seconds
    
    if (diff < 60) return 'just now';
    if (diff < 3600) {
        const mins = Math.floor(diff / 60);
        return mins + ' minute' + (mins > 1 ? 's' : '') + ' ago';
    }
    if (diff < 86400) {
        const hours = Math.floor(diff / 3600);
        return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
    }
    if (diff < 604800) {
        const days = Math.floor(diff / 86400);
        return days + ' day' + (days > 1 ? 's' : '') + ' ago';
    }
    if (diff < 2592000) {
        const weeks = Math.floor(diff / 604800);
        return weeks + ' week' + (weeks > 1 ? 's' : '') + ' ago';
    }
    
    return formatDate(dateString);
}

// Debounce Function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy to Clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('Copied to clipboard!', 'success');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initTheme();
    initClock();
    
    // Theme toggle button
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
});
