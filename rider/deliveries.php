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
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
