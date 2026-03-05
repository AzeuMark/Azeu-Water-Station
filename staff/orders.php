<?php
/**
 * ============================================================================
 * AZEU WATER STATION - STAFF/ADMIN ORDERS MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Manage all orders (view, confirm, assign riders, cancel)
 * Role: STAFF, ADMIN
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Manage Orders";
$page_css = "main.css";
$page_js = "orders.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Manage Orders</h1>
    </div>
    
    <div class="glass-card" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <button class="filter-btn active" data-status="">All</button>
            <button class="filter-btn" data-status="pending">Pending</button>
            <button class="filter-btn" data-status="confirmed">Confirmed</button>
            <button class="filter-btn" data-status="assigned">Assigned</button>
            <button class="filter-btn" data-status="on_delivery">On Delivery</button>
            <button class="filter-btn" data-status="delivered">Delivered</button>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table" id="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr><td colspan="7" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div class="modal-overlay" id="order-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="modal-close" onclick="closeModal('order-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="modal-body" id="order-details"></div>
        <div class="modal-footer" id="order-actions"></div>
    </div>
</div>

<!-- Assign Rider Modal -->
<div class="modal-overlay" id="assign-rider-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Assign Rider</h3>
            <button class="modal-close" onclick="closeModal('assign-rider-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="assign-rider-form">
            <div class="modal-body">
                <input type="hidden" id="assign-order-id">
                <label for="rider-select" style="display: block; margin-bottom: 8px; font-weight: 600;">Select Rider</label>
                <select id="rider-select" class="form-select" required>
                    <option value="">Loading...</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-rider-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
