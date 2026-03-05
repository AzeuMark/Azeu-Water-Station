<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDERS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: View and manage rider accounts
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Riders";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Riders</h1>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Available</th>
                        <th>Total Deliveries</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody id="riders-tbody">
                    <tr><td colspan="6" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', loadRiders);

async function loadRiders() {
    try {
        const response = await fetch('../api/riders/list.php');
        const data = await response.json();
        
        const tbody = document.getElementById('riders-tbody');
        
        if (data.success && data.riders.length > 0) {
            let html = '';
            data.riders.forEach(rider => {
                html += `
                    <tr>
                        <td><strong>${rider.full_name}</strong></td>
                        <td>${rider.phone}</td>
                        <td>
                            <span class="badge ${rider.is_available ? 'badge-success' : 'badge-danger'}">
                                ${rider.is_available ? 'Available' : 'Unavailable'}
                            </span>
                        </td>
                        <td>${rider.total_deliveries}</td>
                        <td>${rider.assigned_deliveries}</td>
                        <td>${rider.completed_deliveries}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No riders</p></div></td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
