<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CUSTOMER ORDERS PAGE
 * ============================================================================
 * 
 * Purpose: View and manage customer orders
 * Role: CUSTOMER
 * 
 * Features:
 * - List all orders with filtering by status
 * - View order details
 * - Cancel pending orders
 * - Confirm delivery
 * - View receipt
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "My Orders";
$page_js = "orders.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_CUSTOMER]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">My Orders</h1>
        <p class="content-breadcrumb">
            <span>Home</span>
            <span class="breadcrumb-separator">/</span>
            <span>My Orders</span>
        </p>
    </div>
    
    <!-- Filter Bar -->
    <div class="glass-card" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <button class="filter-btn active" data-status="">All Orders</button>
            <button class="filter-btn" data-status="pending">Pending</button>
            <button class="filter-btn" data-status="confirmed">Confirmed</button>
            <button class="filter-btn" data-status="assigned">Assigned</button>
            <button class="filter-btn" data-status="on_delivery">On Delivery</button>
            <button class="filter-btn" data-status="delivered">Delivered</button>
            <button class="filter-btn" data-status="cancelled">Cancelled</button>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table" id="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div class="modal-overlay" id="order-details-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="modal-close" onclick="closeModal('order-details-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="modal-body" id="order-details-content">
            <!-- Content loaded dynamically -->
        </div>
        <div class="modal-footer" id="order-details-actions">
            <!-- Actions loaded dynamically -->
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
