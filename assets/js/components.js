/**
 * Azeu Water Station - Component JavaScript
 * Dialog system, toast notifications, table pagination, notification dropdown
 */

// Toast Notification System
function showToast(message, type = 'info', duration = 4000) {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = {
        'success': 'check_circle',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    }[type] || 'info';
    
    toast.innerHTML = `
        <span class="material-icons">${icon}</span>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Dialog/Modal System
function showDialog(options) {
    const {
        title = 'Dialog',
        message = '',
        type = 'info',
        confirmText = 'OK',
        cancelText = 'Cancel',
        showCancel = false,
        onConfirm = null,
        onCancel = null,
        html = null
    } = options;
    
    // Use SweetAlert2 if available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            html: html || message,
            icon: type,
            showCancelButton: showCancel,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: '#1565C0',
            cancelButtonColor: '#9E9E9E'
        }).then((result) => {
            if (result.isConfirmed && onConfirm) {
                onConfirm();
            } else if (result.isDismissed && onCancel) {
                onCancel();
            }
        });
    } else {
        // Fallback to native confirm/alert
        if (showCancel) {
            if (confirm(message)) {
                if (onConfirm) onConfirm();
            } else {
                if (onCancel) onCancel();
            }
        } else {
            alert(message);
            if (onConfirm) onConfirm();
        }
    }
}

// Confirmation Dialog
function confirmDialog(message, onConfirm, onCancel = null) {
    showDialog({
        title: 'Confirm Action',
        message: message,
        type: 'warning',
        confirmText: 'Yes',
        cancelText: 'No',
        showCancel: true,
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// Custom Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Table Pagination
class TablePagination {
    constructor(tableId, itemsPerPage = 50) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;
        
        this.tbody = this.table.querySelector('tbody');
        this.itemsPerPage = itemsPerPage;
        this.currentPage = 1;
        this.items = [];
        this.filteredItems = [];
        
        this.init();
    }
    
    init() {
        // Get all rows
        this.items = Array.from(this.tbody.querySelectorAll('tr'));
        this.filteredItems = [...this.items];
        
        this.render();
        this.createPagination();
    }
    
    filter(filterFn) {
        this.filteredItems = this.items.filter(filterFn);
        this.currentPage = 1;
        this.render();
        this.createPagination();
    }
    
    search(query, columns = []) {
        query = query.toLowerCase();
        
        this.filteredItems = this.items.filter(row => {
            if (columns.length === 0) {
                return row.textContent.toLowerCase().includes(query);
            }
            
            return columns.some(colIndex => {
                const cell = row.cells[colIndex];
                return cell && cell.textContent.toLowerCase().includes(query);
            });
        });
        
        this.currentPage = 1;
        this.render();
        this.createPagination();
    }
    
    render() {
        // Hide all rows
        this.items.forEach(item => item.style.display = 'none');
        
        // Show current page items
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        const pageItems = this.filteredItems.slice(start, end);
        
        pageItems.forEach(item => item.style.display = '');
        
        // Show empty state if no items
        this.showEmptyState(this.filteredItems.length === 0);
    }
    
    showEmptyState(show) {
        let emptyRow = this.tbody.querySelector('.empty-row');
        
        if (show) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-row';
                const colCount = this.table.querySelectorAll('thead th').length;
                emptyRow.innerHTML = `
                    <td colspan="${colCount}">
                        <div class="empty-state">
                            <span class="material-icons empty-icon">inbox</span>
                            <p class="empty-title">No data found</p>
                            <p class="empty-message">No records match your criteria</p>
                        </div>
                    </td>
                `;
                this.tbody.appendChild(emptyRow);
            }
        } else {
            if (emptyRow) {
                emptyRow.remove();
            }
        }
    }
    
    createPagination() {
        let paginationContainer = document.querySelector('.pagination');
        
        if (!paginationContainer) {
            paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination';
            this.table.parentNode.appendChild(paginationContainer);
        }
        
        const totalPages = Math.ceil(this.filteredItems.length / this.itemsPerPage);
        
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Previous button
        html += `
            <button class="page-btn" ${this.currentPage === 1 ? 'disabled' : ''} onclick="tablePagination.goToPage(${this.currentPage - 1})">
                <span class="material-icons">chevron_left</span>
            </button>
        `;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                html += `
                    <button class="page-btn ${i === this.currentPage ? 'active' : ''}" onclick="tablePagination.goToPage(${i})">
                        ${i}
                    </button>
                `;
            } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                html += `<span>...</span>`;
            }
        }
        
        // Next button
        html += `
            <button class="page-btn" ${this.currentPage === totalPages ? 'disabled' : ''} onclick="tablePagination.goToPage(${this.currentPage + 1})">
                <span class="material-icons">chevron_right</span>
            </button>
        `;
        
        paginationContainer.innerHTML = html;
    }
    
    goToPage(page) {
        const totalPages = Math.ceil(this.filteredItems.length / this.itemsPerPage);
        
        if (page < 1 || page > totalPages) return;
        
        this.currentPage = page;
        this.render();
        this.createPagination();
    }
}

// Make TablePagination available globally
window.TablePagination = TablePagination;

// Notification Dropdown
function initNotificationDropdown() {
    const notifBell = document.querySelector('.notif-bell');
    const notifDropdown = document.querySelector('.notif-dropdown');
    
    if (!notifBell || !notifDropdown) return;
    
    // Toggle dropdown
    notifBell.addEventListener('click', function(e) {
        e.stopPropagation();
        notifDropdown.classList.toggle('show');
        
        if (notifDropdown.classList.contains('show')) {
            loadNotifications();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notifDropdown.contains(e.target) && e.target !== notifBell) {
            notifDropdown.classList.remove('show');
        }
    });
    
    // Mark all as read
    const markReadBtn = document.querySelector('.notif-mark-read');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', markAllNotificationsRead);
    }
    
    // Load unread count on page load
    updateNotificationCount();
}

async function loadNotifications() {
    try {
        const response = await fetch('../api/notifications/get.php');
        const data = await response.json();
        
        if (data.success) {
            renderNotifications(data.notifications);
        }
    } catch (error) {
        console.error('Failed to load notifications:', error);
    }
}

function renderNotifications(notifications) {
    const dropdown = document.querySelector('.notif-dropdown');
    if (!dropdown) return;
    
    let itemsHTML = '';
    
    if (notifications.length === 0) {
        itemsHTML = `
            <div class="empty-state" style="padding: 40px 20px;">
                <span class="material-icons empty-icon" style="font-size: 48px;">notifications_none</span>
                <p class="empty-message">No notifications</p>
            </div>
        `;
    } else {
        notifications.forEach(notif => {
            const unreadClass = notif.is_read == 0 ? 'unread' : '';
            itemsHTML += `
                <div class="notif-item ${unreadClass}" onclick="handleNotificationClick(${notif.id}, ${notif.reference_id}, '${notif.type}')">
                    <div class="notif-icon">
                        <span class="material-icons">${getNotificationIcon(notif.type)}</span>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">${notif.title}</div>
                        <div class="notif-message">${notif.message}</div>
                        <div class="notif-time">${timeAgo(notif.created_at)}</div>
                    </div>
                </div>
            `;
        });
    }
    
    const existingItems = dropdown.querySelectorAll('.notif-item, .empty-state');
    existingItems.forEach(el => el.remove());
    
    dropdown.insertAdjacentHTML('beforeend', itemsHTML);
}

function getNotificationIcon(type) {
    const icons = {
        'order_placed': 'shopping_cart',
        'order_confirmed': 'check_circle',
        'order_assigned': 'assignment',
        'order_on_delivery': 'local_shipping',
        'order_delivered': 'done_all',
        'order_cancelled': 'cancel',
        'account_approved': 'verified',
        'account_flagged': 'flag',
        'appeal_approved': 'thumb_up',
        'appeal_denied': 'thumb_down',
        'low_stock': 'warning',
        'ready_for_pickup': 'store',
        'rider_reassigned': 'swap_horiz',
        'system': 'info'
    };
    
    return icons[type] || 'notifications';
}

async function handleNotificationClick(notifId, referenceId, type) {
    // Mark as read
    await markNotificationRead(notifId);
    
    // Redirect based on notification type
    const redirects = {
        'order_placed': `orders.php?id=${referenceId}`,
        'order_confirmed': `orders.php?id=${referenceId}`,
        'order_assigned': `orders.php?id=${referenceId}`,
        'order_on_delivery': `orders.php?id=${referenceId}`,
        'order_delivered': `orders.php?id=${referenceId}`,
        'order_cancelled': `orders.php?id=${referenceId}`,
        'ready_for_pickup': `orders.php?id=${referenceId}`,
        'account_approved': 'dashboard.php',
        'account_flagged': 'settings.php',
        'low_stock': 'inventory.php'
    };
    
    const redirect = redirects[type];
    if (redirect) {
        window.location.href = redirect;
    }
}

async function markNotificationRead(notifId) {
    try {
        await fetch('../api/notifications/mark_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notification_id: notifId,
                csrf_token: getCSRFToken()
            })
        });
        
        updateNotificationCount();
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
}

async function markAllNotificationsRead() {
    try {
        await fetch('../api/notifications/mark_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                mark_all: true,
                csrf_token: getCSRFToken()
            })
        });
        
        loadNotifications();
        updateNotificationCount();
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error);
    }
}

async function updateNotificationCount() {
    try {
        const response = await fetch('../api/notifications/count_unread.php');
        const data = await response.json();
        
        if (data.success) {
            const badge = document.querySelector('.notif-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Failed to update notification count:', error);
    }
}

// Loading Overlay
function showLoading() {
    let overlay = document.querySelector('.spinner-overlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'spinner-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);
    }
    
    overlay.style.display = 'flex';
}

function hideLoading() {
    const overlay = document.querySelector('.spinner-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initNotificationDropdown();
    
    // Initialize modal close buttons
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(element => {
        element.addEventListener('click', function(e) {
            if (e.target === this) {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        });
    });
});
