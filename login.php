<?php
/**
 * Azeu Water Station - Login & Register Page (Combined)
 */
session_start();

require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'config/logger.php';

logger_info("AUTH PAGE ACCESSED", [
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
    header('Location: ' . ($redirects[$role] ?? 'login.php'));
    exit;
}

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$station_logo = get_setting('station_logo') ?? 'images/system/logo-1.png';
$start_mode = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(16)); ?>">
    <title><?php echo $start_mode === 'register' ? 'Register' : 'Login'; ?> - <?php echo htmlspecialchars($station_name); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-orb auth-orb--1"></div>
        <div class="auth-orb auth-orb--2"></div>
        <div class="auth-orb auth-orb--3"></div>

        <div class="auth-container">
            <!-- Top bar: logo + theme -->
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

            <!-- Card -->
            <div class="auth-card">
                <!-- Tabs -->
                <div class="auth-tabs">
                    <button type="button" class="auth-tab<?php echo $start_mode === 'login' ? ' active' : ''; ?>" data-tab="login">Sign In</button>
                    <button type="button" class="auth-tab<?php echo $start_mode === 'register' ? ' active' : ''; ?>" data-tab="register">Register</button>
                    <div class="auth-tab-indicator"></div>
                </div>

                <!-- Panels viewport -->
                <div class="auth-panels-viewport">
                    <div class="auth-panels-track<?php echo $start_mode === 'register' ? ' show-register' : ''; ?>">

                        <!-- ===== LOGIN PANEL ===== -->
                        <div class="auth-panel" id="login-panel">
                            <div class="auth-header">
                                <h1 class="auth-title">Welcome back</h1>
                                <p class="auth-subtitle">Sign in to your account to continue</p>
                            </div>

                            <form id="login-form" class="auth-form">
                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="text" id="username" class="float-input" placeholder="Username" required autocomplete="username">
                                        <label for="username" class="float-label">Username</label>
                                        <span class="material-icons input-icon">person_outline</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="password" id="password" class="float-input" placeholder="Password" required autocomplete="current-password">
                                        <label for="password" class="float-label">Password</label>
                                        <span class="material-icons input-icon">lock_outline</span>
                                        <button type="button" class="password-toggle" aria-label="Toggle password">
                                            <span class="material-icons">visibility</span>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn-submit">Sign In</button>
                            </form>

                            <div class="auth-links">
                                <a href="forgot_password.php" class="auth-link">Forgot your password?</a>
                            </div>
                        </div>

                        <!-- ===== REGISTER PANEL ===== -->
                        <div class="auth-panel" id="register-panel">
                            <div class="auth-header">
                                <h1 class="auth-title">Create account</h1>
                                <p class="auth-subtitle">Register as a new customer to get started</p>
                            </div>

                            <form id="register-form" class="auth-form">
                                <div class="form-row form-row-3">
                                    <div class="form-group">
                                        <div class="float-input-group">
                                            <input type="text" id="reg_first_name" class="float-input" placeholder="First Name" required>
                                            <label for="reg_first_name" class="float-label">First Name</label>
                                            <span class="material-icons input-icon">person_outline</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="float-input-group">
                                            <input type="text" id="reg_middle_initial" class="float-input" placeholder="M.I." maxlength="2">
                                            <label for="reg_middle_initial" class="float-label">M.I.</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="float-input-group">
                                            <input type="text" id="reg_last_name" class="float-input" placeholder="Last Name" required>
                                            <label for="reg_last_name" class="float-label">Last Name</label>
                                            <span class="material-icons input-icon">person_outline</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="text" id="reg_username" class="float-input" placeholder="Username" required autocomplete="username">
                                        <label for="reg_username" class="float-label">Username</label>
                                        <span class="material-icons input-icon">alternate_email</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="email" id="reg_email" class="float-input" placeholder="Email" required autocomplete="email">
                                        <label for="reg_email" class="float-label">Email</label>
                                        <span class="material-icons input-icon">mail_outline</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="tel" id="reg_phone" class="float-input" placeholder="Phone Number" required autocomplete="tel">
                                        <label for="reg_phone" class="float-label">Phone Number</label>
                                        <span class="material-icons input-icon">phone_outlined</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="text" id="reg_address" class="float-input" placeholder="Address" required autocomplete="street-address">
                                        <label for="reg_address" class="float-label">Address</label>
                                        <span class="material-icons input-icon">location_on</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="password" id="reg_password" class="float-input" placeholder="Password" required autocomplete="new-password">
                                        <label for="reg_password" class="float-label">Password</label>
                                        <span class="material-icons input-icon">lock_outline</span>
                                        <button type="button" class="password-toggle" aria-label="Toggle password">
                                            <span class="material-icons">visibility</span>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="password-strength">
                                        <div class="strength-bar-track">
                                            <div class="strength-bar-fill" id="strength-bar"></div>
                                        </div>
                                        <span class="strength-text" id="strength-text"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="float-input-group">
                                        <input type="password" id="reg_confirm_password" class="float-input" placeholder="Confirm Password" required autocomplete="new-password">
                                        <label for="reg_confirm_password" class="float-label">Confirm Password</label>
                                        <span class="material-icons input-icon">lock_outline</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn-submit">Create Account</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <p class="auth-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($station_name); ?></p>
        </div>
    </div>

    <script src="assets/js/global.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
