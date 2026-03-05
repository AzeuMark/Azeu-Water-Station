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

// Define menu items per role
$menu_items = [];

switch ($role) {
    case ROLE_CUSTOMER:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'add_shopping_cart', 'label' => 'Place Order', 'href' => 'place_order.php'],
            ['icon' => 'shopping_bag', 'label' => 'My Orders', 'href' => 'orders.php'],
            ['icon' => 'location_on', 'label' => 'Addresses', 'href' => 'addresses.php'],
            ['divider' => true],
            ['icon' => 'settings', 'label' => 'Settings', 'href' => 'settings.php'],
            ['icon' => 'logout', 'label' => 'Logout', 'href' => '../api/auth/logout.php']
        ];
        break;
        
    case ROLE_RIDER:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'local_shipping', 'label' => 'My Deliveries', 'href' => 'deliveries.php'],
            ['icon' => 'swap_vert', 'label' => 'Assigned Deliveries', 'href' => 'assigned_deliveries.php'],
            ['icon' => 'history', 'label' => 'Delivery History', 'href' => 'delivery_history.php'],
            ['divider' => true],
            ['icon' => 'settings', 'label' => 'Settings', 'href' => 'settings.php'],
            ['icon' => 'logout', 'label' => 'Logout', 'href' => '../api/auth/logout.php']
        ];
        break;
        
    case ROLE_STAFF:
        $menu_items = [
            ['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => 'receipt_long', 'label' => 'Orders', 'href' => 'orders.php'],
            ['icon' => 'people', 'label' => 'Accounts', 'href' => 'accounts.php'],
            ['icon' => 'pending_actions', 'label' => 'Pending Accounts', 'href' => 'pending_accounts.php'],
            ['icon' => 'inventory_2', 'label' => 'Inventory', 'href' => 'inventory.php'],
            ['icon' => 'directions_bike', 'label' => 'Riders', 'href' => 'riders.php'],
            ['icon' => 'bar_chart', 'label' => 'Rider Statistics', 'href' => 'rider_statistics.php'],
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
            ['icon' => 'receipt_long', 'label' => 'Orders', 'href' => 'orders.php'],
            ['icon' => 'people', 'label' => 'Accounts', 'href' => 'accounts.php'],
            ['icon' => 'pending_actions', 'label' => 'Pending Accounts', 'href' => 'pending_accounts.php'],
            ['icon' => 'inventory_2', 'label' => 'Inventory', 'href' => 'inventory.php'],
            ['icon' => 'directions_bike', 'label' => 'Riders', 'href' => 'riders.php'],
            ['icon' => 'bar_chart', 'label' => 'Rider Statistics', 'href' => 'rider_statistics.php'],
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

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="material-icons">water_drop</span>
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
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>
