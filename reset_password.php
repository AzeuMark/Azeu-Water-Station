<?php
/**
 * Azeu Water Station - Reset Password Page
 */
session_start();

require_once 'config/constants.php';
require_once 'config/functions.php';

$station_name = get_setting('station_name') ?? 'Azeu Water Station';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: index.php');
    exit;
}

$token = $_GET['token'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(16)); ?>">
    <title>Reset Password - <?php echo htmlspecialchars($station_name); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <span class="material-icons">vpn_key</span>
                </div>
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Enter your new password</p>
            </div>
            
            <form id="reset-password-form" class="auth-form">
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="password" id="password" class="float-input" placeholder="New Password" required autocomplete="new-password">
                        <label for="password" class="float-label">New Password</label>
                        <span class="material-icons input-icon">lock</span>
                        <button type="button" class="password-toggle">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="password" id="confirm_password" class="float-input" placeholder="Confirm Password" required autocomplete="new-password">
                        <label for="confirm_password" class="float-label">Confirm Password</label>
                        <span class="material-icons input-icon">lock</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <span class="material-icons">check_circle</span>
                    Reset Password
                </button>
            </form>
            
            <div class="auth-links">
                <a href="index.php" class="auth-link">Back to Login</a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/global.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
