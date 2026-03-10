<?php
/**
 * ============================================================================
 * AZEU WATER STATION - RIDER DELIVERIES PAGE
 * ============================================================================
 * 
 * Purpose: Manage active deliveries (on delivery status)
 * Role: RIDER
 * 
 * Features:
 * - View current delivery details
 * - Update delivery status (on_delivery → delivered)
 * - View customer contact info
 * - View delivery address
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "My Deliveries";
$page_css = "deliveries.css";
$page_js = "deliveries.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_RIDER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">My Deliveries</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>My Deliveries</span>
        </p>
    </div>
    
    <!-- Active Deliveries -->
    <div class="glass-card">
        <h3 style="margin-bottom: 20px;">Active Deliveries</h3>
        
        <div id="deliveries-container">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-controls-wrapper" id="deliveries-pagination" style="display: none;">
            <div class="pagination-controls">
                <button class="btn-icon" onclick="prevDeliveriesPage()" id="del-prev-btn" title="Previous">
                    <span class="material-icons">chevron_left</span>
                </button>
                <span class="page-info" id="del-page-info">Page 1 of 1</span>
                <button class="btn-icon" onclick="nextDeliveriesPage()" id="del-next-btn" title="Next">
                    <span class="material-icons">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</main>

<style>
.pagination-controls-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    border-top: 1px solid var(--border);
}
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}
.page-info {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    padding: 0 8px;
    min-width: 100px;
    text-align: center;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
