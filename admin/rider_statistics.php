<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDER STATISTICS PAGE
 * ============================================================================
 * 
 * Purpose: View detailed rider performance statistics
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Rider Statistics";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Rider Statistics</h1>
    </div>
    
    <div id="riders-stats-container" style="display: grid; gap: 24px;">
        <div style="text-align: center; padding: 60px;">
            <div class="spinner"></div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', loadRiderStats);

async function loadRiderStats() {
    try {
        const response = await fetch('../api/riders/list.php');
        const data = await response.json();
        
        const container = document.getElementById('riders-stats-container');
        
        if (data.success && data.riders.length > 0) {
            let html = '';
            
            data.riders.forEach(rider => {
                const completionRate = rider.total_deliveries > 0 
                    ? Math.round((rider.completed_deliveries / rider.total_deliveries) * 100) 
                    : 0;
                
                html += `
                    <div class="glass-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <h3 style="margin: 0;">${rider.full_name}</h3>
                                <p style="color: var(--text-muted); margin: 4px 0 0 0;">${rider.phone}</p>
                            </div>
                            <span class="badge ${rider.is_available ? 'badge-success' : 'badge-danger'}">
                                ${rider.is_available ? 'Available' : 'Unavailable'}
                            </span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                            <div style="text-align: center; padding: 16px; background: var(--surface); border-radius: var(--radius-sm);">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">${rider.total_deliveries}</div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Total</div>
                            </div>
                            <div style="text-align: center; padding: 16px; background: var(--surface); border-radius: var(--radius-sm);">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">${rider.assigned_deliveries}</div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Assigned</div>
                            </div>
                            <div style="text-align: center; padding: 16px; background: var(--surface); border-radius: var(--radius-sm);">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--success);">${rider.completed_deliveries}</div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Completed</div>
                            </div>
                            <div style="text-align: center; padding: 16px; background: var(--surface); border-radius: var(--radius-sm);">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--info);">${completionRate}%</div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Completion</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="glass-card"><div class="empty-state"><p>No riders found</p></div></div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
