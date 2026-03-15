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
    
    <!-- Pagination -->
    <div id="riders-pagination" class="pagination-controls-wrapper" style="display: none; margin-top: 20px; border: 1px solid var(--border); border-radius: var(--radius);">
        <div class="pagination-controls">
            <button class="btn-icon" id="riders-prev-btn" title="Previous Page">
                <span class="material-icons">chevron_left</span>
            </button>
            <span class="page-info" id="riders-page-info">Page 1 of 1</span>
            <button class="btn-icon" id="riders-next-btn" title="Next Page">
                <span class="material-icons">chevron_right</span>
            </button>
        </div>
    </div>
</main>

<script>
let allRiders = [];
let sortedRiders = [];
let currentSort = 'name';
let currentPage = 1;

function getPageSize() {
    return window.innerWidth <= 1024 ? 5 : 10;
}

document.addEventListener('DOMContentLoaded', () => {
    loadRiders();
    initFilterButtons();
    window.addEventListener('resize', () => {
        const oldSize = getPageSize();
        // Re-render on breakpoint cross
        renderPage();
    });
});

function initFilterButtons() {
    // Desktop buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.dataset.sort;
            currentPage = 1;
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
            currentPage = 1;
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
            currentPage = 1;
            sortRiders();
        } else {
            document.getElementById('riders-container').innerHTML = '<div class="glass-card"><div class="empty-state"><span class="material-icons empty-icon">directions_bike</span><p class="empty-title">No riders found</p></div></div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function sortRiders() {
    sortedRiders = [...allRiders];
    
    switch (currentSort) {
        case 'name':
            sortedRiders.sort((a, b) => a.full_name.localeCompare(b.full_name));
            break;
        case 'total_desc':
            sortedRiders.sort((a, b) => (b.total_deliveries || 0) - (a.total_deliveries || 0));
            break;
        case 'total_asc':
            sortedRiders.sort((a, b) => (a.total_deliveries || 0) - (b.total_deliveries || 0));
            break;
        case 'completion_desc':
            sortedRiders.sort((a, b) => getCompletionRate(b) - getCompletionRate(a));
            break;
        case 'completion_asc':
            sortedRiders.sort((a, b) => getCompletionRate(a) - getCompletionRate(b));
            break;
        case 'available':
            sortedRiders.sort((a, b) => (b.is_available ? 1 : 0) - (a.is_available ? 1 : 0));
            break;
    }
    
    renderPage();
}

function getCompletionRate(rider) {
    const total = rider.total_deliveries || 0;
    const completed = rider.completed_deliveries || 0;
    return total > 0 ? Math.round((completed / total) * 100) : 0;
}

function renderPage() {
    const pageSize = getPageSize();
    const totalPages = Math.ceil(sortedRiders.length / pageSize);
    
    // Clamp current page
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;
    
    const start = (currentPage - 1) * pageSize;
    const pageRiders = sortedRiders.slice(start, start + pageSize);
    
    renderRiders(pageRiders);
    renderPagination(totalPages, pageSize);
}

function renderRiders(riders) {
    const container = document.getElementById('riders-container');
    
    let html = '';
    riders.forEach((rider) => {
        const totalDeliveries = rider.total_deliveries || 0;
        const activeDeliveries = rider.active_deliveries || 0;
        const completedDeliveries = rider.completed_deliveries || 0;
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
                            <p style="color: var(--text-muted); margin: 2px 0 0 0; font-size: 13px;">${rider.phone || '—'}</p>
                        </div>
                    </div>
                    <span class="badge ${rider.is_available ? 'badge-success' : 'badge-danger'}">
                        ${rider.is_available ? 'Available' : 'Unavailable'}
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px;">
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">${totalDeliveries}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Total</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">${activeDeliveries}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Assigned</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--surface); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">${completedDeliveries}</div>
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

function renderPagination(totalPages, pageSize) {
    const paginationEl = document.getElementById('riders-pagination');
    const pageInfo = document.getElementById('riders-page-info');
    const prevBtn = document.getElementById('riders-prev-btn');
    const nextBtn = document.getElementById('riders-next-btn');
    
    if (totalPages <= 1) {
        paginationEl.style.display = 'none';
        return;
    }
    
    paginationEl.style.display = 'flex';
    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
    prevBtn.onclick = () => goToPage(currentPage - 1);
    nextBtn.onclick = () => goToPage(currentPage + 1);
}

function goToPage(page) {
    const pageSize = getPageSize();
    const totalPages = Math.ceil(sortedRiders.length / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderPage();
    // Scroll to top of container
    document.getElementById('riders-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
