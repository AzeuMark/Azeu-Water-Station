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
 * - Sort by priority, area, customer, amount
 * - Drag-to-reorder priority
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
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <div>
                <h1 class="content-title">My Deliveries</h1>
                <p class="content-breadcrumb">
                    <span>Home</span>
                    <span class="breadcrumb-separator">/</span>
                    <span>My Deliveries</span>
                </p>
            </div>
            <span class="delivery-count-badge" id="delivery-count-badge" style="display: none;">
                <span id="delivery-count">0</span> active
            </span>
        </div>
    </div>

    <!-- Desktop Sort Bar -->
    <div class="glass-card filter-bar-desktop" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-weight: 500; font-size: 14px; white-space: nowrap;">
                    <span class="material-icons" style="font-size: 20px;">sort</span>
                    Sort by:
                </div>
                <button class="filter-btn active" data-sort="priority">
                    <span class="material-icons" style="font-size: 15px;">low_priority</span> Priority
                </button>
                <button class="filter-btn" data-sort="nearest">
                    <span class="material-icons" style="font-size: 15px;">near_me</span> Nearest
                </button>
                <button class="filter-btn" data-sort="group_area">
                    <span class="material-icons" style="font-size: 15px;">location_on</span> Group by Area
                </button>
                <button class="filter-btn" data-sort="customer">
                    <span class="material-icons" style="font-size: 15px;">person</span> Customer Name
                </button>
                <button class="filter-btn" data-sort="amount_asc">
                    <span class="material-icons" style="font-size: 15px;">arrow_upward</span> Amount ↑
                </button>
                <button class="filter-btn" data-sort="amount_desc">
                    <span class="material-icons" style="font-size: 15px;">arrow_downward</span> Amount ↓
                </button>
            </div>
            <div id="drag-hint" style="display: none; align-items: center; gap: 6px; font-size: 0.82rem; color: var(--text-muted); white-space: nowrap; flex-shrink: 0;">
                <span class="material-icons" style="font-size: 16px;">drag_indicator</span>
                Drag to reorder
            </div>
        </div>
    </div>

    <!-- Mobile Sort Dropdown -->
    <div class="glass-card filter-bar-mobile" style="margin-bottom: 24px; display: none;">
        <div style="padding: 16px;">
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger" id="mobile-sort-trigger">
                    <span class="material-icons" style="margin-right: 8px; font-size: 20px;">sort</span>
                    <span class="selected-text">Priority</span>
                    <span class="material-icons arrow">expand_more</span>
                </div>
                <div class="custom-select-options" id="mobile-sort-options">
                    <div class="custom-select-option selected" data-sort="priority">Priority</div>
                    <div class="custom-select-option" data-sort="nearest">Nearest</div>
                    <div class="custom-select-option" data-sort="group_area">Group by Area</div>
                    <div class="custom-select-option" data-sort="customer">Customer Name</div>
                    <div class="custom-select-option" data-sort="amount_asc">Amount ↑</div>
                    <div class="custom-select-option" data-sort="amount_desc">Amount ↓</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deliveries Container -->
    <div id="deliveries-container" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="text-align: center; padding: 60px;">
            <div class="spinner"></div>
        </div>
    </div>

    <!-- Pagination -->
    <div id="deliveries-pagination" style="display: none; justify-content: center; align-items: center; padding: 20px; margin-top: 8px; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-icon" onclick="prevDeliveriesPage()" id="del-prev-btn" title="Previous">
                <span class="material-icons">chevron_left</span>
            </button>
            <span id="del-page-info" style="font-size: 14px; font-weight: 500; color: var(--text-primary); min-width: 100px; text-align: center;">Page 1 of 1</span>
            <button class="btn-icon" onclick="nextDeliveriesPage()" id="del-next-btn" title="Next">
                <span class="material-icons">chevron_right</span>
            </button>
        </div>
    </div>
</main>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
