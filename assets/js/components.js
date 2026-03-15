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

    // Create mobile overlay if it doesn't exist
    if (!document.querySelector('.notif-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'notif-overlay';
        document.body.appendChild(overlay);
        overlay.addEventListener('click', closeNotifDropdown);
    }
    
    // Toggle dropdown
    notifBell.addEventListener('click', function(e) {
        e.stopPropagation();
        if (notifDropdown.classList.contains('show')) {
            closeNotifDropdown();
        } else {
            notifDropdown.classList.add('show');
            document.querySelector('.notif-overlay')?.classList.add('show');
            loadNotifications();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notifDropdown.contains(e.target) && !notifBell.contains(e.target)) {
            closeNotifDropdown();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNotifDropdown();
    });
    
    // Mark all as read
    const markReadBtn = document.querySelector('.notif-mark-read');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', markAllNotificationsRead);
    }
    
    // Load unread count on page load
    updateNotificationCount();

    // Poll for new notifications every 30 seconds
    setInterval(updateNotificationCount, 30000);
}

function closeNotifDropdown() {
    document.querySelector('.notif-dropdown')?.classList.remove('show');
    document.querySelector('.notif-overlay')?.classList.remove('show');
}

async function loadNotifications() {
    const notifList = document.querySelector('.notif-list');
    const notifEmpty = document.querySelector('.notif-empty');
    if (!notifList) return;

    // Show subtle loading state
    notifList.style.opacity = '0.5';

    try {
        const response = await fetch('../api/notifications/get.php');
        const data = await response.json();
        
        if (data.success) {
            notifList.style.opacity = '1';
            renderNotifications(data.notifications);
        }
    } catch (error) {
        notifList.style.opacity = '1';
        console.error('Failed to load notifications:', error);
    }
}

function renderNotifications(notifications) {
    const notifList = document.querySelector('.notif-list');
    const notifEmpty = document.querySelector('.notif-empty');
    if (!notifList) return;
    
    notifList.innerHTML = '';
    
    if (notifications.length === 0) {
        notifList.style.display = 'none';
        if (notifEmpty) notifEmpty.style.display = 'flex';
        return;
    }

    notifList.style.display = 'block';
    if (notifEmpty) notifEmpty.style.display = 'none';

    // Group notifications by date
    const groups = groupNotificationsByDate(notifications);
    let itemIndex = 0;

    for (const [label, items] of Object.entries(groups)) {
        // Add group label
        const groupLabel = document.createElement('div');
        groupLabel.className = 'notif-group-label';
        groupLabel.textContent = label;
        notifList.appendChild(groupLabel);

        items.forEach(notif => {
            const unreadClass = notif.is_read == 0 ? 'unread' : '';
            const iconInfo = getNotificationIconInfo(notif.type);
            const delay = Math.min(itemIndex * 0.04, 0.4);

            const item = document.createElement('div');
            item.className = `notif-item ${unreadClass}`;
            item.style.animationDelay = `${delay}s`;
            item.onclick = () => handleNotificationClick(notif.id, notif.reference_id, notif.type);

            item.innerHTML = `
                ${notif.is_read == 0 ? '<span class="notif-unread-dot"></span>' : ''}
                <div class="notif-icon ${iconInfo.colorClass}">
                    <span class="material-icons">${iconInfo.icon}</span>
                </div>
                <div class="notif-content">
                    <div class="notif-title">${escapeHtml(notif.title)}</div>
                    <div class="notif-message">${escapeHtml(notif.message)}</div>
                    <div class="notif-time">
                        <span class="material-icons">schedule</span>
                        ${timeAgo(notif.created_at)}
                    </div>
                </div>
            `;

            notifList.appendChild(item);
            itemIndex++;
        });
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function groupNotificationsByDate(notifications) {
    const groups = {};
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    notifications.forEach(notif => {
        const date = new Date(notif.created_at);
        const notifDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        
        let label;
        if (notifDate.getTime() === today.getTime()) {
            label = 'Today';
        } else if (notifDate.getTime() === yesterday.getTime()) {
            label = 'Yesterday';
        } else {
            label = 'Earlier';
        }

        if (!groups[label]) groups[label] = [];
        groups[label].push(notif);
    });

    return groups;
}

function getNotificationIconInfo(type) {
    const map = {
        'order_placed':     { icon: 'shopping_cart',  colorClass: 'notif-icon--order' },
        'order_confirmed':  { icon: 'check_circle',   colorClass: 'notif-icon--success' },
        'order_assigned':   { icon: 'assignment_ind',  colorClass: 'notif-icon--info' },
        'order_on_delivery':{ icon: 'local_shipping',  colorClass: 'notif-icon--delivery' },
        'order_delivered':  { icon: 'done_all',        colorClass: 'notif-icon--success' },
        'order_cancelled':  { icon: 'cancel',          colorClass: 'notif-icon--danger' },
        'account_approved': { icon: 'verified',        colorClass: 'notif-icon--account' },
        'account_flagged':  { icon: 'flag',            colorClass: 'notif-icon--danger' },
        'appeal_approved':  { icon: 'thumb_up',        colorClass: 'notif-icon--success' },
        'appeal_denied':    { icon: 'thumb_down',      colorClass: 'notif-icon--danger' },
        'low_stock':        { icon: 'warning',         colorClass: 'notif-icon--warning' },
        'ready_for_pickup': { icon: 'store',           colorClass: 'notif-icon--info' },
        'rider_reassigned': { icon: 'swap_horiz',      colorClass: 'notif-icon--delivery' },
        'system':           { icon: 'info',            colorClass: 'notif-icon--system' }
    };
    
    return map[type] || { icon: 'notifications', colorClass: 'notif-icon--order' };
}

async function handleNotificationClick(notifId, referenceId, type) {
    // Mark as read
    await markNotificationRead(notifId);
    
    closeNotifDropdown();

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
        const btn = document.querySelector('.notif-mark-read');
        if (btn) {
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        }

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

        if (btn) {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
        
        loadNotifications();
        updateNotificationCount();
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error);
        const btn = document.querySelector('.notif-mark-read');
        if (btn) {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    }
}

async function updateNotificationCount() {
    try {
        const response = await fetch('../api/notifications/count_unread.php');
        const data = await response.json();
        
        if (data.success) {
            const badge = document.querySelector('.notif-badge');
            const unreadLabel = document.querySelector('.notif-unread-count');

            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }

            if (unreadLabel) {
                if (data.count > 0) {
                    unreadLabel.textContent = `${data.count} unread`;
                    unreadLabel.style.display = 'inline';
                } else {
                    unreadLabel.style.display = 'none';
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
