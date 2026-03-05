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
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="css/<?php echo $page_css; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Hamburger Toggle (Mobile) -->
    <button class="hamburger-toggle">
        <span class="material-icons">menu</span>
    </button>
    
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-left">
            <button class="collapse-btn">
                <span class="material-icons">menu</span>
            </button>
            
            <div class="header-time">
                <span class="material-icons">schedule</span>
                <span id="manila-time">--:--:--</span>
            </div>
        </div>
        
        <div class="header-right">
            <!-- Theme Toggle -->
            <button class="theme-toggle" title="Toggle Theme">
                <span class="material-icons">dark_mode</span>
            </button>
            
            <!-- Notification Bell -->
            <div style="position: relative;">
                <button class="notif-bell">
                    <span class="material-icons">notifications</span>
                    <span class="notif-badge" style="display: none;">0</span>
                </button>
                
                <div class="notif-dropdown">
                    <div class="notif-header">
                        <h4>Notifications</h4>
                        <button class="notif-mark-read">Mark all read</button>
                    </div>
                    <!-- Notifications loaded via JavaScript -->
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
