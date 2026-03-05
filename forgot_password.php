<?php
/**
 * Azeu Water Station - Forgot Password Page
 */
session_start();

require_once 'config/constants.php';
require_once 'config/functions.php';

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
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
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <span class="material-icons">lock_reset</span>
                </div>
                <h1 class="auth-title">Forgot Password</h1>
                <p class="auth-subtitle">Enter your email to reset your password</p>
            </div>
            
            <form id="forgot-password-form" class="auth-form">
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="email" id="email" class="float-input" placeholder="Email" required autocomplete="email">
                        <label for="email" class="float-label">Email Address</label>
                        <span class="material-icons input-icon">email</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <span class="material-icons">send</span>
                    Send Reset Link
                </button>
            </form>
            
            <div class="auth-links">
                <a href="index.php" class="auth-link">
                    <span class="material-icons" style="font-size: 18px; vertical-align: middle;">arrow_back</span>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/global.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
