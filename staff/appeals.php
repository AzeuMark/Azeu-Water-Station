<?php
/**
 * ============================================================================
 * AZEU WATER STATION - APPEALS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Review and approve/deny cancellation appeals
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Appeals";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Cancellation Appeals</h1>
    </div>
    
    <div class="glass-card" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <button class="filter-btn active" data-status="">All</button>
            <button class="filter-btn" data-status="pending">Pending</button>
            <button class="filter-btn" data-status="approved">Approved</button>
            <button class="filter-btn" data-status="denied">Denied</button>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="appeals-tbody">
                    <tr><td colspan="5" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Review Appeal Modal -->
<div class="modal-overlay" id="review-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Review Appeal</h3>
            <button class="modal-close" onclick="closeModal('review-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="review-form">
            <div class="modal-body">
                <input type="hidden" id="appeal-id">
                <div id="appeal-details" style="margin-bottom: 20px;"></div>
                <div class="form-group">
                    <label for="admin-notes">Admin Notes (Optional)</label>
                    <textarea id="admin-notes" class="form-select" rows="3" placeholder="Add notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('review-modal')">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="reviewAppeal('deny')">Deny</button>
                <button type="button" class="btn btn-success" onclick="reviewAppeal('approve')">Approve</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStatusFilter = '';

document.addEventListener('DOMContentLoaded', function() {
    loadAppeals();
    
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentStatusFilter = this.dataset.status;
            loadAppeals();
        });
    });
});

async function loadAppeals() {
    try {
        const url = currentStatusFilter ? `../api/appeals/list.php?status=${currentStatusFilter}` : '../api/appeals/list.php';
        const response = await fetch(url);
        const data = await response.json();
        
        const tbody = document.getElementById('appeals-tbody');
        
        if (data.success && data.appeals.length > 0) {
            let html = '';
            data.appeals.forEach(appeal => {
                html += `
                    <tr>
                        <td><strong>${appeal.customer_name}</strong></td>
                        <td>${truncate(appeal.reason, 60)}</td>
                        <td>${formatDate(appeal.created_at)}</td>
                        <td><span class="badge badge-${appeal.status}">${appeal.status}</span></td>
                        <td>
                            ${appeal.status === 'pending' ? 
                                `<button class="btn-icon" onclick="showReview(${appeal.id})" title="Review">
                                    <span class="material-icons">rate_review</span>
                                </button>` : 
                                `<span style="color: var(--text-muted);">Reviewed</span>`
                            }
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><p>No appeals found</p></div></td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function showReview(appealId) {
    try {
        const response = await fetch('../api/appeals/list.php');
        const data = await response.json();
        
        if (data.success) {
            const appeal = data.appeals.find(a => a.id === appealId);
            if (appeal) {
                document.getElementById('appeal-id').value = appealId;
                document.getElementById('appeal-details').innerHTML = `
                    <div><strong>Customer:</strong> ${appeal.customer_name}</div>
                    <div style="margin-top: 12px;"><strong>Reason:</strong></div>
                    <div style="background: var(--surface); padding: 12px; border-radius: var(--radius-sm); margin-top: 8px;">
                        ${appeal.reason}
                    </div>
                `;
                document.getElementById('admin-notes').value = '';
                openModal('review-modal');
            }
        }
    } catch (error) {
        showToast('Error loading appeal', 'error');
    }
}

async function reviewAppeal(action) {
    const appealId = document.getElementById('appeal-id').value;
    const notes = document.getElementById('admin-notes').value;
    
    if (!confirm(`${action === 'approve' ? 'Approve' : 'Deny'} this appeal?`)) return;
    
    showLoading();
    
    try {
        const response = await fetch('../api/appeals/review.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                appeal_id: parseInt(appealId),
                action: action,
                admin_notes: notes,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showToast(`Appeal ${action}d successfully`, 'success');
            closeModal('review-modal');
            loadAppeals();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Error occurred', 'error');
    }
}

function truncate(text, length) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
