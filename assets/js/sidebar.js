/**
 * Azeu Water Station - Sidebar JavaScript
 * Unified hamburger toggle for desktop collapse and mobile slide-in
 */

function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const hamburgerToggle = document.querySelector('.hamburger-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar) return;
    
    // Desktop: load saved collapsed state
    if (window.innerWidth > 1024) {
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            updateLayout(true);
        } else {
            // Sidebar is expanded — show X (click to collapse)
            if (hamburgerToggle) hamburgerToggle.classList.add('active');
        }
    }
    
    // Unified hamburger toggle click
    if (hamburgerToggle) {
        hamburgerToggle.addEventListener('click', function() {
            if (window.innerWidth > 1024) {
                // Desktop: collapse/expand sidebar
                sidebar.classList.toggle('collapsed');
                const collapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebar-collapsed', collapsed);
                // active = X icon = sidebar is expanded; inactive = ☰ = sidebar is collapsed
                hamburgerToggle.classList.toggle('active', !collapsed);
                updateLayout(collapsed);
            } else {
                // Mobile/Tablet: slide sidebar in/out
                toggleMobileSidebar();
            }
        });
    }
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            closeMobileSidebar();
        });
    }
    
    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1024) {
            if (!sidebar.contains(e.target) && 
                !hamburgerToggle?.contains(e.target) && 
                !overlay?.contains(e.target)) {
                closeMobileSidebar();
            }
        }
    });
    
    // Close sidebar on mobile when clicking a link
    const sidebarLinks = sidebar.querySelectorAll('.sidebar-item');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                closeMobileSidebar();
            }
        });
    });
    
    highlightActivePage();
}

function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const hamburger = document.querySelector('.hamburger-toggle');
    if (!sidebar) return;
    
    const isOpen = sidebar.classList.contains('show');
    if (isOpen) {
        closeMobileSidebar();
    } else {
        sidebar.classList.add('show');
        if (overlay) overlay.classList.add('show');
        if (hamburger) hamburger.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const hamburger = document.querySelector('.hamburger-toggle');
    if (!sidebar) return;
    
    sidebar.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    if (hamburger) hamburger.classList.remove('active');
    document.body.style.overflow = '';
}

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

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
});

function updateLayout(isCollapsed) {
    const header = document.querySelector('.main-header');
    const content = document.querySelector('.main-content');
    
    if (window.innerWidth <= 1024) return;
    
    if (isCollapsed) {
        if (header) header.style.left = '70px';
        if (content) content.style.marginLeft = '70px';
    } else {
        if (header) header.style.left = '260px';
        if (content) content.style.marginLeft = '260px';
    }
}

window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    const header = document.querySelector('.main-header');
    const content = document.querySelector('.main-content');
    const hamburger = document.querySelector('.hamburger-toggle');
    
    if (!sidebar) return;
    
    if (window.innerWidth > 1024) {
        closeMobileSidebar();
        const collapsed = sidebar.classList.contains('collapsed');
        if (hamburger) hamburger.classList.toggle('active', !collapsed);
        updateLayout(collapsed);
    } else {
        if (header) header.style.left = '0';
        if (content) content.style.marginLeft = '0';
        // Reset hamburger state for mobile (only active when sidebar is shown)
        if (hamburger && !sidebar.classList.contains('show')) {
            hamburger.classList.remove('active');
        }
    }
});
