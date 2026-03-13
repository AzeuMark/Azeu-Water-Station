<?php
/**
 * ============================================================================
 * AZEU WATER STATION — INTERACTIVE DATABASE TEST SETUP SCRIPT
 * ============================================================================
 *
 * ⚠️  FOR TESTING PURPOSES ONLY
 *
 * This script provides a fully editable interface to configure:
 *   • User accounts per role (with custom credentials)
 *   • Inventory items (name, stock, price, status)
 *   • Order generation toggles per status
 *   • System settings
 *
 * Then DROP + recreate the database with your configured data.
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

function msg($text, $type = 'info')
{
    global $messages;
    $messages[] = ['text' => $text, 'type' => $type];
}

function makeToken()
{
    return bin2hex(random_bytes(32));
}

// ── Default Configuration (editable via form) ───────────────────────────────

$defaultUsers = [
    'super_admin' => [
        ['username' => 'admin', 'password' => 'admin', 'full_name' => 'System Administrator', 'email' => 'admin@azeu.com', 'phone' => '09170000001', 'status' => 'active'],
    ],
    'admin' => [
        ['username' => 'admin1', 'password' => '12345', 'full_name' => 'Branch Manager', 'email' => 'manager@azeu.com', 'phone' => '09170000002', 'status' => 'active'],
    ],
    'staff' => [
        ['username' => 'staff1', 'password' => '12345', 'full_name' => 'Anna Cruz', 'email' => 'anna.cruz@azeu.com', 'phone' => '09173000001', 'status' => 'active'],
        ['username' => 'staff2', 'password' => '12345', 'full_name' => 'Patricia Villanueva', 'email' => 'patricia.v@azeu.com', 'phone' => '09173000002', 'status' => 'active'],
    ],
    'rider' => [
        ['username' => 'rider1', 'password' => '12345', 'full_name' => 'Carlo Mendoza', 'email' => 'carlo.mendoza@gmail.com', 'phone' => '09172000001', 'status' => 'active'],
        ['username' => 'rider2', 'password' => '12345', 'full_name' => 'Miguel Torres', 'email' => 'miguel.torres@gmail.com', 'phone' => '09172000002', 'status' => 'active'],
    ],
    'customer' => [
        ['username' => 'customer1', 'password' => '12345', 'full_name' => 'Maria Santos', 'email' => 'maria.santos@gmail.com', 'phone' => '09171000001', 'status' => 'active'],
        ['username' => 'customer2', 'password' => '12345', 'full_name' => 'Jose Reyes', 'email' => 'jose.reyes@gmail.com', 'phone' => '09171000002', 'status' => 'active'],
        ['username' => 'pending1', 'password' => '12345', 'full_name' => 'Roberto Garcia', 'email' => 'roberto.garcia@gmail.com', 'phone' => '09174000001', 'status' => 'pending'],
    ],
];

$defaultInventory = [
    ['item_name' => '30L Water Refill', 'stock_count' => 50, 'price' => 45.00, 'status' => 'active'],
    ['item_name' => '20L Water Refill', 'stock_count' => 40, 'price' => 35.00, 'status' => 'active'],
    ['item_name' => '10L Water Refill', 'stock_count' => 60, 'price' => 25.00, 'status' => 'active'],
    ['item_name' => '5L Water Refill', 'stock_count' => 80, 'price' => 15.00, 'status' => 'active'],
    ['item_name' => '1L Bottled Water', 'stock_count' => 200, 'price' => 8.00, 'status' => 'active'],
    ['item_name' => '500ml Bottled Water', 'stock_count' => 300, 'price' => 5.00, 'status' => 'active'],
    ['item_name' => 'Bleach 1L', 'stock_count' => 25, 'price' => 55.00, 'status' => 'active'],
    ['item_name' => 'Water Dispenser', 'stock_count' => 5, 'price' => 850.00, 'status' => 'active'],
    ['item_name' => 'Water Container 30L', 'stock_count' => 15, 'price' => 320.00, 'status' => 'active'],
    ['item_name' => 'Water Container 20L', 'stock_count' => 0, 'price' => 250.00, 'status' => 'out_of_stock'],
];

$defaultOrderStatuses = [
    'pending'          => ['enabled' => true,  'label' => 'Pending'],
    'confirmed'        => ['enabled' => true,  'label' => 'Confirmed'],
    'assigned'         => ['enabled' => true,  'label' => 'Assigned'],
    'on_delivery'      => ['enabled' => true,  'label' => 'On Delivery'],
    'delivered'        => ['enabled' => true,  'label' => 'Delivered'],
    'accepted'         => ['enabled' => true,  'label' => 'Accepted'],
    'ready_for_pickup' => ['enabled' => true,  'label' => 'Ready for Pickup'],
    'picked_up'        => ['enabled' => true,  'label' => 'Picked Up'],
    'cancelled'        => ['enabled' => true,  'label' => 'Cancelled'],
];

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

// ── Process POST ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
    $executed = true;

    try {
        // ── Parse submitted users ────────────────────────────────────────────
        $submittedUsers = [];
        $userIdMap = [];   // track assigned IDs per role
        if (isset($_POST['users']) && is_array($_POST['users'])) {
            foreach ($_POST['users'] as $role => $accounts) {
                if (!is_array($accounts)) continue;
                foreach ($accounts as $acc) {
                    if (empty($acc['username']) || empty($acc['full_name'])) continue;
                    $submittedUsers[] = [
                        'username'  => trim($acc['username']),
                        'password'  => trim($acc['password'] ?: '12345'),
                        'full_name' => trim($acc['full_name']),
                        'email'     => trim($acc['email'] ?: ''),
                        'phone'     => trim($acc['phone'] ?: ''),
                        'role'      => $role,
                        'status'    => $acc['status'] ?? 'active',
                    ];
                }
            }
        }

        // ── Parse submitted inventory ────────────────────────────────────────
        $submittedInventory = [];
        if (isset($_POST['inventory']) && is_array($_POST['inventory'])) {
            foreach ($_POST['inventory'] as $item) {
                if (empty($item['item_name'])) continue;
                $submittedInventory[] = [
                    'item_name'   => trim($item['item_name']),
                    'stock_count' => (int)($item['stock_count'] ?? 0),
                    'price'       => (float)($item['price'] ?? 0),
                    'status'      => $item['status'] ?? 'active',
                ];
            }
        }

        // ── Parse order status toggles ───────────────────────────────────────
        $enabledStatuses = [];
        if (isset($_POST['order_status']) && is_array($_POST['order_status'])) {
            $enabledStatuses = array_keys($_POST['order_status']);
        }

        // ── Parse settings ───────────────────────────────────────────────────
        $submittedSettings = [];
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $val) {
                $submittedSettings[$key] = trim($val);
            }
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

        $pdo->exec("CREATE TABLE user_preferences (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            user_id INT(11) UNIQUE NOT NULL,
            dark_mode TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            CONSTRAINT fk_user_pref FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

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

        $pdo->exec("CREATE TABLE settings (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT NOT NULL,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE default_items (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            item_name VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

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
        $settingsStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $settingsToUse = !empty($submittedSettings) ? $submittedSettings : $defaultSettings;
        foreach ($settingsToUse as $k => $v) {
            $settingsStmt->execute([$k, $v]);
        }
        msg("Seeded " . count($settingsToUse) . " system settings.", 'success');

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
        $useEncryption = ($settingsToUse['encrypt_passwords'] ?? '1') === '1';
        $resetDate = date('Y-m-d', strtotime('first day of next month'));

        $userStmt = $pdo->prepare(
            "INSERT INTO users (username, password, full_name, email, phone, role, status, is_available, cancellation_reset_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)"
        );

        $usersToSeed = !empty($submittedUsers) ? $submittedUsers : [];
        if (empty($usersToSeed)) {
            // Flatten defaults
            foreach ($defaultUsers as $role => $accounts) {
                foreach ($accounts as $acc) {
                    $usersToSeed[] = array_merge($acc, ['role' => $role]);
                }
            }
        }

        // Track IDs by role for order seeding
        $usersByRole = ['super_admin' => [], 'admin' => [], 'staff' => [], 'rider' => [], 'customer' => []];
        $userCount = 0;

        foreach ($usersToSeed as $u) {
            $pwd = $useEncryption ? encrypt($u['password'], ENCRYPTION_KEY) : $u['password'];
            $isCustomer = ($u['role'] === 'customer');
            $userStmt->execute([
                $u['username'],
                $pwd,
                $u['full_name'],
                $u['email'],
                $u['phone'],
                $u['role'],
                $u['status'],
                $isCustomer ? $resetDate : null,
            ]);
            $userId = (int)$pdo->lastInsertId();
            $usersByRole[$u['role']][] = [
                'id'       => $userId,
                'username' => $u['username'],
                'status'   => $u['status'],
            ];
            $userCount++;
        }
        msg("Created $userCount user accounts.", 'success');

        // User preferences
        $prefStmt = $pdo->prepare("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, 0)");
        $allUserIds = [];
        foreach ($usersByRole as $roleUsers) {
            foreach ($roleUsers as $ru) {
                $allUserIds[] = $ru['id'];
                $prefStmt->execute([$ru['id']]);
            }
        }
        msg("Created user preferences for all accounts.", 'success');

        // Customer addresses for active customers
        $addrStmt = $pdo->prepare(
            "INSERT INTO customer_addresses (customer_id, label, full_address, is_default) VALUES (?, ?, ?, ?)"
        );
        $sampleAddresses = [
            '456 Rizal Avenue, Brgy. San Isidro, Quezon City, Metro Manila 1100',
            '78 Mabini Street, Brgy. Poblacion, Mandaluyong City, Metro Manila 1550',
            '22 Bonifacio Drive, Brgy. Caniogan, Pasig City, Metro Manila 1606',
            '15 Katipunan Avenue, Brgy. Loyola Heights, Quezon City, Metro Manila 1108',
            '88 Shaw Boulevard, Brgy. Wack-Wack, Mandaluyong City, Metro Manila 1555',
        ];
        $addrIdx = 0;
        foreach ($usersByRole['customer'] as $cust) {
            $addr = $sampleAddresses[$addrIdx % count($sampleAddresses)];
            $addrStmt->execute([$cust['id'], 'Home', $addr, 1]);
            $addrIdx++;
        }
        msg("Created customer addresses.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 6. SEED INVENTORY
        // ──────────────────────────────────────────────────────────────────────
        $now = date('Y-m-d H:i:s');
        $inventoryToSeed = !empty($submittedInventory) ? $submittedInventory : $defaultInventory;

        $invStmt = $pdo->prepare(
            "INSERT INTO inventory (item_name, stock_count, price, status, last_restocked_at) VALUES (?, ?, ?, ?, ?)"
        );
        $inventoryIds = [];
        foreach ($inventoryToSeed as $item) {
            $restocked = ($item['status'] !== 'out_of_stock') ? $now : null;
            $invStmt->execute([
                $item['item_name'],
                $item['stock_count'],
                $item['price'],
                $item['status'],
                $restocked,
            ]);
            $inventoryIds[] = [
                'id'    => (int)$pdo->lastInsertId(),
                'name'  => $item['item_name'],
                'price' => (float)$item['price'],
                'status' => $item['status'],
            ];
        }
        msg("Created " . count($inventoryIds) . " inventory items.", 'success');

        // ──────────────────────────────────────────────────────────────────────
        // 7. SEED ORDERS (based on toggles)
        // ──────────────────────────────────────────────────────────────────────
        // Only create orders if we have customers and active inventory
        $activeCustomers = array_filter($usersByRole['customer'], fn($c) => $c['status'] === 'active');
        $activeRiders    = array_filter($usersByRole['rider'], fn($r) => $r['status'] === 'active');
        $activeStaff     = array_filter($usersByRole['staff'], fn($s) => $s['status'] === 'active');
        $activeInv       = array_filter($inventoryIds, fn($i) => $i['status'] === 'active');

        $activeCustomers = array_values($activeCustomers);
        $activeRiders    = array_values($activeRiders);
        $activeStaff     = array_values($activeStaff);
        $activeInv       = array_values($activeInv);

        $deliveryFee = (float)($settingsToUse['delivery_fee'] ?? 50.00);

        if (!empty($activeCustomers) && !empty($activeInv) && !empty($enabledStatuses)) {

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
            $notifStmt = $pdo->prepare(
                "INSERT INTO notifications (user_id, title, message, type, reference_id, is_read) VALUES (?,?,?,?,?,?)"
            );

            // Fetch first customer address
            $addrFetch = $pdo->prepare("SELECT full_address FROM customer_addresses WHERE customer_id = ? AND is_default = 1 LIMIT 1");

            $orderCount = 0;
            $custIdx = 0;
            $riderIdx = 0;
            $invIdx = 0;

            // Helper to get next cycled element
            $nextCust = function () use (&$activeCustomers, &$custIdx) {
                $c = $activeCustomers[$custIdx % count($activeCustomers)];
                $custIdx++;
                return $c;
            };
            $nextRider = function () use (&$activeRiders, &$riderIdx) {
                if (empty($activeRiders)) return null;
                $r = $activeRiders[$riderIdx % count($activeRiders)];
                $riderIdx++;
                return $r;
            };
            $nextInv = function () use (&$activeInv, &$invIdx) {
                $i = $activeInv[$invIdx % count($activeInv)];
                $invIdx++;
                return $i;
            };
            $getCustAddr = function ($custId) use ($addrFetch) {
                $addrFetch->execute([$custId]);
                $row = $addrFetch->fetch();
                return $row ? $row['full_address'] : 'Default Address, Metro Manila';
            };

            // ── PENDING ──────────────────────────────────────────────────────
            if (in_array('pending', $enabledStatuses)) {
                $cust = $nextCust();
                $inv  = $nextInv();
                $qty  = 2;
                $sub  = $inv['price'] * $qty;
                $total = $sub + $deliveryFee;
                $addr = $getCustAddr($cust['id']);

                $orderStmt->execute([
                    $cust['id'], null, 'cod', 'delivery', 'pending',
                    $addr, 'Please deliver before noon', $deliveryFee, $sub, $total,
                    null, null, null,
                    null, 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-1 hour')), null
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $notifStmt->execute([$cust['id'], "Order #$oid Placed", "Your order #$oid has been placed.", 'order_placed', $oid, 0]);
                $orderCount++;
                msg("Order #$oid — PENDING", 'info');
            }

            // ── CONFIRMED ────────────────────────────────────────────────────
            if (in_array('confirmed', $enabledStatuses)) {
                $cust = $nextCust();
                $inv  = $nextInv();
                $qty  = 2;
                $sub  = $inv['price'] * $qty;
                $total = $sub + $deliveryFee;
                $addr = $getCustAddr($cust['id']);

                $orderStmt->execute([
                    $cust['id'], null, 'cod', 'delivery', 'confirmed',
                    $addr, null, $deliveryFee, $sub, $total,
                    null, null, null,
                    'Waiting for available rider', 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-5 hours')), null
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $notifStmt->execute([$cust['id'], "Order #$oid Confirmed", "Your order #$oid has been confirmed!", 'order_confirmed', $oid, 0]);
                $orderCount++;
                msg("Order #$oid — CONFIRMED", 'info');
            }

            // ── ASSIGNED ─────────────────────────────────────────────────────
            if (in_array('assigned', $enabledStatuses) && !empty($activeRiders)) {
                $cust  = $nextCust();
                $rider = $nextRider();
                $inv   = $nextInv();
                $qty   = 1;
                $sub   = $inv['price'] * $qty;
                $total = $sub + $deliveryFee;
                $addr  = $getCustAddr($cust['id']);

                $orderStmt->execute([
                    $cust['id'], $rider['id'], 'cod', 'delivery', 'assigned',
                    $addr, 'Leave at the gate', $deliveryFee, $sub, $total,
                    null, null, null,
                    null, 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-1 day')), null
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $dpStmt->execute([$rider['id'], $oid, 1]);
                $notifStmt->execute([$rider['id'], 'New Delivery', "Delivery assigned: Order #$oid", 'order_assigned', $oid, 0]);
                $notifStmt->execute([$cust['id'], 'Rider Assigned', "A rider has been assigned to Order #$oid.", 'order_assigned', $oid, 0]);
                $orderCount++;
                msg("Order #$oid — ASSIGNED (rider: {$rider['username']})", 'info');
            }

            // ── ON DELIVERY ──────────────────────────────────────────────────
            if (in_array('on_delivery', $enabledStatuses) && !empty($activeRiders)) {
                $cust  = $nextCust();
                $rider = $nextRider();
                $inv1  = $nextInv();
                $inv2  = $nextInv();
                $qty1  = 2;
                $qty2  = 3;
                $sub   = ($inv1['price'] * $qty1) + ($inv2['price'] * $qty2);
                $total = $sub + $deliveryFee;
                $addr  = $getCustAddr($cust['id']);

                $orderStmt->execute([
                    $cust['id'], $rider['id'], 'cod', 'delivery', 'on_delivery',
                    $addr, 'Call upon arrival', $deliveryFee, $sub, $total,
                    date('Y-m-d'), null, null,
                    null, 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-2 days')), null
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv1['id'], $inv1['name'], null, $inv1['price'], $qty1, $inv1['price'] * $qty1]);
                $oiStmt->execute([$oid, $inv2['id'], $inv2['name'], null, $inv2['price'], $qty2, $inv2['price'] * $qty2]);
                $dpStmt->execute([$rider['id'], $oid, 1]);
                $notifStmt->execute([$cust['id'], 'On the Way', "Order #$oid is on delivery!", 'on_delivery', $oid, 0]);
                $orderCount++;
                msg("Order #$oid — ON DELIVERY (rider: {$rider['username']})", 'info');
            }

            // ── DELIVERED ────────────────────────────────────────────────────
            if (in_array('delivered', $enabledStatuses) && !empty($activeRiders)) {
                $cust  = $nextCust();
                $rider = $nextRider();
                $inv   = $nextInv();
                $qty   = 1;
                $sub   = $inv['price'] * $qty;
                $total = $sub + $deliveryFee;
                $addr  = $getCustAddr($cust['id']);
                $delAt = date('Y-m-d H:i:s', strtotime('-3 days'));

                $orderStmt->execute([
                    $cust['id'], $rider['id'], 'cod', 'delivery', 'delivered',
                    $addr, null, $deliveryFee, $sub, $total,
                    date('Y-m-d', strtotime('-3 days')), null, null,
                    null, 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-4 days')), $delAt
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $notifStmt->execute([$cust['id'], 'Delivered', "Order #$oid has been delivered!", 'delivered', $oid, 0]);
                $orderCount++;
                msg("Order #$oid — DELIVERED", 'info');
            }

            // ── ACCEPTED ─────────────────────────────────────────────────────
            if (in_array('accepted', $enabledStatuses) && !empty($activeRiders)) {
                $cust  = $nextCust();
                $rider = $nextRider();
                $inv   = $nextInv();
                $qty   = 2;
                $sub   = $inv['price'] * $qty;
                $total = $sub + $deliveryFee;
                $addr  = $getCustAddr($cust['id']);
                $delAt = date('Y-m-d H:i:s', strtotime('-5 days'));
                $accAt = date('Y-m-d H:i:s', strtotime('-5 days +2 hours'));

                $orderStmt->execute([
                    $cust['id'], $rider['id'], 'cod', 'delivery', 'accepted',
                    $addr, 'Thank you!', $deliveryFee, $sub, $total,
                    date('Y-m-d', strtotime('-5 days')), null, null,
                    null, 1, $accAt,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-6 days')), $delAt
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $notifStmt->execute([$cust['id'], 'Accepted', "Order #$oid confirmed by you.", 'accepted', $oid, 1]);
                $orderCount++;
                msg("Order #$oid — ACCEPTED", 'info');
            }

            // ── READY FOR PICKUP ─────────────────────────────────────────────
            if (in_array('ready_for_pickup', $enabledStatuses)) {
                $cust = $nextCust();
                $inv  = $nextInv();
                $qty  = 1;
                $sub  = $inv['price'] * $qty;

                $orderStmt->execute([
                    $cust['id'], null, 'pickup', 'pickup', 'ready_for_pickup',
                    null, 'Will pick up at 3 PM', 0.00, $sub, $sub,
                    null, null, null,
                    'Items packed and ready at counter', 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-12 hours')), null
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $notifStmt->execute([$cust['id'], 'Ready for Pickup', "Order #$oid is ready!", 'ready_for_pickup', $oid, 0]);
                $orderCount++;
                msg("Order #$oid — READY FOR PICKUP", 'info');
            }

            // ── PICKED UP ────────────────────────────────────────────────────
            if (in_array('picked_up', $enabledStatuses)) {
                $cust = $nextCust();
                $inv  = $nextInv();
                $qty  = 1;
                $sub  = $inv['price'] * $qty;
                $pAt  = date('Y-m-d H:i:s', strtotime('-2 days'));

                $orderStmt->execute([
                    $cust['id'], null, 'pickup', 'pickup', 'picked_up',
                    null, null, 0.00, $sub, $sub,
                    null, null, null,
                    null, 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-3 days')), $pAt
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $orderCount++;
                msg("Order #$oid — PICKED UP", 'info');
            }

            // ── CANCELLED (by customer) ──────────────────────────────────────
            if (in_array('cancelled', $enabledStatuses)) {
                $cust = $nextCust();
                $inv  = $nextInv();
                $qty  = 2;
                $sub  = $inv['price'] * $qty;
                $total = $sub + $deliveryFee;
                $addr = $getCustAddr($cust['id']);

                $orderStmt->execute([
                    $cust['id'], null, 'cod', 'delivery', 'cancelled',
                    $addr, null, $deliveryFee, $sub, $total,
                    null, 'Changed my mind, will order again later', $cust['id'],
                    null, 0, null,
                    makeToken(), date('Y-m-d H:i:s', strtotime('-7 days')), null
                ]);
                $oid = (int)$pdo->lastInsertId();
                $oiStmt->execute([$oid, $inv['id'], $inv['name'], null, $inv['price'], $qty, $sub]);
                $orderCount++;
                msg("Order #$oid — CANCELLED (by customer)", 'info');

                // Second cancelled order (by staff) if staff exist
                if (!empty($activeStaff)) {
                    $cust2 = $nextCust();
                    $inv2  = $nextInv();
                    $qty2  = 1;
                    $sub2  = $inv2['price'] * $qty2;
                    $total2 = $sub2 + $deliveryFee;
                    $addr2 = $getCustAddr($cust2['id']);
                    $staffUser = $activeStaff[0];

                    $orderStmt->execute([
                        $cust2['id'], null, 'cod', 'delivery', 'cancelled',
                        $addr2, null, $deliveryFee, $sub2, $total2,
                        null, 'Customer unreachable after 3 calls', $staffUser['id'],
                        'Multiple contact attempts failed', 0, null,
                        makeToken(), date('Y-m-d H:i:s', strtotime('-10 days')), null
                    ]);
                    $oid2 = (int)$pdo->lastInsertId();
                    $oiStmt->execute([$oid2, $inv2['id'], $inv2['name'], null, $inv2['price'], $qty2, $sub2]);
                    $orderCount++;
                    msg("Order #$oid2 — CANCELLED (by staff: {$staffUser['username']})", 'info');
                }
            }

            msg("Created $orderCount orders.", 'success');
        } else {
            if (empty($enabledStatuses)) {
                msg("No order statuses enabled — skipped order seeding.", 'warning');
            } elseif (empty($activeCustomers)) {
                msg("No active customers — skipped order seeding.", 'warning');
            } elseif (empty($activeInv)) {
                msg("No active inventory — skipped order seeding.", 'warning');
            }
        }

        // ──────────────────────────────────────────────────────────────────────
        // 8. SESSION LOGS
        // ──────────────────────────────────────────────────────────────────────
        $slStmt = $pdo->prepare(
            "INSERT INTO session_logs (user_id, username, role, action, ip_address, created_at) VALUES (?,?,?,?,?,?)"
        );
        // Log super_admin login
        if (!empty($usersByRole['super_admin'])) {
            $sa = $usersByRole['super_admin'][0];
            $slStmt->execute([$sa['id'], $sa['username'], 'super_admin', 'login', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-1 day'))]);
        }
        // Log first customer login/logout
        if (!empty($activeCustomers)) {
            $fc = $activeCustomers[0];
            $slStmt->execute([$fc['id'], $fc['username'], 'customer', 'login',  '127.0.0.1', date('Y-m-d H:i:s', strtotime('-2 hours'))]);
            $slStmt->execute([$fc['id'], $fc['username'], 'customer', 'logout', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-1 hour'))]);
        }
        // Log first staff login
        if (!empty($activeStaff)) {
            $fs = $activeStaff[0];
            $slStmt->execute([$fs['id'], $fs['username'], 'staff', 'login', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-30 minutes'))]);
        }
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

// ── Role display config ──────────────────────────────────────────────────────
$roleConfig = [
    'super_admin' => ['icon' => 'shield',            'color' => '#EF5350', 'label' => 'Super Admin'],
    'admin'       => ['icon' => 'admin_panel_settings', 'color' => '#FF7043', 'label' => 'Admin'],
    'staff'       => ['icon' => 'badge',              'color' => '#AB47BC', 'label' => 'Staff'],
    'rider'       => ['icon' => 'two_wheeler',        'color' => '#42A5F5', 'label' => 'Rider'],
    'customer'    => ['icon' => 'person',             'color' => '#66BB6A', 'label' => 'Customer'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Setup — Azeu Water Station</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0D0D1A;
            color: #E0E0F0;
            min-height: 100vh;
            padding: 30px 16px 60px;
        }

        .container { max-width: 960px; margin: 0 auto; }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header .material-icons { font-size: 52px; color: #42A5F5; }
        .header h1 { font-size: 1.7em; margin: 8px 0 4px; }
        .header .sub { color: #8888AA; font-size: 0.95em; }

        /* ── Warning ── */
        .warning-box {
            background: rgba(239, 83, 80, 0.1);
            border: 1px solid rgba(239, 83, 80, 0.3);
            border-left: 4px solid #EF5350;
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 28px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .warning-box .material-icons { color: #EF5350; font-size: 28px; flex-shrink: 0; }
        .warning-box p { color: #F0A0A0; line-height: 1.5; font-size: 0.92em; }
        .warning-box strong { color: #EF5350; }

        /* ── Sections ── */
        .section {
            background: rgba(25, 25, 45, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid #2A2A42;
            border-radius: 14px;
            margin-bottom: 24px;
            overflow: hidden;
        }
        .section-header {
            background: rgba(30, 30, 55, 0.8);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #2A2A42;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }
        .section-header:hover { background: rgba(40, 40, 65, 0.8); }
        .section-header .material-icons { font-size: 24px; color: #42A5F5; }
        .section-header h2 { font-size: 1.05em; font-weight: 600; flex: 1; }
        .section-header .badge {
            background: rgba(66,165,245,0.15);
            color: #90CAF9;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78em;
            font-weight: 600;
        }
        .section-header .chevron {
            color: #666;
            transition: transform 0.3s;
        }
        .section-header.collapsed .chevron { transform: rotate(-90deg); }
        .section-body { padding: 20px 22px; }
        .section-body.collapsed { display: none; }

        /* ── Role Groups ── */
        .role-group {
            border: 1px solid #2A2A42;
            border-radius: 10px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .role-group:last-child { margin-bottom: 0; }
        .role-header {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }
        .role-header:hover { background: rgba(255,255,255,0.03); }
        .role-header .material-icons { font-size: 22px; }
        .role-header .role-label { font-weight: 600; font-size: 0.95em; flex: 1; }
        .role-header .count-badge {
            background: rgba(255,255,255,0.08);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            color: #aaa;
        }
        .role-body { padding: 0 18px 16px; }

        /* ── User Card ── */
        .user-card {
            background: rgba(20, 20, 40, 0.6);
            border: 1px solid #222240;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .user-card:last-child { margin-bottom: 0; }
        .user-card .full-width { grid-column: 1 / -1; }

        .user-card-header {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .user-card-header span { font-size: 0.8em; color: #888; }
        .btn-remove {
            background: rgba(239,83,80,0.1);
            border: 1px solid rgba(239,83,80,0.3);
            color: #EF5350;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 0.75em;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }
        .btn-remove:hover { background: rgba(239,83,80,0.25); }

        /* ── Inputs ── */
        .field { display: flex; flex-direction: column; gap: 4px; }
        .field label {
            font-size: 0.72em;
            font-weight: 600;
            color: #7777AA;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field input, .field select {
            background: rgba(15, 15, 30, 0.8);
            border: 1px solid #2A2A42;
            color: #E0E0F0;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 0.88em;
            font-family: inherit;
            transition: border-color 0.2s;
            width: 100%;
        }
        .field input:focus, .field select:focus {
            outline: none;
            border-color: #42A5F5;
            box-shadow: 0 0 0 2px rgba(66,165,245,0.15);
        }
        .field input::placeholder { color: #555; }

        /* ── Inventory Table ── */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
        }
        .inv-table th {
            text-align: left;
            font-size: 0.72em;
            font-weight: 600;
            color: #7777AA;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 1px solid #2A2A42;
        }
        .inv-table td {
            padding: 6px 10px;
            border-bottom: 1px solid rgba(42,42,66,0.5);
            vertical-align: middle;
        }
        .inv-table input, .inv-table select {
            background: rgba(15, 15, 30, 0.8);
            border: 1px solid #2A2A42;
            color: #E0E0F0;
            padding: 8px 10px;
            border-radius: 7px;
            font-size: 0.85em;
            font-family: inherit;
            width: 100%;
            transition: border-color 0.2s;
        }
        .inv-table input:focus, .inv-table select:focus {
            outline: none;
            border-color: #42A5F5;
        }
        .inv-table .btn-remove { padding: 5px 10px; }
        .inv-table tr:last-child td { border-bottom: none; }

        /* ── Toggle Switches ── */
        .toggle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .toggle-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(20,20,40,0.6);
            border: 1px solid #222240;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .toggle-item:hover { border-color: #42A5F5; background: rgba(25,25,50,0.8); }
        .toggle-item.active { border-color: #42A5F5; background: rgba(66,165,245,0.08); }

        .switch {
            position: relative;
            width: 42px;
            height: 24px;
            flex-shrink: 0;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .switch .slider {
            position: absolute;
            inset: 0;
            background: #2A2A42;
            border-radius: 24px;
            transition: 0.3s;
            cursor: pointer;
        }
        .switch .slider::before {
            content: '';
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: #666;
            border-radius: 50%;
            transition: 0.3s;
        }
        .switch input:checked + .slider { background: #1E88E5; }
        .switch input:checked + .slider::before {
            transform: translateX(18px);
            background: #fff;
        }
        .toggle-label { font-size: 0.9em; font-weight: 500; }

        /* ── Settings Grid ── */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }

        /* ── Add Buttons ── */
        .btn-add {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(66,165,245,0.1);
            border: 1px dashed rgba(66,165,245,0.3);
            color: #42A5F5;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            margin-top: 10px;
        }
        .btn-add:hover { background: rgba(66,165,245,0.2); border-color: #42A5F5; }
        .btn-add .material-icons { font-size: 18px; }

        /* ── Submit Button ── */
        .btn-submit-wrap { margin: 32px 0 20px; }
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #1565C0, #1E88E5);
            color: #fff;
            font-size: 1.15em;
            font-weight: 700;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            letter-spacing: 0.3px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #0D47A1, #1565C0);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(21,101,192,0.45);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit .material-icons { font-size: 26px; }
        .btn-submit.reset {
            background: linear-gradient(135deg, #E65100, #FB8C00);
            margin-top: 16px;
        }
        .btn-submit.reset:hover {
            background: linear-gradient(135deg, #BF360C, #E65100);
            box-shadow: 0 8px 28px rgba(230,81,0,0.4);
        }

        /* ── Feedback ── */
        .feedback { margin-top: 28px; }
        .feedback h3 { font-size: 1em; margin-bottom: 12px; color: #A8A8C0; }
        .msg {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 6px;
            font-size: 0.85em;
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .msg .material-icons { font-size: 18px; flex-shrink: 0; }
        .msg-success { background: rgba(102,187,106,0.1); border: 1px solid rgba(102,187,106,0.2); color: #A5D6A7; }
        .msg-success .material-icons { color: #66BB6A; }
        .msg-info    { background: rgba(41,182,246,0.08); border: 1px solid rgba(41,182,246,0.18); color: #90CAF9; }
        .msg-info .material-icons { color: #29B6F6; }
        .msg-warning { background: rgba(255,167,38,0.1); border: 1px solid rgba(255,167,38,0.2); color: #FFE0B2; }
        .msg-warning .material-icons { color: #FFA726; }
        .msg-error   { background: rgba(239,83,80,0.12); border: 1px solid rgba(239,83,80,0.25); color: #EF9A9A; }
        .msg-error .material-icons { color: #EF5350; }

        .bottom-link { text-align: center; margin-top: 24px; }
        .bottom-link a { color: #42A5F5; text-decoration: none; font-size: 0.93em; }
        .bottom-link a:hover { text-decoration: underline; }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .user-card { grid-template-columns: 1fr; }
            .toggle-grid { grid-template-columns: 1fr; }
            .settings-grid { grid-template-columns: 1fr; }
            .inv-table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <span class="material-icons">water_drop</span>
        <h1>Interactive Database Setup</h1>
        <p class="sub">Azeu Water Station — Development Tool</p>
    </div>

    <!-- Warning -->
    <div class="warning-box">
        <span class="material-icons">warning</span>
        <p><strong>Warning:</strong> This will <strong>completely delete</strong> the existing database and recreate it with the configuration below. <strong>All current data will be permanently lost.</strong> Use only for testing.</p>
    </div>

    <form method="POST" id="setupForm">

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- SECTION 1: USER ACCOUNTS                                          -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header" onclick="toggleSection(this)">
            <span class="material-icons">group</span>
            <h2>User Accounts</h2>
            <span class="badge" id="userCountBadge"><?= array_sum(array_map('count', $defaultUsers)) ?> users</span>
            <span class="material-icons chevron">expand_more</span>
        </div>
        <div class="section-body" id="usersSection">
            <?php foreach ($roleConfig as $role => $cfg): ?>
            <div class="role-group" id="role-group-<?= $role ?>">
                <div class="role-header" onclick="toggleRole(this)" style="background: <?= $cfg['color'] ?>15;">
                    <span class="material-icons" style="color: <?= $cfg['color'] ?>"><?= $cfg['icon'] ?></span>
                    <span class="role-label" style="color: <?= $cfg['color'] ?>"><?= $cfg['label'] ?></span>
                    <span class="count-badge role-count" data-role="<?= $role ?>"><?= count($defaultUsers[$role] ?? []) ?></span>
                    <span class="material-icons chevron" style="color: #666; font-size: 20px;">expand_more</span>
                </div>
                <div class="role-body" id="role-body-<?= $role ?>">
                    <div class="user-cards-container" id="users-<?= $role ?>">
                        <?php if (isset($defaultUsers[$role])): ?>
                        <?php foreach ($defaultUsers[$role] as $idx => $u): ?>
                        <div class="user-card" data-role="<?= $role ?>">
                            <div class="user-card-header">
                                <span><?= $cfg['label'] ?> #<?= $idx + 1 ?></span>
                                <button type="button" class="btn-remove" onclick="removeUserCard(this)">
                                    <span class="material-icons" style="font-size:14px;vertical-align:middle;">close</span> Remove
                                </button>
                            </div>
                            <div class="field">
                                <label>Username</label>
                                <input type="text" name="users[<?= $role ?>][<?= $idx ?>][username]" value="<?= htmlspecialchars($u['username']) ?>" placeholder="username" required>
                            </div>
                            <div class="field">
                                <label>Password</label>
                                <input type="text" name="users[<?= $role ?>][<?= $idx ?>][password]" value="<?= htmlspecialchars($u['password']) ?>" placeholder="password">
                            </div>
                            <div class="field">
                                <label>Full Name</label>
                                <input type="text" name="users[<?= $role ?>][<?= $idx ?>][full_name]" value="<?= htmlspecialchars($u['full_name']) ?>" placeholder="Full Name" required>
                            </div>
                            <div class="field">
                                <label>Email</label>
                                <input type="email" name="users[<?= $role ?>][<?= $idx ?>][email]" value="<?= htmlspecialchars($u['email']) ?>" placeholder="email@example.com">
                            </div>
                            <div class="field">
                                <label>Phone</label>
                                <input type="text" name="users[<?= $role ?>][<?= $idx ?>][phone]" value="<?= htmlspecialchars($u['phone']) ?>" placeholder="09XX-XXX-XXXX">
                            </div>
                            <div class="field">
                                <label>Status</label>
                                <select name="users[<?= $role ?>][<?= $idx ?>][status]">
                                    <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="pending" <?= $u['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="flagged" <?= $u['status'] === 'flagged' ? 'selected' : '' ?>>Flagged</option>
                                </select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-add" onclick="addUserCard('<?= $role ?>', '<?= $cfg['label'] ?>')">
                        <span class="material-icons">add</span> Add <?= $cfg['label'] ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- SECTION 2: INVENTORY                                              -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header" onclick="toggleSection(this)">
            <span class="material-icons">inventory_2</span>
            <h2>Inventory Items</h2>
            <span class="badge" id="invCountBadge"><?= count($defaultInventory) ?> items</span>
            <span class="material-icons chevron">expand_more</span>
        </div>
        <div class="section-body">
            <div class="inv-table-wrap">
                <table class="inv-table" id="inventoryTable">
                    <thead>
                        <tr>
                            <th style="width:5%">#</th>
                            <th style="width:35%">Item Name</th>
                            <th style="width:15%">Stock</th>
                            <th style="width:15%">Price (₱)</th>
                            <th style="width:20%">Status</th>
                            <th style="width:10%"></th>
                        </tr>
                    </thead>
                    <tbody id="inventoryBody">
                        <?php foreach ($defaultInventory as $idx => $item): ?>
                        <tr>
                            <td style="color:#666; font-size:0.85em;"><?= $idx + 1 ?></td>
                            <td><input type="text" name="inventory[<?= $idx ?>][item_name]" value="<?= htmlspecialchars($item['item_name']) ?>" required></td>
                            <td><input type="number" name="inventory[<?= $idx ?>][stock_count]" value="<?= $item['stock_count'] ?>" min="0"></td>
                            <td><input type="number" step="0.01" name="inventory[<?= $idx ?>][price]" value="<?= $item['price'] ?>" min="0"></td>
                            <td>
                                <select name="inventory[<?= $idx ?>][status]">
                                    <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="out_of_stock" <?= $item['status'] === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                </select>
                            </td>
                            <td><button type="button" class="btn-remove" onclick="removeInvRow(this)"><span class="material-icons" style="font-size:14px;">close</span></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn-add" onclick="addInvRow()">
                <span class="material-icons">add</span> Add Item
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- SECTION 3: ORDER STATUS TOGGLES                                   -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header" onclick="toggleSection(this)">
            <span class="material-icons">receipt_long</span>
            <h2>Order Statuses to Seed</h2>
            <span class="badge" id="orderCountBadge"><?= count($defaultOrderStatuses) ?> statuses</span>
            <span class="material-icons chevron">expand_more</span>
        </div>
        <div class="section-body">
            <p style="font-size:0.85em; color:#8888AA; margin-bottom:16px;">Toggle which order statuses should have sample orders created. Each enabled status generates 1 sample order (cancelled generates 2).</p>
            <div class="toggle-grid" id="orderToggles">
                <?php foreach ($defaultOrderStatuses as $status => $cfg): ?>
                <label class="toggle-item <?= $cfg['enabled'] ? 'active' : '' ?>" id="toggle-<?= $status ?>">
                    <div class="switch">
                        <input type="checkbox" name="order_status[<?= $status ?>]" value="1" <?= $cfg['enabled'] ? 'checked' : '' ?>
                               onchange="this.closest('.toggle-item').classList.toggle('active', this.checked); updateOrderCount();">
                        <span class="slider"></span>
                    </div>
                    <span class="toggle-label"><?= $cfg['label'] ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:14px; display:flex; gap:10px;">
                <button type="button" class="btn-add" style="margin:0;" onclick="toggleAllOrders(true)">
                    <span class="material-icons">check_box</span> Enable All
                </button>
                <button type="button" class="btn-add" style="margin:0; color:#EF5350; border-color:rgba(239,83,80,0.3); background:rgba(239,83,80,0.05);" onclick="toggleAllOrders(false)">
                    <span class="material-icons">check_box_outline_blank</span> Disable All
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- SECTION 4: SYSTEM SETTINGS                                        -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header collapsed" onclick="toggleSection(this)">
            <span class="material-icons">settings</span>
            <h2>System Settings</h2>
            <span class="badge"><?= count($defaultSettings) ?> settings</span>
            <span class="material-icons chevron" style="transform:rotate(-90deg);">expand_more</span>
        </div>
        <div class="section-body collapsed">
            <div class="settings-grid">
                <?php foreach ($defaultSettings as $key => $val): ?>
                <div class="field">
                    <label><?= str_replace('_', ' ', $key) ?></label>
                    <?php if (in_array($val, ['0', '1']) && strpos($key, 'color') === false && $key !== 'max_cancellation' && $key !== 'max_login_attempts' && $key !== 'pending_expiry_days' && $key !== 'low_stock_threshold' && $key !== 'login_lockout_minutes'): ?>
                    <select name="settings[<?= $key ?>]">
                        <option value="1" <?= $val === '1' ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= $val === '0' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                    <?php elseif (strpos($key, 'color') !== false): ?>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="color" name="settings[<?= $key ?>]" value="<?= htmlspecialchars($val) ?>" style="width:42px; height:36px; padding:2px; border-radius:6px; cursor:pointer;">
                        <input type="text" value="<?= htmlspecialchars($val) ?>" style="flex:1;" oninput="this.previousElementSibling.value=this.value" readonly>
                    </div>
                    <?php else: ?>
                    <input type="text" name="settings[<?= $key ?>]" value="<?= htmlspecialchars($val) ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- SUBMIT                                                            -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="btn-submit-wrap">
        <button type="submit" name="create_db" class="btn-submit"
                onclick="return confirm('⚠️ This will DELETE the entire database and recreate it with your configuration.\n\nAre you absolutely sure?');">
            <span class="material-icons">rocket_launch</span>
            CREATE DATABASE &amp; SEED DATA
        </button>
    </div>

    </form>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- FEEDBACK LOG                                                      -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <?php if (!empty($messages)): ?>
    <div class="section">
        <div class="section-header" style="cursor:default;">
            <span class="material-icons"><?= $hasError ? 'error' : 'terminal' ?></span>
            <h2><?= $hasError ? 'Completed with Errors' : 'Execution Log' ?></h2>
            <span class="badge"><?= count($messages) ?> entries</span>
        </div>
        <div class="section-body feedback">
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

    <?php if (!$hasError): ?>
    <form method="POST">
        <button type="submit" name="create_db" class="btn-submit reset"
                onclick="return confirm('Run again? This will DELETE everything and recreate with DEFAULT values.');">
            <span class="material-icons">refresh</span>
            QUICK RESET (Default Values)
        </button>
    </form>
    <?php endif; ?>
    <?php endif; ?>

    <div class="bottom-link">
        <a href="index.php">← Back to Login</a>
    </div>
</div>

<script>
// ── Section collapse/expand ──────────────────────────────────────────────────
function toggleSection(header) {
    const body = header.nextElementSibling;
    const chevron = header.querySelector('.chevron');
    header.classList.toggle('collapsed');
    body.classList.toggle('collapsed');
    chevron.style.transform = header.classList.contains('collapsed') ? 'rotate(-90deg)' : '';
}

// ── Role group collapse/expand ───────────────────────────────────────────────
function toggleRole(header) {
    const body = header.nextElementSibling;
    const chevron = header.querySelector('.chevron');
    const isHidden = body.style.display === 'none';
    body.style.display = isHidden ? '' : 'none';
    chevron.style.transform = isHidden ? '' : 'rotate(-90deg)';
}

// ── Add user card ────────────────────────────────────────────────────────────
let userCounters = {};
function addUserCard(role, roleLabel) {
    const container = document.getElementById('users-' + role);
    const existingCards = container.querySelectorAll('.user-card');
    const idx = Date.now(); // unique index

    const card = document.createElement('div');
    card.className = 'user-card';
    card.setAttribute('data-role', role);
    card.innerHTML = `
        <div class="user-card-header">
            <span>${roleLabel} (new)</span>
            <button type="button" class="btn-remove" onclick="removeUserCard(this)">
                <span class="material-icons" style="font-size:14px;vertical-align:middle;">close</span> Remove
            </button>
        </div>
        <div class="field">
            <label>Username</label>
            <input type="text" name="users[${role}][${idx}][username]" placeholder="username" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="text" name="users[${role}][${idx}][password]" value="12345" placeholder="password">
        </div>
        <div class="field">
            <label>Full Name</label>
            <input type="text" name="users[${role}][${idx}][full_name]" placeholder="Full Name" required>
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="users[${role}][${idx}][email]" placeholder="email@example.com">
        </div>
        <div class="field">
            <label>Phone</label>
            <input type="text" name="users[${role}][${idx}][phone]" placeholder="09XX-XXX-XXXX">
        </div>
        <div class="field">
            <label>Status</label>
            <select name="users[${role}][${idx}][status]">
                <option value="active" selected>Active</option>
                <option value="pending">Pending</option>
                <option value="flagged">Flagged</option>
            </select>
        </div>
    `;
    container.appendChild(card);
    updateUserCount();
    card.querySelector('input').focus();
}

function removeUserCard(btn) {
    const card = btn.closest('.user-card');
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95)';
    card.style.transition = 'all 0.2s';
    setTimeout(() => {
        card.remove();
        updateUserCount();
    }, 200);
}

function updateUserCount() {
    let total = 0;
    document.querySelectorAll('.user-card').forEach(() => total++);
    document.getElementById('userCountBadge').textContent = total + ' users';

    // Per-role counts
    document.querySelectorAll('.role-count').forEach(badge => {
        const role = badge.dataset.role;
        const count = document.querySelectorAll(`#users-${role} .user-card`).length;
        badge.textContent = count;
    });
}

// ── Add inventory row ────────────────────────────────────────────────────────
function addInvRow() {
    const tbody = document.getElementById('inventoryBody');
    const idx = Date.now();
    const rowNum = tbody.rows.length + 1;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td style="color:#666; font-size:0.85em;">${rowNum}</td>
        <td><input type="text" name="inventory[${idx}][item_name]" placeholder="Item name" required></td>
        <td><input type="number" name="inventory[${idx}][stock_count]" value="0" min="0"></td>
        <td><input type="number" step="0.01" name="inventory[${idx}][price]" value="0" min="0"></td>
        <td>
            <select name="inventory[${idx}][status]">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
        </td>
        <td><button type="button" class="btn-remove" onclick="removeInvRow(this)"><span class="material-icons" style="font-size:14px;">close</span></button></td>
    `;
    tbody.appendChild(tr);
    updateInvCount();
    tr.querySelector('input').focus();
}

function removeInvRow(btn) {
    const row = btn.closest('tr');
    row.style.opacity = '0';
    row.style.transition = 'opacity 0.2s';
    setTimeout(() => {
        row.remove();
        renumberInvRows();
        updateInvCount();
    }, 200);
}

function renumberInvRows() {
    const rows = document.querySelectorAll('#inventoryBody tr');
    rows.forEach((row, i) => {
        row.cells[0].textContent = i + 1;
    });
}

function updateInvCount() {
    const count = document.querySelectorAll('#inventoryBody tr').length;
    document.getElementById('invCountBadge').textContent = count + ' items';
}

// ── Order toggles ────────────────────────────────────────────────────────────
function toggleAllOrders(state) {
    document.querySelectorAll('#orderToggles input[type="checkbox"]').forEach(cb => {
        cb.checked = state;
        cb.closest('.toggle-item').classList.toggle('active', state);
    });
    updateOrderCount();
} //

function updateOrderCount() {
    const checked = document.querySelectorAll('#orderToggles input[type="checkbox"]:checked').length;
    document.getElementById('orderCountBadge').textContent = checked + ' enabled';
}

// ── Color picker sync ────────────────────────────────────────────────────────
document.querySelectorAll('input[type="color"]').forEach(picker => {
    const textInput = picker.nextElementSibling;
    picker.addEventListener('input', () => {
        textInput.value = picker.value;
    });
})
</script>
</body>
</html>