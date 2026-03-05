<?php
/**
 * Azeu Water Station - Customer Registration Page
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

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
    <title>Register - <?php echo htmlspecialchars($station_name); ?></title>
    
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
                    <span class="material-icons">person_add</span>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Register as a new customer</p>
            </div>
            
            <form id="register-form" class="auth-form">
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="full_name" class="float-input" placeholder="Full Name" required>
                        <label for="full_name" class="float-label">Full Name</label>
                        <span class="material-icons input-icon">badge</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="text" id="username" class="float-input" placeholder="Username" required autocomplete="username">
                        <label for="username" class="float-label">Username</label>
                        <span class="material-icons input-icon">person</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="email" id="email" class="float-input" placeholder="Email" required autocomplete="email">
                        <label for="email" class="float-label">Email</label>
                        <span class="material-icons input-icon">email</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="tel" id="phone" class="float-input" placeholder="Phone Number" required autocomplete="tel">
                        <label for="phone" class="float-label">Phone Number</label>
                        <span class="material-icons input-icon">phone</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="float-input-group">
                        <input type="password" id="password" class="float-input" placeholder="Password" required autocomplete="new-password">
                        <label for="password" class="float-label">Password</label>
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
                    <span class="material-icons">person_add</span>
                    Register
                </button>
            </form>
            
            <div class="auth-links">
                <a href="index.php" class="auth-link">Already have an account? Sign In</a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/global.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
