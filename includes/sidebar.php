<?php
/**
 * Azeu Water Station - Sidebar Include
 * Dynamic sidebar navigation based on user role
 */

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

$current_user = get_logged_in_user();
$role = $current_user['role'];
$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$station_logo = get_setting('station_logo') ?? 'images/system/logo-1.png';

// Fetch sidebar badge counts for staff/admin roles
$badge_counts = ['orders' => 0, 'pending_accounts' => 0, 'inventory' => 0,
                 'orders_on_delivery' => 0, 'rider_active' => 0, 'rider_assigned' => 0];
if (in_array($role, [ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN])) {
    $badge_counts['orders']           = (int)(db_fetch("SELECT COUNT(*) as cnt FROM orders WHERE status = 'pending'")['cnt'] ?? 0);
    $badge_counts['pending_accounts'] = (int)(db_fetch("SELECT COUNT(*) as cnt FROM users WHERE status = 'pending' AND deleted_at IS NULL")['cnt'] ?? 0);
    $badge_counts['inventory']        = (int)(db_fetch("SELECT COUNT(*) as cnt FROM inventory WHERE status = 'out_of_stock'")['cnt'] ?? 0);
}
if ($role === ROLE_CUSTOMER) {
    $cid = (int)$_SESSION['user_id'];
    $badge_counts['orders_on_delivery'] = (int)(db_fetch("SELECT COUNT(*) as cnt FROM orders WHERE customer_id = {$cid} AND status = 'on_delivery'")['cnt'] ?? 0);
}
if ($role === ROLE_RIDER) {
    $rid = (int)$_SESSION['user_id'];
    $badge_counts['rider_active']   = (int)(db_fetch("SELECT COUNT(*) as cnt FROM orders WHERE rider_id = {$rid} AND status = 'on_delivery'")['cnt'] ?? 0);
    $badge_counts['rider_assigned'] = (int)(db_fetch("SELECT COUNT(*) as cnt FROM orders WHERE rider_id = {$rid} AND status = 'assigned'")['cnt'] ?? 0);
}

// Define menu items per role
$menu_items = [];

switch ($role) {
    case ROLE_CUSTOMER:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'add_shopping_cart', 'label' => 'Place Order', 'href' => 'place_order.php'],
            ['icon' => 'shopping_bag', 'label' => 'My Orders', 'href' => 'orders.php', 'badge' => $badge_counts['orders_on_delivery']],
            ['icon' => 'location_on', 'label' => 'Addresses', 'href' => 'addresses.php'],
            ['divider' => true],
            ['icon' => 'settings', 'label' => 'Settings', 'href' => 'settings.php'],
            ['icon' => 'logout', 'label' => 'Logout', 'href' => '../api/auth/logout.php']
        ];
        break;
        
    case ROLE_RIDER:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'local_shipping', 'label' => 'My Deliveries', 'href' => 'deliveries.php', 'badge' => $badge_counts['rider_active']],
            ['icon' => 'swap_vert', 'label' => 'Assigned Deliveries', 'href' => 'assigned_deliveries.php', 'badge' => $badge_counts['rider_assigned']],
            ['icon' => 'history', 'label' => 'Delivery History', 'href' => 'delivery_history.php'],
            ['divider' => true],
            ['icon' => 'settings', 'label' => 'Settings', 'href' => 'settings.php'],
            ['icon' => 'logout', 'label' => 'Logout', 'href' => '../api/auth/logout.php']
        ];
        break;
        
    case ROLE_STAFF:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'receipt_long', 'label' => 'Orders', 'href' => 'orders.php', 'badge' => $badge_counts['orders']],
            ['icon' => 'people', 'label' => 'Accounts', 'href' => 'accounts.php'],
            ['icon' => 'pending_actions', 'label' => 'Pending Accounts', 'href' => 'pending_accounts.php', 'badge' => $badge_counts['pending_accounts']],
            ['icon' => 'inventory_2', 'label' => 'Inventory', 'href' => 'inventory.php', 'badge' => $badge_counts['inventory']],
            ['icon' => 'directions_bike', 'label' => 'Riders', 'href' => 'riders.php'],
            ['icon' => 'gavel', 'label' => 'Appeals', 'href' => 'appeals.php'],
            ['divider' => true],
            ['icon' => 'settings', 'label' => 'Settings', 'href' => 'settings.php'],
            ['icon' => 'logout', 'label' => 'Logout', 'href' => '../api/auth/logout.php']
        ];
        break;
        
    case ROLE_ADMIN:
    case ROLE_SUPER_ADMIN:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'receipt_long', 'label' => 'Orders', 'href' => 'orders.php', 'badge' => $badge_counts['orders']],
            ['icon' => 'people', 'label' => 'Accounts', 'href' => 'accounts.php'],
            ['icon' => 'pending_actions', 'label' => 'Pending Accounts', 'href' => 'pending_accounts.php', 'badge' => $badge_counts['pending_accounts']],
            ['icon' => 'inventory_2', 'label' => 'Inventory', 'href' => 'inventory.php', 'badge' => $badge_counts['inventory']],
            ['icon' => 'directions_bike', 'label' => 'Riders', 'href' => 'riders.php'],
            ['icon' => 'gavel', 'label' => 'Appeals', 'href' => 'appeals.php'],
            ['divider' => true],
            ['icon' => 'analytics', 'label' => 'Analytics', 'href' => 'analytics.php'],
            ['icon' => 'history', 'label' => 'Session Logs', 'href' => 'session_logs.php'],
            ['icon' => 'settings', 'label' => 'System Settings', 'href' => 'system_settings.php'],
            ['icon' => 'account_circle', 'label' => 'Profile Settings', 'href' => 'settings.php'],
            ['icon' => 'logout', 'label' => 'Logout', 'href' => '../api/auth/logout.php']
        ];
        break;
}
?>

<!-- Sidebar Overlay (mobile backdrop) -->
<div class="sidebar-overlay"></div>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../<?php echo htmlspecialchars($station_logo); ?>" alt="Station Logo">
        </div>
        <div class="sidebar-title"><?php echo htmlspecialchars($station_name); ?></div>
    </div>
    
    <nav class="sidebar-nav">
        <?php foreach ($menu_items as $item): ?>
            <?php if (isset($item['divider']) && $item['divider']): ?>
                <div class="sidebar-divider"></div>
            <?php else: ?>
                <a href="<?php echo $item['href']; ?>" class="sidebar-item">
                    <span class="material-icons"><?php echo $item['icon']; ?></span>
                    <span><?php echo $item['label']; ?></span>
                    <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                        <span class="sidebar-badge"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>
