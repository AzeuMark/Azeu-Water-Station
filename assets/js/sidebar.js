/**
 * Azeu Water Station - Sidebar JavaScript
 * Collapse/expand, hamburger toggle, active page highlight
 */

// Sidebar Toggle
function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const collapseBtn = document.querySelector('.collapse-btn');
    const hamburgerToggle = document.querySelector('.hamburger-toggle');
    
    if (!sidebar) return;
    
    // Load saved state
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }
    
    // Collapse/Expand button (desktop)
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            const collapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', collapsed);
            
            // Update header and content positioning
            updateLayout(collapsed);
        });
    }
    
    // Update layout on initial load if sidebar is collapsed
    if (isCollapsed) {
        updateLayout(true);
    }
    
    // Hamburger toggle (mobile)
    if (hamburgerToggle) {
        hamburgerToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1024) {
            if (!sidebar.contains(e.target) && !hamburgerToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    
    // Close sidebar on mobile when clicking a link
    const sidebarLinks = sidebar.querySelectorAll('.sidebar-item');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('show');
            }
        });
    });
    
    // Highlight active page
    highlightActivePage();
}

// Highlight Active Page
function highlightActivePage() {
    const currentPage = window.location.pathname.split('/').pop();
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    
    sidebarItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href) {
            const pageName = href.split('/').pop();
            
            if (pageName === currentPage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
});

// Update header and content layout based on sidebar state
function updateLayout(isCollapsed) {
    const header = document.querySelector('.main-header');
    const content = document.querySelector('.main-content');
    
    if (isCollapsed) {
        if (header) header.style.left = '70px';
        if (content) content.style.marginLeft = '70px';
    } else {
        if (header) header.style.left = '260px';
        if (content) content.style.marginLeft = '260px';
    }
}

// Responsive sidebar handling
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    const header = document.querySelector('.main-header');
    const content = document.querySelector('.main-content');
    
    if (!sidebar) return;
    
    if (window.innerWidth > 1024) {
        sidebar.classList.remove('show');
        // Restore desktop layout
        const collapsed = sidebar.classList.contains('collapsed');
        updateLayout(collapsed);
    } else {
        // Mobile layout - full width
        if (header) header.style.left = '0';
        if (content) content.style.marginLeft = '0';
    }
});
