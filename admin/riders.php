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
        </div>
    </div>
    
    <!-- Desktop Filter Bar -->
    <div class="glass-card filter-bar-desktop" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">sort</span>
                    Sort by:
                </div>
                <button class="filter-btn active" data-sort="name">Name (A-Z)</button>
                <button class="filter-btn" data-sort="total_desc">Most Deliveries</button>
                <button class="filter-btn" data-sort="total_asc">Least Deliveries</button>
                <button class="filter-btn" data-sort="completion_desc">Highest Completion %</button>
                <button class="filter-btn" data-sort="completion_asc">Lowest Completion %</button>
                <button class="filter-btn" data-sort="available">Available First</button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Filter Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-filter-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">sort</span>
                    <span class="selected-text">Name (A-Z)</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-filter-options">
                    <div class="custom-select-option selected" data-sort="name">Name (A-Z)</div>
                    <div class="custom-select-option" data-sort="total_desc">Most Deliveries</div>
                    <div class="custom-select-option" data-sort="total_asc">Least Deliveries</div>
                    <div class="custom-select-option" data-sort="completion_desc">Highest Completion %</div>
                    <div class="custom-select-option" data-sort="completion_asc">Lowest Completion %</div>
                    <div class="custom-select-option" data-sort="available">Available First</div>
                </div>
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
let currentSort = 'name';

document.addEventListener('DOMContentLoaded', () => {
    loadRiders();
    initFilterButtons();
});

function initFilterButtons() {
    // Desktop buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.dataset.sort;
            sortRiders();
            
            // Sync mobile dropdown text
            const text = this.textContent;
            const mobileSelectedText = document.querySelector('#mobile-filter-trigger .selected-text');
            if(mobileSelectedText) mobileSelectedText.textContent = text;
            
            // Sync mobile options
            document.querySelectorAll('#mobile-filter-options .custom-select-option').forEach(opt => {
                opt.classList.remove('selected');
                if(opt.dataset.sort === currentSort) opt.classList.add('selected');
            });
        });
    });

    // Mobile Dropdown logic
    const mobileTrigger = document.getElementById('mobile-filter-trigger');
    const mobileOptions = document.getElementById('mobile-filter-options');
    
    if (mobileTrigger && mobileOptions) {
        mobileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileTrigger.classList.toggle('active');
            mobileOptions.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (!mobileTrigger.contains(e.target) && !mobileOptions.contains(e.target)) {
                mobileTrigger.classList.remove('active');
                mobileOptions.classList.remove('active');
            }
        });
        
        mobileOptions.addEventListener('click', function(e) {
            const option = e.target.closest('.custom-select-option');
            if (!option) return;
            
            const sortType = option.dataset.sort;
            
            mobileOptions.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            
            mobileTrigger.querySelector('.selected-text').textContent = option.textContent.trim();
            mobileTrigger.classList.remove('active');
            mobileOptions.classList.remove('active');
            
            currentSort = sortType;
            sortRiders();
            
            // Sync desktop active state
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('active');
                if(b.dataset.sort === sortType) b.classList.add('active');
            });
        });
    }
}

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
    let sorted = [...allRiders];
    
    switch (currentSort) {
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
