<?php
/**
 * Azeu Water Station - Login Page
 */
session_start();

require_once 'config/constants.php';
require_once 'config/functions.php';
require_once 'config/logger.php';

// Log page access
logger_info("LOGIN PAGE ACCESSED", [
    'already_logged_in' => isset($_SESSION['user_id'])
]);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirects = [
        'customer' => 'customer/dashboard.php',
        'rider' => 'rider/dashboard.php',
        'staff' => 'staff/dashboard.php',
        'admin' => 'admin/dashboard.php',
        'super_admin' => 'admin/dashboard.php'
    ];
    
    $role = $_SESSION['role'] ?? 'customer';
    logger_info("Redirecting logged-in user", ['role' => $role]);
    header('Location: ' . ($redirects[$role] ?? 'index.php'));
    exit;
}

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(16)); ?>">
    <title>Login - <?php echo htmlspecialchars($station_name); ?></title>
    
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
                    <span class="material-icons">water_drop</span>
                </div>
                <h1 class="auth-title"><?php echo htmlspecialchars($station_name); ?></h1>
                <p class="auth-subtitle">Sign in to your account</p>
            </div>
            
            <form id="login-form" class="auth-form">
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="username" class="float-input" placeholder="Username" required autocomplete="username">
                        <label for="username" class="float-label">Username</label>
                        <span class="material-icons input-icon">person</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="password" id="password" class="float-input" placeholder="Password" required autocomplete="current-password">
                        <label for="password" class="float-label">Password</label>
                        <span class="material-icons input-icon">lock</span>
                        <button type="button" class="password-toggle">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <span class="material-icons">login</span>
                    Sign In
                </button>
            </form>
            
            <div class="auth-links">
                <a href="forgot_password.php" class="auth-link">Forgot Password?</a>
                <div class="auth-divider">or</div>
                <a href="register.php" class="auth-link">Don't have an account? Register</a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/global.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
