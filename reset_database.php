<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DATABASE RESET & SEED SCRIPT
 * ============================================================================
 * 
 * Purpose: Completely resets the database and creates test accounts for all roles
 * 
 * ⚠️ WARNING: THIS WILL DELETE ALL DATA!
 * 
 * Test Accounts Created:
 * - Super Admin: username=admin, password=admin
 * - Admin: username=admin2, password=admin123
 * - Staff: username=staff1, password=staff123
 * - Rider: username=rider1, password=rider123
 * - Rider: username=rider2, password=rider123
 * - Customer: username=customer1, password=customer123 (active)
 * - Customer: username=customer2, password=customer123 (pending)
 * - Customer: username=customer3, password=customer123 (flagged)
 * 
 * Usage: Access via browser: http://localhost/Station_A/reset_database.php
 * ============================================================================
 */

// Prevent accidental execution in production
$ALLOW_RESET = true; // Set to false in production!

if (!$ALLOW_RESET) {
    die('<h1>❌ Database reset is disabled</h1><p>Edit reset_database.php and set $ALLOW_RESET = true to enable.</p>');
}

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/AESCrypt.php';

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h1>🔄 Database Reset & Seed Script</h1>";
    echo "<pre>";
    
    // Step 1: Drop database if exists and recreate
    echo "\n=== STEP 1: Dropping and recreating database ===\n";
    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    echo "✓ Dropped database: " . DB_NAME . "\n";
    
    $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Created database: " . DB_NAME . "\n";
    
    $pdo->exec("USE " . DB_NAME);
    echo "✓ Using database: " . DB_NAME . "\n";
    
    // Step 2: Create all tables
    echo "\n=== STEP 2: Creating tables ===\n";
    
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: users\n";
    
    // Table 2: user_preferences
    $pdo->exec("CREATE TABLE user_preferences (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        user_id INT(11) UNIQUE NOT NULL,
        dark_mode TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: user_preferences\n";
    
    // Table 3: customer_addresses
    $pdo->exec("CREATE TABLE customer_addresses (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        customer_id INT(11) NOT NULL,
        label VARCHAR(50) NOT NULL DEFAULT 'Home',
        full_address TEXT NOT NULL,
        is_default TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_customer (customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: customer_addresses\n";
    
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: inventory\n";
    
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
        FOREIGN KEY (customer_id) REFERENCES users(id),
        FOREIGN KEY (rider_id) REFERENCES users(id),
        FOREIGN KEY (cancelled_by) REFERENCES users(id),
        INDEX idx_customer (customer_id),
        INDEX idx_rider (rider_id),
        INDEX idx_status (status),
        INDEX idx_receipt_token (receipt_token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: orders\n";
    
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
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (inventory_id) REFERENCES inventory(id),
        INDEX idx_order (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: order_items\n";
    
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_read (is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: notifications\n";
    
    // Table 8: session_logs
    $pdo->exec("CREATE TABLE session_logs (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        user_id INT(11) NULL,
        username VARCHAR(50) NOT NULL,
        role VARCHAR(20) NOT NULL,
        action ENUM('login','logout','failed_login') NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_user (user_id),
        INDEX idx_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: session_logs\n";
    
    // Table 9: settings
    $pdo->exec("CREATE TABLE settings (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: settings\n";
    
    // Table 10: default_items
    $pdo->exec("CREATE TABLE default_items (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        item_name VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: default_items\n";
    
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
        FOREIGN KEY (customer_id) REFERENCES users(id),
        FOREIGN KEY (reviewed_by) REFERENCES users(id),
        INDEX idx_customer (customer_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: cancellation_appeals\n";
    
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: password_resets\n";
    
    // Table 13: delivery_priority
    $pdo->exec("CREATE TABLE delivery_priority (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        rider_id INT(11) NOT NULL,
        order_id INT(11) NOT NULL,
        priority_order INT(11) NOT NULL DEFAULT 0,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (rider_id) REFERENCES users(id),
        FOREIGN KEY (order_id) REFERENCES orders(id),
        INDEX idx_rider (rider_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created table: delivery_priority\n";
    
    echo "\n✓ All 13 tables created successfully\n";
    
    // Step 3: Seed settings
    echo "\n=== STEP 3: Seeding system settings ===\n";
    
    $settings = [
        ['station_name', 'Azeu Water Station'],
        ['station_address', '123 Main Street, Manila, Philippines'],
        ['max_cancellation', '5'],
        ['pending_expiry_days', '7'],
        ['low_stock_threshold', '10'],
        ['maintenance_mode', '0'],
        ['encrypt_passwords', '1'],
        ['auto_assign_orders', '0'],
        ['timezone', 'Asia/Manila'],
        ['force_dark_mode', '0'],
        ['primary_color', '#1565C0'],
        ['secondary_color', '#1E88E5'],
        ['accent_color', '#42A5F5'],
        ['surface_color', '#F5F7FA'],
        ['max_login_attempts', '10'],
        ['delivery_fee', '50.00'],
        ['login_lockout_minutes', '15']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    echo "✓ Inserted " . count($settings) . " system settings\n";
    
    // Step 4: Seed default items
    echo "\n=== STEP 4: Seeding default item names ===\n";
    
    $defaultItems = [
        '30L Water Refill',
        '20L Water Refill',
        '10L Water Refill',
        '5L Water Refill',
        '1L Bottled Water',
        '500ml Bottled Water',
        'Bleach 1L',
        'Water Dispenser',
        'Water Container 30L',
        'Water Container 20L'
    ];
    
    $stmt = $pdo->prepare("INSERT INTO default_items (item_name) VALUES (?)");
    foreach ($defaultItems as $item) {
        $stmt->execute([$item]);
    }
    echo "✓ Inserted " . count($defaultItems) . " default item names\n";
    
    // Step 5: Create test accounts
    echo "\n=== STEP 5: Creating test accounts ===\n";
    
    $encryptPasswords = true; // Match the setting
    
    function createPassword($plainPassword, $encrypt) {
        if ($encrypt) {
            return encrypt($plainPassword, ENCRYPTION_KEY);
        }
        return $plainPassword;
    }
    
    $users = [
        // Super Admin (system default)
        [
            'username' => 'admin',
            'password' => createPassword('admin', $encryptPasswords),
            'full_name' => 'System Administrator',
            'email' => 'admin@azeu.com',
            'phone' => '09171234567',
            'role' => 'super_admin',
            'status' => 'active'
        ],
        // Admin
        [
            'username' => 'admin2',
            'password' => createPassword('admin123', $encryptPasswords),
            'full_name' => 'John Admin',
            'email' => 'admin2@azeu.com',
            'phone' => '09171234568',
            'role' => 'admin',
            'status' => 'active'
        ],
        // Staff
        [
            'username' => 'staff1',
            'password' => createPassword('staff123', $encryptPasswords),
            'full_name' => 'Maria Staff',
            'email' => 'staff1@azeu.com',
            'phone' => '09171234569',
            'role' => 'staff',
            'status' => 'active'
        ],
        // Riders
        [
            'username' => 'rider1',
            'password' => createPassword('rider123', $encryptPasswords),
            'full_name' => 'Pedro Rider',
            'email' => 'rider1@azeu.com',
            'phone' => '09171234570',
            'role' => 'rider',
            'status' => 'active',
            'is_available' => 1
        ],
        [
            'username' => 'rider2',
            'password' => createPassword('rider123', $encryptPasswords),
            'full_name' => 'Juan Rider',
            'email' => 'rider2@azeu.com',
            'phone' => '09171234571',
            'role' => 'rider',
            'status' => 'active',
            'is_available' => 1
        ],
        // Customers
        [
            'username' => 'customer1',
            'password' => createPassword('customer123', $encryptPasswords),
            'full_name' => 'Anna Customer',
            'email' => 'customer1@azeu.com',
            'phone' => '09171234572',
            'role' => 'customer',
            'status' => 'active'
        ],
        [
            'username' => 'customer2',
            'password' => createPassword('customer123', $encryptPasswords),
            'full_name' => 'Jose Customer',
            'email' => 'customer2@azeu.com',
            'phone' => '09171234573',
            'role' => 'customer',
            'status' => 'pending' // Pending approval
        ],
        [
            'username' => 'customer3',
            'password' => createPassword('customer123', $encryptPasswords),
            'full_name' => 'Rosa Customer',
            'email' => 'customer3@azeu.com',
            'phone' => '09171234574',
            'role' => 'customer',
            'status' => 'flagged', // Flagged due to cancellations
            'cancellation_count' => 5
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role, status, is_available, cancellation_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $userIds = [];
    foreach ($users as $user) {
        $stmt->execute([
            $user['username'],
            $user['password'],
            $user['full_name'],
            $user['email'],
            $user['phone'],
            $user['role'],
            $user['status'],
            $user['is_available'] ?? 1,
            $user['cancellation_count'] ?? 0
        ]);
        $userIds[$user['username']] = $pdo->lastInsertId();
        echo "✓ Created {$user['role']}: {$user['username']} (password: " . ($user['username'] === 'admin' ? 'admin' : substr($user['username'], 0, -1) . '123') . ")\n";
    }
    
    // Create user preferences for all users
    $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, ?)");
    foreach ($userIds as $username => $userId) {
        $stmt->execute([$userId, 0]);
    }
    echo "✓ Created user preferences for all users\n";
    
    echo "\n✓ Total accounts created: " . count($users) . "\n";
    
    // Step 6: Create inventory items
    echo "\n=== STEP 6: Creating inventory items ===\n";
    
    $inventoryItems = [
        ['30L Water Refill', 100, 150.00, 'active'],
        ['20L Water Refill', 80, 100.00, 'active'],
        ['10L Water Refill', 60, 60.00, 'active'],
        ['5L Water Refill', 50, 35.00, 'active'],
        ['1L Bottled Water', 200, 20.00, 'active'],
        ['500ml Bottled Water', 150, 12.00, 'active'],
        ['Bleach 1L', 30, 45.00, 'active'],
        ['Water Dispenser', 5, 2500.00, 'active'],
        ['Water Container 30L', 15, 450.00, 'active'],
        ['Water Container 20L', 20, 350.00, 'active'],
        ['Ice Tube (per pack)', 40, 25.00, 'active'],
        ['Alkaline Water 1L', 0, 35.00, 'out_of_stock'], // Out of stock
        ['Distilled Water 5L', 25, 50.00, 'active']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO inventory (item_name, stock_count, price, status, last_restocked_at) VALUES (?, ?, ?, ?, NOW())");
    
    $inventoryIds = [];
    foreach ($inventoryItems as $item) {
        $stmt->execute([$item[0], $item[1], $item[2], $item[3]]);
        $inventoryIds[] = $pdo->lastInsertId();
        echo "✓ Created item: {$item[0]} (Stock: {$item[1]}, Price: ₱{$item[2]})\n";
    }
    
    echo "\n✓ Total inventory items created: " . count($inventoryItems) . "\n";
    
    // Step 7: Create customer addresses
    echo "\n=== STEP 7: Creating customer addresses ===\n";
    
    $addresses = [
        // Customer 1 addresses
        [$userIds['customer1'], 'Home', '123 Mabini Street, Quezon City, Metro Manila', 1],
        [$userIds['customer1'], 'Office', '456 Roxas Boulevard, Manila City, Metro Manila', 0],
        [$userIds['customer1'], 'Parents House', '789 Rizal Avenue, Caloocan City, Metro Manila', 0],
        // Customer 3 addresses
        [$userIds['customer3'], 'Home', '321 EDSA, Mandaluyong City, Metro Manila', 1]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO customer_addresses (customer_id, label, full_address, is_default) VALUES (?, ?, ?, ?)");
    foreach ($addresses as $address) {
        $stmt->execute($address);
        echo "✓ Created address for customer: {$address[1]} - {$address[2]}\n";
    }
    
    echo "\n✓ Total addresses created: " . count($addresses) . "\n";
    
    // Step 8: Create sample orders
    echo "\n=== STEP 8: Creating sample orders ===\n";
    
    $orders = [
        // Order 1: Pending (customer1)
        [
            'customer_id' => $userIds['customer1'],
            'rider_id' => null,
            'payment_type' => 'cod',
            'delivery_type' => 'delivery',
            'status' => 'pending',
            'delivery_address' => '123 Mabini Street, Quezon City, Metro Manila',
            'order_notes' => 'Please call when you arrive',
            'delivery_fee' => 50.00,
            'subtotal' => 300.00,
            'total_amount' => 350.00,
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[0], '30L Water Refill', null, 150.00, 2, 300.00] // 2x 30L
            ]
        ],
        // Order 2: Confirmed (customer1)
        [
            'customer_id' => $userIds['customer1'],
            'rider_id' => null,
            'payment_type' => 'cod',
            'delivery_type' => 'delivery',
            'status' => 'confirmed',
            'delivery_address' => '123 Mabini Street, Quezon City, Metro Manila',
            'order_notes' => null,
            'delivery_fee' => 50.00,
            'subtotal' => 160.00,
            'total_amount' => 210.00,
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[1], '20L Water Refill', null, 100.00, 1, 100.00],
                [$inventoryIds[2], '10L Water Refill', null, 60.00, 1, 60.00]
            ]
        ],
        // Order 3: Assigned to rider1 (customer1)
        [
            'customer_id' => $userIds['customer1'],
            'rider_id' => $userIds['rider1'],
            'payment_type' => 'cod',
            'delivery_type' => 'delivery',
            'status' => 'assigned',
            'delivery_address' => '456 Roxas Boulevard, Manila City, Metro Manila',
            'order_notes' => 'Leave at the guard',
            'delivery_fee' => 50.00,
            'subtotal' => 240.00,
            'total_amount' => 290.00,
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[4], '1L Bottled Water', null, 20.00, 12, 240.00] // 12x 1L
            ]
        ],
        // Order 4: On Delivery (customer1)
        [
            'customer_id' => $userIds['customer1'],
            'rider_id' => $userIds['rider1'],
            'payment_type' => 'cod',
            'delivery_type' => 'delivery',
            'status' => 'on_delivery',
            'delivery_address' => '123 Mabini Street, Quezon City, Metro Manila',
            'order_notes' => null,
            'delivery_fee' => 50.00,
            'subtotal' => 350.00,
            'total_amount' => 400.00,
            'expected_delivery_date' => date('Y-m-d'),
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[3], '5L Water Refill', null, 35.00, 10, 350.00] // 10x 5L
            ]
        ],
        // Order 5: Delivered (customer1)
        [
            'customer_id' => $userIds['customer1'],
            'rider_id' => $userIds['rider2'],
            'payment_type' => 'cod',
            'delivery_type' => 'delivery',
            'status' => 'delivered',
            'delivery_address' => '123 Mabini Street, Quezon City, Metro Manila',
            'order_notes' => null,
            'delivery_fee' => 50.00,
            'subtotal' => 450.00,
            'total_amount' => 500.00,
            'delivered_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[0], '30L Water Refill', null, 150.00, 3, 450.00] // 3x 30L
            ]
        ],
        // Order 6: Pickup - Ready for Pickup (customer3)
        [
            'customer_id' => $userIds['customer3'],
            'rider_id' => null,
            'payment_type' => 'pickup',
            'delivery_type' => 'pickup',
            'status' => 'ready_for_pickup',
            'delivery_address' => null,
            'order_notes' => 'Will pick up tomorrow morning',
            'delivery_fee' => 0.00,
            'subtotal' => 120.00,
            'total_amount' => 120.00,
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[2], '10L Water Refill', null, 60.00, 2, 120.00] // 2x 10L
            ]
        ],
        // Order 7: Cancelled (customer3)
        [
            'customer_id' => $userIds['customer3'],
            'rider_id' => null,
            'payment_type' => 'cod',
            'delivery_type' => 'delivery',
            'status' => 'cancelled',
            'delivery_address' => '321 EDSA, Mandaluyong City, Metro Manila',
            'order_notes' => null,
            'delivery_fee' => 50.00,
            'subtotal' => 200.00,
            'total_amount' => 250.00,
            'cancellation_reason' => 'Changed my mind',
            'cancelled_by' => $userIds['customer3'],
            'receipt_token' => bin2hex(random_bytes(32)),
            'items' => [
                [$inventoryIds[1], '20L Water Refill', null, 100.00, 2, 200.00]
            ]
        ]
    ];
    
    $orderStmt = $pdo->prepare("INSERT INTO orders (customer_id, rider_id, payment_type, delivery_type, status, delivery_address, order_notes, delivery_fee, subtotal, total_amount, expected_delivery_date, cancellation_reason, cancelled_by, delivered_at, receipt_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, inventory_id, item_name, item_icon, item_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $orderIds = [];
    foreach ($orders as $order) {
        $orderStmt->execute([
            $order['customer_id'],
            $order['rider_id'],
            $order['payment_type'],
            $order['delivery_type'],
            $order['status'],
            $order['delivery_address'],
            $order['order_notes'],
            $order['delivery_fee'],
            $order['subtotal'],
            $order['total_amount'],
            $order['expected_delivery_date'] ?? null,
            $order['cancellation_reason'] ?? null,
            $order['cancelled_by'] ?? null,
            $order['delivered_at'] ?? null,
            $order['receipt_token']
        ]);
        
        $orderId = $pdo->lastInsertId();
        $orderIds[] = $orderId;
        
        // Insert order items
        foreach ($order['items'] as $item) {
            $itemStmt->execute([
                $orderId,
                $item[0], // inventory_id
                $item[1], // item_name
                $item[2], // item_icon
                $item[3], // item_price
                $item[4], // quantity
                $item[5]  // subtotal
            ]);
        }
        
        echo "✓ Created order #{$orderId}: {$order['status']} (₱{$order['total_amount']})\n";
    }
    
    echo "\n✓ Total orders created: " . count($orders) . "\n";
    
    // Step 9: Create delivery priority for riders
    echo "\n=== STEP 9: Creating delivery priority for riders ===\n";
    
    $priorities = [
        // Rider 1 has order 3 (assigned) and order 4 (on_delivery)
        [$userIds['rider1'], $orderIds[3], 1], // Order 4 (on_delivery) - priority 1
        [$userIds['rider1'], $orderIds[2], 2]  // Order 3 (assigned) - priority 2
    ];
    
    $stmt = $pdo->prepare("INSERT INTO delivery_priority (rider_id, order_id, priority_order) VALUES (?, ?, ?)");
    foreach ($priorities as $priority) {
        $stmt->execute($priority);
    }
    echo "✓ Created delivery priorities for riders\n";
    
    // Step 10: Create notifications
    echo "\n=== STEP 10: Creating sample notifications ===\n";
    
    $notifications = [
        // For customer1
        [$userIds['customer1'], 'Order Confirmed', 'Your order #2 has been confirmed and is being prepared.', 'order_confirmed', 2, 0],
        [$userIds['customer1'], 'Rider Assigned', 'A rider has been assigned to your order #3.', 'order_assigned', 3, 0],
        [$userIds['customer1'], 'Order On The Way', 'Your order #4 is on the way! Expected delivery today.', 'order_on_delivery', 4, 0],
        [$userIds['customer1'], 'Order Delivered', 'Your order #5 has been delivered. Thank you!', 'order_delivered', 5, 1],
        
        // For customer2 (pending)
        [$userIds['customer2'], 'Account Pending', 'Your account is pending approval. Please wait for staff confirmation.', 'system', null, 0],
        
        // For customer3 (flagged)
        [$userIds['customer3'], 'Account Flagged', 'Your account has been flagged due to excessive cancellations. Please submit an appeal.', 'account_flagged', null, 0],
        [$userIds['customer3'], 'Order Ready', 'Your order #6 is ready for pickup at the station.', 'ready_for_pickup', 6, 0],
        
        // For rider1
        [$userIds['rider1'], 'New Delivery', 'You have been assigned order #3 for delivery.', 'order_assigned', 3, 0],
        [$userIds['rider1'], 'Delivery In Progress', 'Order #4 is marked as on delivery. Good luck!', 'order_on_delivery', 4, 1],
        
        // For rider2
        [$userIds['rider2'], 'Delivery Completed', 'Thank you for completing order #5!', 'order_delivered', 5, 1],
        
        // For staff1
        [$userIds['staff1'], 'New Order', 'New order #1 received from customer1. Please review.', 'order_placed', 1, 0],
        [$userIds['staff1'], 'Pending Account', 'New customer registration: customer2 (Jose Customer)', 'system', null, 0],
        
        // For admin and admin2
        [$userIds['admin'], 'Low Stock Alert', 'Alkaline Water 1L is out of stock!', 'low_stock', null, 0],
        [$userIds['admin'], 'New Order', 'New order #1 placed by customer1', 'order_placed', 1, 1],
        [$userIds['admin2'], 'System Notice', 'Welcome to Azeu Water Station admin panel!', 'system', null, 0]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, reference_id, is_read) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($notifications as $notif) {
        $stmt->execute($notif);
    }
    
    echo "✓ Created " . count($notifications) . " notifications for users\n";
    
    // Step 11: Create session logs (login history)
    echo "\n=== STEP 11: Creating session logs ===\n";
    
    $sessionLogs = [
        [$userIds['admin'], 'admin', 'super_admin', 'login', '127.0.0.1'],
        [$userIds['admin2'], 'admin2', 'admin', 'login', '127.0.0.1'],
        [$userIds['staff1'], 'staff1', 'staff', 'login', '192.168.1.100'],
        [$userIds['rider1'], 'rider1', 'rider', 'login', '192.168.1.101'],
        [$userIds['rider2'], 'rider2', 'rider', 'login', '192.168.1.102'],
        [$userIds['customer1'], 'customer1', 'customer', 'login', '192.168.1.200'],
        [$userIds['customer1'], 'customer1', 'customer', 'logout', '192.168.1.200'],
        [$userIds['customer1'], 'customer1', 'customer', 'login', '192.168.1.200'],
        [null, 'hacker123', 'unknown', 'failed_login', '1.2.3.4'],
        [null, 'admin', 'unknown', 'failed_login', '5.6.7.8']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO session_logs (user_id, username, role, action, ip_address) VALUES (?, ?, ?, ?, ?)");
    foreach ($sessionLogs as $log) {
        $stmt->execute($log);
    }
    
    echo "✓ Created " . count($sessionLogs) . " session log entries\n";
    
    // Step 12: Create a cancellation appeal
    echo "\n=== STEP 12: Creating cancellation appeal ===\n";
    
    $stmt = $pdo->prepare("INSERT INTO cancellation_appeals (customer_id, reason, status) VALUES (?, ?, ?)");
    $stmt->execute([
        $userIds['customer3'],
        'I apologize for the excessive cancellations. I had a family emergency and needed to cancel my orders. I promise to be more responsible with my orders moving forward.',
        'pending'
    ]);
    
    echo "✓ Created cancellation appeal for customer3\n";
    
    // Final summary
    echo "\n";
    echo "========================================\n";
    echo "✅ DATABASE RESET & SEED COMPLETE!\n";
    echo "========================================\n\n";
    
    echo "📊 SUMMARY:\n";
    echo "  • Database: " . DB_NAME . " ✓\n";
    echo "  • Tables: 13 ✓\n";
    echo "  • Settings: " . count($settings) . " ✓\n";
    echo "  • Default Items: " . count($defaultItems) . " ✓\n";
    echo "  • Users: " . count($users) . " ✓\n";
    echo "  • Inventory: " . count($inventoryItems) . " ✓\n";
    echo "  • Addresses: " . count($addresses) . " ✓\n";
    echo "  • Orders: " . count($orders) . " ✓\n";
    echo "  • Notifications: " . count($notifications) . " ✓\n";
    echo "  • Session Logs: " . count($sessionLogs) . " ✓\n";
    echo "  • Appeals: 1 ✓\n\n";
    
    echo "👥 TEST ACCOUNTS:\n\n";
    echo "  🔴 SUPER ADMIN:\n";
    echo "     Username: admin\n";
    echo "     Password: admin\n\n";
    
    echo "  🔴 ADMIN:\n";
    echo "     Username: admin2\n";
    echo "     Password: admin123\n\n";
    
    echo "  🟡 STAFF:\n";
    echo "     Username: staff1\n";
    echo "     Password: staff123\n\n";
    
    echo "  🟢 RIDERS:\n";
    echo "     Username: rider1\n";
    echo "     Password: rider123\n\n";
    
    echo "     Username: rider2\n";
    echo "     Password: rider123\n\n";
    
    echo "  🔵 CUSTOMERS:\n";
    echo "     Username: customer1 (Active)\n";
    echo "     Password: customer123\n\n";
    
    echo "     Username: customer2 (Pending Approval)\n";
    echo "     Password: customer123\n\n";
    
    echo "     Username: customer3 (Flagged - Has Appeal)\n";
    echo "     Password: customer123\n\n";
    
    echo "📦 SAMPLE DATA:\n";
    echo "  • 13 inventory items (1 out of stock)\n";
    echo "  • 7 orders with different statuses\n";
    echo "  • 4 customer addresses\n";
    echo "  • Notifications for all roles\n";
    echo "  • Login history and failed attempts\n\n";
    
    echo "🎯 NEXT STEPS:\n";
    echo "  1. Go to: http://localhost/Station_A/\n";
    echo "  2. Login with any test account above\n";
    echo "  3. Explore the system features!\n\n";
    
    echo "⚠️  To reset again, simply refresh this page.\n\n";
    
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "</pre>";
    echo "<h2 style='color: red;'>❌ Database Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    die();
}
