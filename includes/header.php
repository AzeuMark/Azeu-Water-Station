<?php
/**
 * Azeu Water Station - Header Include
 * Navigation header with station name, time, notifications, theme toggle
 */

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

$current_user = get_logged_in_user();
$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$time_region = get_setting('time_region') ?? 'Asia/Manila';

// Timezone → country code mapping for flag images
$timezone_country_codes = [
    'Asia/Manila' => 'ph',
    'Asia/Tokyo' => 'jp',
    'Asia/Seoul' => 'kr',
    'Asia/Shanghai' => 'cn',
    'Asia/Singapore' => 'sg',
    'Asia/Kolkata' => 'in',
    'Asia/Dubai' => 'ae',
    'Europe/London' => 'gb',
    'Europe/Paris' => 'fr',
    'Europe/Berlin' => 'de',
    'America/New_York' => 'us',
    'America/Chicago' => 'us',
    'America/Denver' => 'us',
    'America/Los_Angeles' => 'us',
    'Australia/Sydney' => 'au',
    'Pacific/Auckland' => 'nz',
];
$time_region_cc = $timezone_country_codes[$time_region] ?? 'ph';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - <?php echo htmlspecialchars($station_name); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="../assets/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/components.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/layout.css?v=<?php echo time(); ?>">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="css/<?php echo $page_css; ?>?v=<?php echo time(); ?>">
    <?php endif; ?>
    
    <?php if (get_setting('force_dark_mode') == '1'): ?>
        <meta name="force-dark-mode" content="1">
    <?php endif; ?>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-left">
            <!-- Sidebar Toggle — animated hamburger, used on all views -->
            <button class="hamburger-toggle" aria-label="Toggle sidebar">
                <div class="hamburger-bars">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            
            <div class="header-time">
                <img class="header-time-flag" id="header-time-flag" src="https://flagcdn.com/w40/<?php echo $time_region_cc; ?>.png" alt="<?php echo strtoupper($time_region_cc); ?>" title="<?php echo htmlspecialchars($time_region); ?>">
                <span class="material-icons">schedule</span>
                <span id="manila-time" data-timezone="<?php echo htmlspecialchars($time_region); ?>">--:--:--</span>
            </div>
        </div>
        
        <div class="header-right">
            <!-- Theme Toggle -->
            <button class="theme-toggle" title="Toggle Theme">
                <span class="material-icons">dark_mode</span>
            </button>
            
            <!-- Notification Bell -->
            <div class="notif-wrapper">
                <button class="notif-bell" aria-label="Notifications">
                    <span class="material-icons">notifications</span>
                    <span class="notif-badge" style="display: none;">0</span>
                </button>
                
                <div class="notif-dropdown">
                    <div class="notif-header">
                        <div class="notif-header-left">
                            <h4>Notifications</h4>
                            <span class="notif-unread-count" style="display: none;">0 unread</span>
                        </div>
                        <button class="notif-mark-read" title="Mark all as read">
                            <span class="material-icons">done_all</span>
                            <span>Mark all read</span>
                        </button>
                    </div>
                    <div class="notif-list">
                        <!-- Notifications loaded via JavaScript -->
                    </div>
                    <div class="notif-empty" style="display: none;">
                        <div class="notif-empty-icon">
                            <span class="material-icons">notifications_none</span>
                        </div>
                        <p class="notif-empty-title">All caught up!</p>
                        <p class="notif-empty-desc">No notifications right now.</p>
                    </div>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="user-menu">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(get_role_display_name($current_user['role'])); ?></div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="app-wrapper">
