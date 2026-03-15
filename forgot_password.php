<?php
/**
 * Azeu Water Station - Forgot Password Page
 */
session_start();

require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'config/functions.php';

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$station_logo = get_setting('station_logo') ?? 'images/system/logo-1.png';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(16)); ?>">
    <title>Forgot Password - <?php echo htmlspecialchars($station_name); ?></title>
    
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
        <div class="auth-orb auth-orb--1"></div>
        <div class="auth-orb auth-orb--2"></div>
        <div class="auth-orb auth-orb--3"></div>

        <div class="auth-container">
            <div class="auth-top-bar">
                <div class="auth-logo-bar">
                    <div class="auth-logo-icon">
                        <img src="<?php echo htmlspecialchars($station_logo); ?>" alt="Logo">
                    </div>
                    <span class="auth-logo-name"><?php echo htmlspecialchars($station_name); ?></span>
                </div>
                <button type="button" class="theme-toggle" title="Toggle theme">
                    <span class="material-icons">dark_mode</span>
                </button>
            </div>

            <div class="auth-card">
                <div class="auth-panel">
                    <div class="auth-header">
                        <h1 class="auth-title">Reset your password</h1>
                        <p class="auth-subtitle">Enter the email linked to your account and we'll send a reset link</p>
                    </div>

                    <form id="forgot-password-form" class="auth-form">
                        <div class="form-group">
                            <div class="float-input-group">
                                <input type="email" id="email" class="float-input" placeholder="Email" required autocomplete="email">
                                <label for="email" class="float-label">Email Address</label>
                                <span class="material-icons input-icon">mail_outline</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Send Reset Link</button>
                    </form>

                    <div class="auth-links">
                        <a href="login.php" class="auth-link">
                            <span class="material-icons" style="font-size: 16px;">arrow_back</span>
                            Back to Sign In
                        </a>
                    </div>
                </div>
            </div>

            <p class="auth-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($station_name); ?></p>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/global.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
