<?php
/**
 * ============================================================================
 * AZEU WATER STATION — DATABASE TEST SETUP SCRIPT
 * ============================================================================
 *
 * ⚠️  FOR TESTING PURPOSES ONLY
 *
 * This script will DROP the existing database and recreate it from scratch
 * with sample users, inventory items, and orders across various statuses.
 *
 * Default credentials:
 *   Super Admin  → admin   : admin
 *   All others   → username : 12345
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

function msg($text, $type = 'info') {
    global $messages;
    $messages[] = ['text' => $text, 'type' => $type];
}

// ── Run on POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
    $executed = true;

    try {
        // Connect WITHOUT selecting a database
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
        // 1. DROP & CREATE DATABASE
        // ──────────────────────────────────────────────────────────────────────
        $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
        msg("Dropped existing database '" . DB_NAME . "'.", 'warning');

        $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");
        msg("Created fresh database '" . DB_NAME . "'.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 2. CREATE ALL 13 TABLES
        // ──────────────────────────────────────────────────────────────────────

        // Table 1: users
        $pdo->exec("CREATE TABLE users (
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

        // Table 2: user_preferences
        $pdo->exec("CREATE TABLE user_preferences (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) UNIQUE NOT NULL,
            dark_mode TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            CONSTRAINT fk_user_pref FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // Table 3: customer_addresses
        $pdo->exec("CREATE TABLE customer_addresses (
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

        // Table 4: inventory
        $pdo->exec("CREATE TABLE inventory (
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

        // Table 5: orders
        $pdo->exec("CREATE TABLE orders (
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

        // Table 6: order_items
        $pdo->exec("CREATE TABLE order_items (
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

        // Table 7: notifications
        $pdo->exec("CREATE TABLE notifications (
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

        // Table 8: session_logs
        $pdo->exec("CREATE TABLE session_logs (
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

        // Table 9: settings
        $pdo->exec("CREATE TABLE settings (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT NOT NULL,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        // Table 10: default_items
        $pdo->exec("CREATE TABLE default_items (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            item_name VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        // Table 11: cancellation_appeals
        $pdo->exec("CREATE TABLE cancellation_appeals (
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

        // Table 12: password_resets
        $pdo->exec("CREATE TABLE password_resets (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email)
        ) ENGINE=InnoDB");

        // Table 13: delivery_priority
        $pdo->exec("CREATE TABLE delivery_priority (
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

        msg("All 13 tables created successfully.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 3. SEED SETTINGS
        // ──────────────────────────────────────────────────────────────────────
        $defaultSettings = [
            'station_name'          => 'Azeu Water Station',
            'station_address'       => '123 Main Street, Bagong Bayan, Manila, Philippines',
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

        $settingsStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaultSettings as $k => $v) {
            $settingsStmt->execute([$k, $v]);
        }
        msg("Seeded " . count($defaultSettings) . " system settings.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 4. SEED DEFAULT ITEM NAMES
        // ──────────────────────────────────────────────────────────────────────
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

        // ──────────────────────────────────────────────────────────────────────
        // 5. SEED USER ACCOUNTS
        // ──────────────────────────────────────────────────────────────────────
        // Passwords are encrypted with AES because encrypt_passwords = 1
        $adminPwd    = encrypt('admin', ENCRYPTION_KEY);
        $defaultPwd  = encrypt('12345', ENCRYPTION_KEY);

        $resetDate = date('Y-m-d', strtotime('first day of next month'));

        // (username, password, full_name, email, phone, role, status, is_available, cancellation_reset_date)
        $users = [
            // Super Admin (id 1)
            ['admin',     $adminPwd,   'System Administrator',  'admin@azeu.com',           '09170000001', 'super_admin', 'active',  1, null],
            // Customers (ids 2, 3)
            ['customer1', $defaultPwd, 'Maria Santos',          'maria.santos@gmail.com',   '09171000001', 'customer',    'active',  1, $resetDate],
            ['customer2', $defaultPwd, 'Jose Reyes',            'jose.reyes@gmail.com',     '09171000002', 'customer',    'active',  1, $resetDate],
            // Riders (ids 4, 5)
            ['rider1',    $defaultPwd, 'Carlo Mendoza',         'carlo.mendoza@gmail.com',  '09172000001', 'rider',       'active',  1, null],
            ['rider2',    $defaultPwd, 'Miguel Torres',         'miguel.torres@gmail.com',  '09172000002', 'rider',       'active',  1, null],
            // Staff (ids 6, 7)
            ['staff1',    $defaultPwd, 'Anna Cruz',             'anna.cruz@azeu.com',       '09173000001', 'staff',       'active',  1, null],
            ['staff2',    $defaultPwd, 'Patricia Villanueva',   'patricia.v@azeu.com',      '09173000002', 'staff',       'active',  1, null],
            // Pending Customer (id 8) — for testing approval flow
            ['pending1',  $defaultPwd, 'Roberto Garcia',        'roberto.garcia@gmail.com', '09174000001', 'customer',    'pending', 1, $resetDate],
        ];

        $userStmt = $pdo->prepare(
            "INSERT INTO users (username, password, full_name, email, phone, role, status, is_available, cancellation_reset_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        foreach ($users as $u) {
            $userStmt->execute($u);
        }
        msg("Created 8 user accounts (1 super‑admin, 2 customers, 2 riders, 2 staff, 1 pending).", 'success');

        // User preferences for every account
        $prefStmt = $pdo->prepare("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, 0)");
        for ($i = 1; $i <= 8; $i++) {
            $prefStmt->execute([$i]);
        }
        msg("Created user preferences for all accounts.", 'success');

        // Customer addresses
        $addrStmt = $pdo->prepare(
            "INSERT INTO customer_addresses (customer_id, label, full_address, is_default) VALUES (?, ?, ?, ?)"
        );
        // Customer 1 (id 2) addresses
        $addrStmt->execute([2, 'Home',   '456 Rizal Avenue, Brgy. San Isidro, Quezon City, Metro Manila 1100',     1]);
        $addrStmt->execute([2, 'Office', '12th Flr, BPI Tower, Ayala Avenue, Makati City, Metro Manila 1226',       0]);
        // Customer 2 (id 3) address
        $addrStmt->execute([3, 'Home',   '78 Mabini Street, Brgy. Poblacion, Mandaluyong City, Metro Manila 1550', 1]);
        // Pending customer (id 8) address
        $addrStmt->execute([8, 'Home',   '22 Bonifacio Drive, Brgy. Caniogan, Pasig City, Metro Manila 1606',      1]);
        msg("Created customer addresses.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 6. SEED INVENTORY (10 items)
        // ──────────────────────────────────────────────────────────────────────
        $now = date('Y-m-d H:i:s');
        $inventoryItems = [
            // [item_name, stock_count, price, status, last_restocked_at]
            ['30L Water Refill',     50,  45.00, 'active',       $now],
            ['20L Water Refill',     40,  35.00, 'active',       $now],
            ['10L Water Refill',     60,  25.00, 'active',       $now],
            ['5L Water Refill',      80,  15.00, 'active',       $now],
            ['1L Bottled Water',    200,   8.00, 'active',       $now],
            ['500ml Bottled Water', 300,   5.00, 'active',       $now],
            ['Bleach 1L',            25,  55.00, 'active',       $now],
            ['Water Dispenser',       5, 850.00, 'active',       $now],
            ['Water Container 30L',  15, 320.00, 'active',       $now],
            ['Water Container 20L',   0, 250.00, 'out_of_stock', null],
        ];

        $invStmt = $pdo->prepare(
            "INSERT INTO inventory (item_name, stock_count, price, status, last_restocked_at) VALUES (?, ?, ?, ?, ?)"
        );
        foreach ($inventoryItems as $item) {
            $invStmt->execute($item);
        }
        msg("Created 10 inventory items (1 out‑of‑stock for testing).", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 7. SEED ORDERS (multiple statuses)
        // ──────────────────────────────────────────────────────────────────────

        // Helper: generate a 64‑char hex receipt token
        function makeToken() { return bin2hex(random_bytes(32)); }

        $orderStmt = $pdo->prepare(
            "INSERT INTO orders
                (customer_id, rider_id, payment_type, delivery_type, status,
                 delivery_address, order_notes, delivery_fee, subtotal, total_amount,
                 expected_delivery_date, cancellation_reason, cancelled_by,
                 staff_comment, customer_confirmed, customer_confirmed_at,
                 receipt_token, order_date, delivered_at)
             VALUES (?,?,?,?,?, ?,?,?,?,?, ?,?,?, ?,?,?, ?,?,?)"
        );
        $oiStmt = $pdo->prepare(
            "INSERT INTO order_items (order_id, inventory_id, item_name, item_icon, item_price, quantity, subtotal)
             VALUES (?,?,?,?,?,?,?)"
        );
        $dpStmt = $pdo->prepare(
            "INSERT INTO delivery_priority (rider_id, order_id, priority_order) VALUES (?,?,?)"
        );

        $custAddr1 = '456 Rizal Avenue, Brgy. San Isidro, Quezon City, Metro Manila 1100';
        $custAddr2 = '78 Mabini Street, Brgy. Poblacion, Mandaluyong City, Metro Manila 1550';

        // ─── Order 1: PENDING (customer1, delivery) ─────────────────────────
        $orderStmt->execute([
            2, null, 'cod', 'delivery', 'pending',
            $custAddr1, 'Please deliver before noon', 50.00, 90.00, 140.00,
            null, null, null,
            null, 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-1 hour')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 1, '30L Water Refill', null, 45.00, 2, 90.00]);
        msg("Order #$orderId created — status: PENDING.", 'info');

        // ─── Order 2: CONFIRMED (customer1, delivery) ───────────────────────
        $orderStmt->execute([
            2, null, 'cod', 'delivery', 'confirmed',
            $custAddr1, null, 50.00, 70.00, 120.00,
            null, null, null,
            'Waiting for available rider', 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-5 hours')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 2, '20L Water Refill', null, 35.00, 2, 70.00]);
        msg("Order #$orderId created — status: CONFIRMED.", 'info');

        // ─── Order 3: ASSIGNED (customer2, delivery, rider1) ────────────────
        $orderStmt->execute([
            3, 4, 'cod', 'delivery', 'assigned',
            $custAddr2, 'Leave at the gate', 50.00, 25.00, 75.00,
            null, null, null,
            null, 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-1 day')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 3, '10L Water Refill', null, 25.00, 1, 25.00]);
        $dpStmt->execute([4, $orderId, 1]);
        msg("Order #$orderId created — status: ASSIGNED (rider1).", 'info');

        // ─── Order 4: ON DELIVERY (customer1, delivery, rider2) ─────────────
        $orderStmt->execute([
            2, 5, 'cod', 'delivery', 'on_delivery',
            $custAddr1, 'Call upon arrival', 50.00, 60.00, 110.00,
            date('Y-m-d'), null, null,
            null, 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-2 days')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 4, '5L Water Refill',  null, 15.00, 2, 30.00]);
        $oiStmt->execute([$orderId, 5, '1L Bottled Water', null,  8.00, 3, 24.00]);
        // extra item to make the math $60 = 30+24+? — add a small item
        // Actually 30+24 = 54, not 60. Let me include one more item to equal 60.
        $oiStmt->execute([$orderId, 6, '500ml Bottled Water', null, 5.00, 1, 5.00]);
        // Subtotal now 30+24+5 = 59, close enough — let me just fix the order subtotal.
        // Actually let's keep the math exact: update subtotal to 59, total to 109.
        $pdo->prepare("UPDATE orders SET subtotal = 59.00, total_amount = 109.00 WHERE id = ?")->execute([$orderId]);
        $dpStmt->execute([5, $orderId, 1]);
        msg("Order #$orderId created — status: ON DELIVERY (rider2).", 'info');

        // ─── Order 5: DELIVERED (customer2, delivery, rider1) ───────────────
        $deliveredAt = date('Y-m-d H:i:s', strtotime('-3 days'));
        $orderStmt->execute([
            3, 4, 'cod', 'delivery', 'delivered',
            $custAddr2, null, 50.00, 45.00, 95.00,
            date('Y-m-d', strtotime('-3 days')), null, null,
            null, 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-4 days')), $deliveredAt
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 1, '30L Water Refill', null, 45.00, 1, 45.00]);
        msg("Order #$orderId created — status: DELIVERED.", 'info');

        // ─── Order 6: ACCEPTED (customer1, delivery, rider1) ────────────────
        $deliveredAt2 = date('Y-m-d H:i:s', strtotime('-5 days'));
        $acceptedAt   = date('Y-m-d H:i:s', strtotime('-5 days +2 hours'));
        $orderStmt->execute([
            2, 4, 'cod', 'delivery', 'accepted',
            $custAddr1, 'Thank you!', 50.00, 70.00, 120.00,
            date('Y-m-d', strtotime('-5 days')), null, null,
            null, 1, $acceptedAt,
            makeToken(), date('Y-m-d H:i:s', strtotime('-6 days')), $deliveredAt2
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 2, '20L Water Refill', null, 35.00, 2, 70.00]);
        msg("Order #$orderId created — status: ACCEPTED.", 'info');

        // ─── Order 7: READY FOR PICKUP (customer2, pickup) ──────────────────
        $orderStmt->execute([
            3, null, 'pickup', 'pickup', 'ready_for_pickup',
            null, 'Will pick up at 3 PM', 0.00, 55.00, 55.00,
            null, null, null,
            'Items packed and ready at counter', 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-12 hours')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 7, 'Bleach 1L', null, 55.00, 1, 55.00]);
        msg("Order #$orderId created — status: READY FOR PICKUP.", 'info');

        // ─── Order 8: PICKED UP (customer1, pickup) ─────────────────────────
        $pickedUpAt = date('Y-m-d H:i:s', strtotime('-2 days'));
        $orderStmt->execute([
            2, null, 'pickup', 'pickup', 'picked_up',
            null, null, 0.00, 850.00, 850.00,
            null, null, null,
            null, 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-3 days')), $pickedUpAt
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 8, 'Water Dispenser', null, 850.00, 1, 850.00]);
        msg("Order #$orderId created — status: PICKED UP.", 'info');

        // ─── Order 9: CANCELLED by customer (customer2) ─────────────────────
        $orderStmt->execute([
            3, null, 'cod', 'delivery', 'cancelled',
            $custAddr2, null, 50.00, 30.00, 80.00,
            null, 'Changed my mind, will order again later', 3,
            null, 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-7 days')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 4, '5L Water Refill', null, 15.00, 2, 30.00]);
        msg("Order #$orderId created — status: CANCELLED (by customer).", 'info');

        // ─── Order 10: CANCELLED by staff (customer1) ───────────────────────
        $orderStmt->execute([
            2, null, 'cod', 'delivery', 'cancelled',
            $custAddr1, null, 50.00, 45.00, 95.00,
            null, 'Customer unreachable, no response after 3 calls', 6,
            'Multiple contact attempts failed', 0, null,
            makeToken(), date('Y-m-d H:i:s', strtotime('-10 days')), null
        ]);
        $orderId = $pdo->lastInsertId();
        $oiStmt->execute([$orderId, 1, '30L Water Refill', null, 45.00, 1, 45.00]);
        msg("Order #$orderId created — status: CANCELLED (by staff).", 'info');

        msg("Created 10 orders covering all major statuses.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 8. SEED NOTIFICATIONS (a handful of samples)
        // ──────────────────────────────────────────────────────────────────────
        $notifStmt = $pdo->prepare(
            "INSERT INTO notifications (user_id, title, message, type, reference_id, is_read) VALUES (?,?,?,?,?,?)"
        );
        $notifStmt->execute([2, 'Order #1 Placed',      'Your order #1 has been placed successfully.', 'order_placed',    1, 1]);
        $notifStmt->execute([2, 'Order #2 Confirmed',    'Your order #2 has been confirmed!',           'order_confirmed',  2, 1]);
        $notifStmt->execute([2, 'Rider Assigned',        'A rider has been assigned to Order #4.',      'order_assigned',   4, 0]);
        $notifStmt->execute([3, 'Order #7 Ready',        'Order #7 is ready for pickup!',               'ready_for_pickup', 7, 0]);
        $notifStmt->execute([4, 'New Delivery Assigned',  'New delivery assigned: Order #3.',            'order_assigned',   3, 0]);
        $notifStmt->execute([5, 'New Delivery Assigned',  'New delivery assigned: Order #4.',            'order_assigned',   4, 0]);
        $notifStmt->execute([6, 'New Order #1',           'New order #1 from customer1.',                'order_placed',     1, 1]);
        $notifStmt->execute([6, 'New Order #2',           'New order #2 from customer1.',                'order_placed',     2, 0]);
        msg("Created sample notifications.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 9. SEED SESSION LOGS (a few samples)
        // ──────────────────────────────────────────────────────────────────────
        $slStmt = $pdo->prepare(
            "INSERT INTO session_logs (user_id, username, role, action, ip_address, created_at) VALUES (?,?,?,?,?,?)"
        );
        $slStmt->execute([1, 'admin',     'super_admin', 'login',  '127.0.0.1', date('Y-m-d H:i:s', strtotime('-1 day'))]);
        $slStmt->execute([2, 'customer1', 'customer',    'login',  '127.0.0.1', date('Y-m-d H:i:s', strtotime('-2 hours'))]);
        $slStmt->execute([2, 'customer1', 'customer',    'logout', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-1 hour'))]);
        $slStmt->execute([6, 'staff1',    'staff',       'login',  '127.0.0.1', date('Y-m-d H:i:s', strtotime('-30 minutes'))]);
        msg("Created sample session logs.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // DONE
        // ──────────────────────────────────────────────────────────────────────
        msg("✅  Database setup complete! You may now log in.", 'success');

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
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Test Database — Azeu Water Station</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #12121F;
            color: #E8E8F0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 16px;
        }
        .container {
            max-width: 700px;
            width: 100%;
        }
        .card {
            background: rgba(30, 30, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid #2A2A40;
            border-radius: 16px;
            padding: 36px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .logo { text-align: center; margin-bottom: 24px; }
        .logo .material-icons { font-size: 48px; color: #42A5F5; }
        h1 { text-align: center; font-size: 1.6em; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #A8A8C0; margin-bottom: 28px; font-size: 0.95em; }
        .warning-box {
            background: rgba(239, 83, 80, 0.12);
            border: 1px solid rgba(239, 83, 80, 0.35);
            border-left: 4px solid #EF5350;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 28px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .warning-box .material-icons { color: #EF5350; font-size: 28px; flex-shrink: 0; margin-top: 2px; }
        .warning-box p { color: #F0A0A0; line-height: 1.55; font-size: 0.93em; }
        .warning-box strong { color: #EF5350; }
        .info-box {
            background: rgba(30, 136, 229, 0.1);
            border: 1px solid rgba(30, 136, 229, 0.25);
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 28px;
            font-size: 0.88em;
            color: #90CAF9;
            line-height: 1.65;
        }
        .info-box code {
            background: rgba(66,165,245,0.15);
            padding: 2px 7px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.95em;
            color: #BBDEFB;
        }
        .btn-create {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1565C0, #1E88E5);
            color: #fff;
            font-size: 1.1em;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .btn-create:hover { background: linear-gradient(135deg, #0D47A1, #1565C0); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(21,101,192,0.4); }
        .btn-create:active { transform: translateY(0); }
        .btn-create .material-icons { font-size: 24px; }

        /* Feedback */
        .feedback { margin-top: 28px; }
        .feedback h3 { font-size: 1em; margin-bottom: 12px; color: #A8A8C0; }
        .msg {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 0.88em;
            line-height: 1.45;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .msg .material-icons { font-size: 18px; flex-shrink: 0; }
        .msg-success { background: rgba(102,187,106,0.12); border: 1px solid rgba(102,187,106,0.25); color: #A5D6A7; }
        .msg-success .material-icons { color: #66BB6A; }
        .msg-info    { background: rgba(41,182,246,0.10); border: 1px solid rgba(41,182,246,0.20); color: #90CAF9; }
        .msg-info .material-icons { color: #29B6F6; }
        .msg-warning { background: rgba(255,167,38,0.12); border: 1px solid rgba(255,167,38,0.25); color: #FFE0B2; }
        .msg-warning .material-icons { color: #FFA726; }
        .msg-error   { background: rgba(239,83,80,0.15); border: 1px solid rgba(239,83,80,0.30); color: #EF9A9A; }
        .msg-error .material-icons { color: #EF5350; }

        .bottom-link { text-align: center; margin-top: 24px; }
        .bottom-link a { color: #42A5F5; text-decoration: none; font-size: 0.93em; }
        .bottom-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="logo"><span class="material-icons">water_drop</span></div>
        <h1>Database Test Setup</h1>
        <p class="subtitle">Azeu Water Station — Development Tool</p>

        <div class="warning-box">
            <span class="material-icons">warning</span>
            <p><strong>Warning:</strong> This action will <strong>delete the existing database</strong> and recreate all data from scratch. Use <strong>only for testing purposes</strong>. All current data will be permanently lost.</p>
        </div>

        <div class="info-box">
            <strong>What will be created:</strong><br>
            • 13 database tables<br>
            • 8 user accounts — Super Admin: <code>admin</code> / <code>admin</code> — Others: password <code>12345</code><br>
            &nbsp;&nbsp;(2 Customers, 2 Riders, 2 Staff, 1 Pending Account)<br>
            • 10 inventory items with stock &amp; pricing<br>
            • 10 orders across all statuses (pending, confirmed, assigned, on delivery, delivered, accepted, ready for pickup, picked up, cancelled)<br>
            • Sample notifications, session logs &amp; system settings
        </div>

        <?php if (!$executed): ?>
        <form method="POST">
            <button type="submit" name="create_db" class="btn-create" onclick="return confirm('Are you sure? This will DELETE the entire database and recreate it.');">
                <span class="material-icons">rocket_launch</span>
                CREATE DATABASE &amp; SEED DATA
            </button>
        </form>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
        <div class="feedback">
            <h3><?= $hasError ? '⚠️ Completed with errors' : '📋 Execution Log' ?></h3>
            <?php foreach ($messages as $m): ?>
                <?php
                    $iconMap = [
                        'success' => 'check_circle',
                        'info'    => 'info',
                        'warning' => 'warning',
                        'error'   => 'error',
                    ];
                    $icon = $iconMap[$m['type']] ?? 'info';
                ?>
                <div class="msg msg-<?= $m['type'] ?>">
                    <span class="material-icons"><?= $icon ?></span>
                    <?= htmlspecialchars($m['text']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$hasError): ?>
        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="create_db" class="btn-create" style="background: linear-gradient(135deg, #FFA726, #FB8C00);"
                    onclick="return confirm('Run again? This will DELETE everything and recreate it.');">
                <span class="material-icons">refresh</span>
                RESET &amp; RECREATE
            </button>
        </form>
        <?php endif; ?>
        <?php endif; ?>

        <div class="bottom-link">
            <a href="index.php">← Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>
