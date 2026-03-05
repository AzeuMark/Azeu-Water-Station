<?php
/**
 * ============================================================================
 * AZEU WATER STATION - INVENTORY MANAGEMENT
 * ============================================================================
 * 
 * Purpose: Manage inventory items and stock
 * Role: STAFF, ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Inventory";
$page_css = "main.css";
$page_js = "inventory.js";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="content-title">Inventory</h1>
            <button class="btn btn-primary" onclick="showAddItem()">
                <span class="material-icons">add</span> Add Item
            </button>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventory-tbody">
                    <tr><td colspan="5" style="text-align: center; padding: 40px;"><div class="spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add/Edit Item Modal -->
<div class="modal-overlay" id="item-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3 id="item-modal-title">Add Item</h3>
            <button class="modal-close" onclick="closeModal('item-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="item-form">
            <div class="modal-body">
                <input type="hidden" id="item-id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Item Name</label>
                    <input type="text" id="item-name" class="form-select" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Price</label>
                    <input type="number" id="item-price" class="form-select" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" id="item-stock" class="form-select" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('item-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal-overlay" id="restock-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h3>Restock Item</h3>
            <button class="modal-close" onclick="closeModal('restock-modal')">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="restock-form">
            <div class="modal-body">
                <input type="hidden" id="restock-item-id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Quantity to Add</label>
                    <input type="number" id="restock-qty" class="form-select" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('restock-modal')">Cancel</button>
                <button type="submit" class="btn btn-success">Restock</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
