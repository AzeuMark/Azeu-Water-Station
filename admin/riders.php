<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDERS MANAGEMENT (CONSOLIDATED)
 * ============================================================================
 * 
 * Purpose: View rider list + statistics in one page
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
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h1 class="content-title">Riders</h1>
            <div style="display: flex; gap: 8px; align-items: center;">
                <label style="font-size: 14px; font-weight: 500; color: var(--text-secondary);">Sort by:</label>
                <select id="sort-select" class="form-select" style="width: auto; min-width: 180px; padding: 10px 14px;" onchange="sortRiders()">
                    <option value="name">Name (A-Z)</option>
                    <option value="total_desc">Most Deliveries</option>
                    <option value="total_asc">Least Deliveries</option>
                    <option value="completion_desc">Highest Completion %</option>
                    <option value="completion_asc">Lowest Completion %</option>
                    <option value="available">Available First</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Rider Cards with Stats -->
    <div id="riders-container" style="display: grid; gap: 20px;">
        <div style="text-align: center; padding: 60px;">
            <div class="spinner"></div>
        </div>
    </div>
</main>

<script>
let allRiders = [];

document.addEventListener('DOMContentLoaded', loadRiders);

async function loadRiders() {
    try {
        const response = await fetch('../api/riders/list.php');
        const data = await response.json();
        
        if (data.success && data.riders.length > 0) {
            allRiders = data.riders;
            sortRiders();
        } else {
            document.getElementById('riders-container').innerHTML = '<div class="glass-card"><div class="empty-state"><span class="material-icons empty-icon">directions_bike</span><p class="empty-title">No riders found</p></div></div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function sortRiders() {
    const sortBy = document.getElementById('sort-select').value;
    let sorted = [...allRiders];
    
    switch (sortBy) {
        case 'name':
            sorted.sort((a, b) => a.full_name.localeCompare(b.full_name));
            break;
        case 'total_desc':
            sorted.sort((a, b) => b.total_deliveries - a.total_deliveries);
            break;
        case 'total_asc':
            sorted.sort((a, b) => a.total_deliveries - b.total_deliveries);
            break;
        case 'completion_desc':
            sorted.sort((a, b) => getCompletionRate(b) - getCompletionRate(a));
            break;
        case 'completion_asc':
            sorted.sort((a, b) => getCompletionRate(a) - getCompletionRate(b));
            break;
        case 'available':
            sorted.sort((a, b) => (b.is_available ? 1 : 0) - (a.is_available ? 1 : 0));
            break;
    }
    
    renderRiders(sorted);
}

function getCompletionRate(rider) {
    return rider.total_deliveries > 0 
        ? Math.round((rider.completed_deliveries / rider.total_deliveries) * 100) 
        : 0;
}

function renderRiders(riders) {
    const container = document.getElementById('riders-container');
    
    let html = '';
    riders.forEach((rider, index) => {
        const completionRate = getCompletionRate(rider);
        const barColor = completionRate >= 80 ? 'var(--success)' : completionRate >= 50 ? 'var(--warning)' : 'var(--danger)';
        
        html += `
            <div class="glass-card" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #1976D2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">
                            ${rider.full_name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 16px;">${rider.full_name}</h4>
                            <p style="color: var(--text-muted); margin: 2px 0 0 0; font-size: 13px;">${rider.phone}</p>
                        </div>
                    </div>
                    <span class="badge ${rider.is_available ? 'badge-success' : 'badge-danger'}">
                        ${rider.is_available ? 'Available' : 'Unavailable'}
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px;">
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">${rider.total_deliveries}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Total</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">${rider.assigned_deliveries}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Assigned</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">${rider.completed_deliveries}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Completed</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--info);">${completionRate}%</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Rate</div>
                    </div>
                </div>
                
                <!-- Completion Progress Bar -->
                <div style="background: var(--surface); border-radius: 6px; height: 8px; overflow: hidden;">
                    <div style="height: 100%; width: ${completionRate}%; background: ${barColor}; border-radius: 6px; transition: width 0.5s ease;"></div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
