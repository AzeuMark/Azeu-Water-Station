<?php
/**
 * ============================================================================
 * AZEU WATER STATION - PENDING ACCOUNTS PAGE
 * ============================================================================
 * 
 * Purpose: Approve/reject pending customer registrations
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Pending Accounts";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Pending Accounts</h1>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pending-tbody">
                    <tr><td colspan="6" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', loadPending);

async function loadPending() {
    try {
        const response = await fetch('../api/accounts/list.php?status=pending');
        const data = await response.json();
        
        const tbody = document.getElementById('pending-tbody');
        
        if (data.success && data.accounts.length > 0) {
            let html = '';
            data.accounts.forEach(acc => {
                html += `
                    <tr>
                        <td>${acc.full_name}</td>
                        <td>${acc.username}</td>
                        <td>${acc.email}</td>
                        <td>${acc.phone}</td>
                        <td>${formatDate(acc.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="approve(${acc.id})">
                                <span class="material-icons">check</span> Approve
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No pending accounts</p></div></td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function approve(userId) {
    if (!confirm('Approve this account?')) return;
    
    try {
        const response = await fetch('../api/accounts/approve.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, csrf_token: getCSRFToken() })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account approved', 'success');
            loadPending();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (error) {
        showToast('Error occurred', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
