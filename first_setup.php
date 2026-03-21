<?php
/**
 * ============================================================================
 * AZEU WATER STATION — FIRST TIME SETUP
 * ============================================================================
 *
 * Creates the database with essential tables and sets up:
 *   - 1 Super Admin account
 *   - 1 Admin account
 *
 * For initial deployment only. Credentials are editable before setup.
 * ============================================================================
 */

// ── Bootstrap ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/AESCrypt.php';

date_default_timezone_set('Asia/Manila');

// ── Feedback collector ───────────────────────────────────────────────────────
$messages = [];
$hasError = false;
$executed = false;
$setupComplete = false;

function msg($text, $type = 'info')
{
    global $messages;
    $messages[] = ['text' => $text, 'type' => $type];
}

// ── Default Credentials (editable via form) ──────────────────────────────────
$defaultAccounts = [
    'super_admin' => [
        'username'  => 'superadmin',
        'password'  => 'admin',
        'full_name' => 'Super Admin',
        'email'     => 'superadmin@azeumark.com',
        'phone'     => '09123456789',
    ],
    'admin' => [
        'username'  => 'admin',
        'password'  => 'admin',
        'full_name' => 'Main Admin',
        'email'     => 'admin@azeumark.com',
        'phone'     => '09123456789',
    ],
];

// ── Default System Settings ──────────────────────────────────────────────────
$defaultSettings = [
    'station_name'          => 'Nexeu Water Station',
    'station_address'       => 'Purok-4 Lapinig, Kapatagan, Lanao del Norte',
    'max_cancellation'      => '5',
    'pending_expiry_days'   => '7',
    'low_stock_threshold'   => '10',
    'maintenance_mode'      => '0',
    'encrypt_passwords'     => '1',
    'auto_assign_orders'    => '0',
    'timezone'              => 'Asia/Manila',
    'force_dark_mode'       => '0',
    'primary_color'         => '#1565C0',
    'secondary_color'       => '#1E88E5',
    'accent_color'          => '#42A5F5',
    'surface_color'         => '#F5F7FA',
    'max_login_attempts'    => '10',
    'delivery_fee'          => '50.00',
    'login_lockout_minutes' => '15',
];

// ── Process POST ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_db'])) {
    $executed = true;

    try {
        // ── Parse submitted accounts ─────────────────────────────────────────
        $superAdmin = [
            'username'  => trim($_POST['super_admin_username'] ?? 'superadmin'),
            'password'  => trim($_POST['super_admin_password'] ?? 'admin123'),
            'full_name' => trim($_POST['super_admin_fullname'] ?? 'System Administrator'),
            'email'     => trim($_POST['super_admin_email'] ?? 'superadmin@azeu.com'),
            'phone'     => trim($_POST['super_admin_phone'] ?? '09170000001'),
        ];

        $admin = [
            'username'  => trim($_POST['admin_username'] ?? 'admin'),
            'password'  => trim($_POST['admin_password'] ?? 'admin123'),
            'full_name' => trim($_POST['admin_fullname'] ?? 'Branch Manager'),
            'email'     => trim($_POST['admin_email'] ?? 'admin@azeu.com'),
            'phone'     => trim($_POST['admin_phone'] ?? '09170000002'),
        ];

        // ── Validation ───────────────────────────────────────────────────────
        if (empty($superAdmin['username']) || empty($superAdmin['password'])) {
            throw new Exception('Super Admin username and password are required.');
        }
        if (empty($admin['username']) || empty($admin['password'])) {
            throw new Exception('Admin username and password are required.');
        }
        if ($superAdmin['username'] === $admin['username']) {
            throw new Exception('Super Admin and Admin must have different usernames.');
        }

        // ── Connect ──────────────────────────────────────────────────────────
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]
        );

        // ──────────────────────────────────────────────────────────────────────
        // 1. CREATE DATABASE (if not exists)
        // ──────────────────────────────────────────────────────────────────────
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");
        msg("Database '" . DB_NAME . "' ready.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 2. CREATE ALL 13 TABLES (if not exist)
        // ──────────────────────────────────────────────────────────────────────

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            role ENUM('customer','rider','staff','admin','super_admin') NOT NULL,
            status ENUM('pending','active','flagged','deleted') NOT NULL DEFAULT 'pending',
            is_available TINYINT(1) NOT NULL DEFAULT 1,
            cancellation_count INT(11) NOT NULL DEFAULT 0,
            cancellation_reset_date DATE NULL,
            login_attempts INT(11) NOT NULL DEFAULT 0,
            login_locked_until DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            INDEX idx_username (username),
            INDEX idx_role (role),
            INDEX idx_status (status)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS user_preferences (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) UNIQUE NOT NULL,
            dark_mode TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            CONSTRAINT fk_user_pref FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS customer_addresses (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            customer_id INT(11) NOT NULL,
            label VARCHAR(50) NOT NULL DEFAULT 'Home',
            full_address TEXT NOT NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            CONSTRAINT fk_addr_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            item_name VARCHAR(100) NOT NULL,
            item_icon VARCHAR(255) NULL DEFAULT NULL,
            stock_count INT(11) NOT NULL DEFAULT 0,
            price DECIMAL(10,2) NOT NULL,
            status ENUM('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
            last_restocked_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            customer_id INT(11) NOT NULL,
            rider_id INT(11) NULL,
            payment_type ENUM('cod','pickup','online') NOT NULL,
            delivery_type ENUM('delivery','pickup') NOT NULL,
            status ENUM('pending','confirmed','assigned','on_delivery','delivered','accepted','ready_for_pickup','picked_up','cancelled') NOT NULL DEFAULT 'pending',
            delivery_address TEXT NULL,
            order_notes TEXT NULL,
            delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            subtotal DECIMAL(10,2) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            expected_delivery_date DATE NULL,
            cancellation_reason TEXT NULL,
            cancelled_by INT(11) NULL,
            staff_comment TEXT NULL,
            customer_confirmed TINYINT(1) NOT NULL DEFAULT 0,
            customer_confirmed_at DATETIME NULL,
            receipt_token VARCHAR(64) UNIQUE NULL,
            order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            delivered_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            INDEX idx_rider (rider_id),
            INDEX idx_status (status),
            INDEX idx_order_date (order_date),
            CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES users(id),
            CONSTRAINT fk_order_rider FOREIGN KEY (rider_id) REFERENCES users(id),
            CONSTRAINT fk_order_cancelled FOREIGN KEY (cancelled_by) REFERENCES users(id)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            order_id INT(11) NOT NULL,
            inventory_id INT(11) NOT NULL,
            item_name VARCHAR(100) NOT NULL,
            item_icon VARCHAR(255) NULL,
            item_price DECIMAL(10,2) NOT NULL,
            quantity INT(11) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_inventory (inventory_id),
            CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT fk_item_inventory FOREIGN KEY (inventory_id) REFERENCES inventory(id)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            title VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            reference_id INT(11) NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_read (is_read),
            CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS session_logs (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) NULL,
            username VARCHAR(50) NOT NULL,
            role VARCHAR(20) NOT NULL,
            action ENUM('login','logout','failed_login') NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_created (created_at),
            CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT NOT NULL,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS default_items (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            item_name VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS cancellation_appeals (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            customer_id INT(11) NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('pending','approved','denied') NOT NULL DEFAULT 'pending',
            reviewed_by INT(11) NULL,
            admin_notes TEXT NULL,
            reviewed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            INDEX idx_reviewer (reviewed_by),
            INDEX idx_status (status),
            CONSTRAINT fk_appeal_customer FOREIGN KEY (customer_id) REFERENCES users(id),
            CONSTRAINT fk_appeal_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS delivery_priority (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            rider_id INT(11) NOT NULL,
            order_id INT(11) NOT NULL,
            priority_order INT(11) NOT NULL DEFAULT 0,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_rider (rider_id),
            INDEX idx_order (order_id),
            CONSTRAINT fk_priority_rider FOREIGN KEY (rider_id) REFERENCES users(id),
            CONSTRAINT fk_priority_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ) ENGINE=InnoDB");

        msg("All 13 tables created/verified.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 3. SEED SETTINGS (if empty)
        // ──────────────────────────────────────────────────────────────────────
        $settingsCount = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
        if ($settingsCount == 0) {
            $settingsStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaultSettings as $k => $v) {
                $settingsStmt->execute([$k, $v]);
            }
            msg("Seeded " . count($defaultSettings) . " system settings.", 'success');
        } else {
            msg("System settings already exist, skipped.", 'info');
        }

        // ──────────────────────────────────────────────────────────────────────
        // 4. CHECK IF ADMIN ACCOUNTS ALREADY EXIST
        // ──────────────────────────────────────────────────────────────────────
        $existingUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('super_admin', 'admin')")->fetchColumn();
        if ($existingUsers > 0) {
            throw new Exception('Admin accounts already exist in the database. This setup is meant for first-time use only.');
        }

        // ──────────────────────────────────────────────────────────────────────
        // 5. CREATE ADMIN ACCOUNTS
        // ──────────────────────────────────────────────────────────────────────
        $userStmt = $pdo->prepare(
            "INSERT INTO users (username, password, full_name, email, phone, role, status, is_available)
             VALUES (?, ?, ?, ?, ?, ?, 'active', 1)"
        );

        // Super Admin
        $encryptedPwd1 = encrypt($superAdmin['password'], ENCRYPTION_KEY);
        $userStmt->execute([
            $superAdmin['username'],
            $encryptedPwd1,
            $superAdmin['full_name'],
            $superAdmin['email'],
            $superAdmin['phone'],
            'super_admin'
        ]);
        $superAdminId = (int)$pdo->lastInsertId();
        msg("Created Super Admin: {$superAdmin['username']}", 'success');

        // Admin
        $encryptedPwd2 = encrypt($admin['password'], ENCRYPTION_KEY);
        $userStmt->execute([
            $admin['username'],
            $encryptedPwd2,
            $admin['full_name'],
            $admin['email'],
            $admin['phone'],
            'admin'
        ]);
        $adminId = (int)$pdo->lastInsertId();
        msg("Created Admin: {$admin['username']}", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 6. CREATE USER PREFERENCES
        // ──────────────────────────────────────────────────────────────────────
        $prefStmt = $pdo->prepare("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, 0)");
        $prefStmt->execute([$superAdminId]);
        $prefStmt->execute([$adminId]);
        msg("Created user preferences for both accounts.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 7. SEED DEFAULT ITEM NAMES (if empty)
        // ──────────────────────────────────────────────────────────────────────
        $defaultItemsCount = $pdo->query("SELECT COUNT(*) FROM default_items")->fetchColumn();
        if ($defaultItemsCount == 0) {
            $defaultItemNames = [
                '30L Water Refill', '20L Water Refill', '10L Water Refill',
                '5L Water Refill', '1L Bottled Water', '500ml Bottled Water',
                'Bleach 1L', 'Water Dispenser', 'Water Container 30L', 'Water Container 20L'
            ];
            $diStmt = $pdo->prepare("INSERT INTO default_items (item_name) VALUES (?)");
            foreach ($defaultItemNames as $n) {
                $diStmt->execute([$n]);
            }
            msg("Seeded " . count($defaultItemNames) . " default item names.", 'success');
        }

        // ──────────────────────────────────────────────────────────────────────
        // DONE
        // ──────────────────────────────────────────────────────────────────────
        $setupComplete = true;
        msg("Setup complete! You can now log in with your admin credentials.", 'success');

    } catch (PDOException $e) {
        $hasError = true;
        msg("DATABASE ERROR: " . $e->getMessage(), 'error');
    } catch (Exception $e) {
        $hasError = true;
        msg("ERROR: " . $e->getMessage(), 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First Time Setup — Azeu Water Station</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0D0D1A 0%, #1A1A2E 50%, #0D0D1A 100%);
            color: #E0E0F0;
            min-height: 100vh;
            padding: 40px 16px 60px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container { max-width: 600px; width: 100%; }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header .material-icons { font-size: 64px; color: #42A5F5; }
        .header h1 { font-size: 1.8em; margin: 12px 0 6px; }
        .header .sub { color: #8888AA; font-size: 0.95em; }

        /* ── Card ── */
        .card {
            background: rgba(25, 25, 45, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid #2A2A42;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background: rgba(30, 30, 55, 0.8);
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #2A2A42;
        }
        .card-header .material-icons { font-size: 26px; }
        .card-header h2 { font-size: 1.1em; font-weight: 600; }
        .card-header.super-admin .material-icons { color: #EF5350; }
        .card-header.admin .material-icons { color: #FF7043; }
        .card-body { padding: 24px; }

        /* ── Form Fields ── */
        .field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .field-grid .full-width { grid-column: 1 / -1; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field label {
            font-size: 0.75em;
            font-weight: 600;
            color: #7777AA;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field input {
            background: rgba(15, 15, 30, 0.8);
            border: 1px solid #2A2A42;
            color: #E0E0F0;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.95em;
            font-family: inherit;
            transition: all 0.2s;
            width: 100%;
        }
        .field input:focus {
            outline: none;
            border-color: #42A5F5;
            box-shadow: 0 0 0 3px rgba(66,165,245,0.15);
        }
        .field input::placeholder { color: #555; }
        .field .hint {
            font-size: 0.72em;
            color: #666;
            margin-top: 2px;
        }

        /* ── Password Toggle ── */
        .password-field {
            position: relative;
        }
        .password-field input {
            padding-right: 44px;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }
        .password-toggle:hover { color: #42A5F5; }
        .password-toggle .material-icons { font-size: 20px; }

        /* ── Submit Button ── */
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #1565C0, #1E88E5);
            color: #fff;
            font-size: 1.1em;
            font-weight: 700;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            letter-spacing: 0.3px;
            margin-top: 8px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #0D47A1, #1565C0);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(21,101,192,0.45);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit .material-icons { font-size: 24px; }

        /* ── Messages ── */
        .feedback { margin-top: 24px; }
        .msg {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 8px;
            font-size: 0.9em;
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .msg .material-icons { font-size: 20px; flex-shrink: 0; }
        .msg-success { background: rgba(102,187,106,0.1); border: 1px solid rgba(102,187,106,0.2); color: #A5D6A7; }
        .msg-success .material-icons { color: #66BB6A; }
        .msg-info    { background: rgba(41,182,246,0.08); border: 1px solid rgba(41,182,246,0.18); color: #90CAF9; }
        .msg-info .material-icons { color: #29B6F6; }
        .msg-warning { background: rgba(255,167,38,0.1); border: 1px solid rgba(255,167,38,0.2); color: #FFE0B2; }
        .msg-warning .material-icons { color: #FFA726; }
        .msg-error   { background: rgba(239,83,80,0.12); border: 1px solid rgba(239,83,80,0.25); color: #EF9A9A; }
        .msg-error .material-icons { color: #EF5350; }

        /* ── Success Box ── */
        .success-box {
            background: rgba(102,187,106,0.08);
            border: 2px solid rgba(102,187,106,0.3);
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            margin-top: 24px;
        }
        .success-box .material-icons { font-size: 56px; color: #66BB6A; }
        .success-box h3 { color: #A5D6A7; margin: 12px 0 8px; font-size: 1.3em; }
        .success-box p { color: #8888AA; font-size: 0.95em; line-height: 1.5; }
        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            background: linear-gradient(135deg, #66BB6A, #4CAF50);
            color: #fff;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102,187,106,0.4);
        }
        .btn-login .material-icons { font-size: 20px; }

        /* ── Info Box ── */
        .info-box {
            background: rgba(66,165,245,0.08);
            border: 1px solid rgba(66,165,245,0.2);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .info-box .material-icons { color: #42A5F5; font-size: 24px; flex-shrink: 0; margin-top: 2px; }
        .info-box p { color: #90CAF9; font-size: 0.88em; line-height: 1.5; }

        /* ── Footer Link ── */
        .footer-link {
            text-align: center;
            margin-top: 24px;
        }
        .footer-link a { color: #42A5F5; text-decoration: none; font-size: 0.9em; }
        .footer-link a:hover { text-decoration: underline; }

        /* ── Responsive ── */
        @media (max-width: 500px) {
            .field-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <span class="material-icons">water_drop</span>
        <h1>First Time Setup</h1>
        <p class="sub">Azeu Water Station</p>
    </div>

    <?php if ($setupComplete): ?>
    <!-- Success State -->
    <div class="success-box">
        <span class="material-icons">check_circle</span>
        <h3>Setup Complete!</h3>
        <p>Your database has been created and admin accounts are ready.<br>You can now log in to start managing your water station.</p>
        <a href="index.php" class="btn-login">
            <span class="material-icons">login</span>
            Go to Login
        </a>
    </div>

    <?php if (!empty($messages)): ?>
    <div class="card">
        <div class="card-header">
            <span class="material-icons" style="color:#66BB6A;">terminal</span>
            <h2>Setup Log</h2>
        </div>
        <div class="card-body feedback">
            <?php foreach ($messages as $m): ?>
                <?php
                    $iconMap = ['success' => 'check_circle', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'];
                    $icon = $iconMap[$m['type']] ?? 'info';
                ?>
                <div class="msg msg-<?= $m['type'] ?>">
                    <span class="material-icons"><?= $icon ?></span>
                    <?= htmlspecialchars($m['text']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Setup Form -->
    <form method="POST" id="setupForm">

        <div class="info-box">
            <span class="material-icons">info</span>
            <p>This will create your database and set up admin accounts. Edit the credentials below before running setup. Make sure to remember your passwords.</p>
        </div>

        <!-- Super Admin Card -->
        <div class="card">
            <div class="card-header super-admin">
                <span class="material-icons">shield</span>
                <h2>Super Admin Account</h2>
            </div>
            <div class="card-body">
                <div class="field-grid">
                    <div class="field">
                        <label>Username</label>
                        <input type="text" name="super_admin_username"
                               value="<?= htmlspecialchars($defaultAccounts['super_admin']['username']) ?>"
                               placeholder="superadmin" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <div class="password-field">
                            <input type="password" name="super_admin_password" id="pwd1"
                                   value="<?= htmlspecialchars($defaultAccounts['super_admin']['password']) ?>"
                                   placeholder="Enter password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('pwd1', this)">
                                <span class="material-icons">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="field full-width">
                        <label>Full Name</label>
                        <input type="text" name="super_admin_fullname"
                               value="<?= htmlspecialchars($defaultAccounts['super_admin']['full_name']) ?>"
                               placeholder="System Administrator">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="super_admin_email"
                               value="<?= htmlspecialchars($defaultAccounts['super_admin']['email']) ?>"
                               placeholder="email@example.com">
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="super_admin_phone"
                               value="<?= htmlspecialchars($defaultAccounts['super_admin']['phone']) ?>"
                               placeholder="09XX-XXX-XXXX">
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Card -->
        <div class="card">
            <div class="card-header admin">
                <span class="material-icons">admin_panel_settings</span>
                <h2>Admin Account</h2>
            </div>
            <div class="card-body">
                <div class="field-grid">
                    <div class="field">
                        <label>Username</label>
                        <input type="text" name="admin_username"
                               value="<?= htmlspecialchars($defaultAccounts['admin']['username']) ?>"
                               placeholder="admin" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <div class="password-field">
                            <input type="password" name="admin_password" id="pwd2"
                                   value="<?= htmlspecialchars($defaultAccounts['admin']['password']) ?>"
                                   placeholder="Enter password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('pwd2', this)">
                                <span class="material-icons">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="field full-width">
                        <label>Full Name</label>
                        <input type="text" name="admin_fullname"
                               value="<?= htmlspecialchars($defaultAccounts['admin']['full_name']) ?>"
                               placeholder="Branch Manager">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="admin_email"
                               value="<?= htmlspecialchars($defaultAccounts['admin']['email']) ?>"
                               placeholder="email@example.com">
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="admin_phone"
                               value="<?= htmlspecialchars($defaultAccounts['admin']['phone']) ?>"
                               placeholder="09XX-XXX-XXXX">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" name="setup_db" class="btn-submit"
                onclick="return confirm('This will create the database and admin accounts.\n\nContinue with setup?');">
            <span class="material-icons">rocket_launch</span>
            RUN FIRST TIME SETUP
        </button>

    </form>

    <?php if (!empty($messages)): ?>
    <div class="card" style="margin-top:24px;">
        <div class="card-header">
            <span class="material-icons" style="color:<?= $hasError ? '#EF5350' : '#42A5F5' ?>;">
                <?= $hasError ? 'error' : 'terminal' ?>
            </span>
            <h2><?= $hasError ? 'Setup Failed' : 'Setup Log' ?></h2>
        </div>
        <div class="card-body feedback">
            <?php foreach ($messages as $m): ?>
                <?php
                    $iconMap = ['success' => 'check_circle', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'];
                    $icon = $iconMap[$m['type']] ?? 'info';
                ?>
                <div class="msg msg-<?= $m['type'] ?>">
                    <span class="material-icons"><?= $icon ?></span>
                    <?= htmlspecialchars($m['text']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="footer-link">
        <a href="index.php">&larr; Back to Login</a>
    </div>
    <?php endif; ?>

</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('.material-icons');
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>
</body>
</html>
