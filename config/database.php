<?php
/**
 * Azeu Water Station - Database Connection & Setup
 * Auto-creates all tables and seeds initial data
 */

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/AESCrypt.php';

// Global PDO connection
global $pdo;

try {
    // Connect to MySQL
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    logger_info("Database connection established");
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    logger_info("Using database: " . DB_NAME);
    
} catch (PDOException $e) {
    // Try to log if logger is available
    if (function_exists('logger_critical')) {
        logger_critical("Database connection failed: " . $e->getMessage());
    }

    // Show user-friendly error page
    http_response_code(503);
    $errorMessage = DEBUG_MODE ? htmlspecialchars($e->getMessage()) : 'Unable to connect to the database server.';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                color: #fff;
                padding: 20px;
            }
            .error-container {
                max-width: 500px;
                text-align: center;
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 50px 40px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            }
            .error-icon {
                width: 100px;
                height: 100px;
                background: linear-gradient(135deg, #ef5350, #f44336);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 30px;
                box-shadow: 0 10px 30px rgba(244, 67, 54, 0.3);
            }
            .error-icon .material-icons {
                font-size: 50px;
                color: #fff;
            }
            h1 {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 15px;
                color: #fff;
            }
            .error-subtitle {
                font-size: 1rem;
                color: rgba(255, 255, 255, 0.7);
                margin-bottom: 30px;
                line-height: 1.6;
            }
            .error-details {
                background: rgba(0, 0, 0, 0.3);
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 30px;
                text-align: left;
            }
            .error-details h3 {
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #ef5350;
                margin-bottom: 15px;
            }
            .error-details ul {
                list-style: none;
                font-size: 0.9rem;
                color: rgba(255, 255, 255, 0.8);
            }
            .error-details li {
                padding: 8px 0;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .error-details li .material-icons {
                font-size: 18px;
                color: #ffa726;
            }
            .technical-error {
                background: rgba(239, 83, 80, 0.1);
                border: 1px solid rgba(239, 83, 80, 0.3);
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 25px;
                font-family: monospace;
                font-size: 0.8rem;
                color: #ef9a9a;
                word-break: break-word;
                text-align: left;
            }
            .btn-retry {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: linear-gradient(135deg, #1565C0, #1E88E5);
                color: #fff;
                padding: 14px 30px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.95rem;
                transition: all 0.3s ease;
                box-shadow: 0 5px 20px rgba(21, 101, 192, 0.3);
            }
            .btn-retry:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(21, 101, 192, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <span class="material-icons">storage</span>
            </div>
            <h1>Database Connection Failed</h1>
            <p class="error-subtitle">
                The website cannot load because it failed to connect to the database server.
                This is usually a temporary issue.
            </p>

            <div class="error-details">
                <h3>Please check the following:</h3>
                <ul>
                    <li><span class="material-icons">check_circle</span> MySQL service is running (XAMPP Control Panel)</li>
                    <li><span class="material-icons">check_circle</span> Database credentials in config/constants.php</li>
                    <li><span class="material-icons">check_circle</span> MySQL port is not blocked (default: 3306)</li>
                </ul>
            </div>

            <?php if (DEBUG_MODE): ?>
            <div class="technical-error">
                <strong>Technical Details:</strong><br>
                <?php echo $errorMessage; ?>
            </div>
            <?php endif; ?>

            <a href="javascript:location.reload()" class="btn-retry">
                <span class="material-icons">refresh</span>
                Try Again
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Create all tables
create_all_tables();

// Run schema migrations for existing installations
run_migrations();

// Seed initial data
seed_initial_data();

/**
 * Create all database tables
 */
function create_all_tables() {
    global $pdo;
    
    // Table 1: users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password TEXT NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NULL,
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
    logger_debug("Table 'users' checked/created");
    
    // Table 2: user_preferences
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_preferences (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        user_id INT(11) UNIQUE NOT NULL,
        dark_mode TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        CONSTRAINT fk_user_pref FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    logger_debug("Table 'user_preferences' checked/created");
    
    // Table 3: customer_addresses
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
    logger_debug("Table 'customer_addresses' checked/created");
    
    // Table 4: inventory
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
    logger_debug("Table 'inventory' checked/created");
    
    // Table 5: orders
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        customer_id INT(11) NOT NULL,
        rider_id INT(11) NULL,
        payment_type ENUM('cod','pickup','online') NOT NULL,
        delivery_type ENUM('delivery','pickup') NOT NULL,
        status ENUM('pending','confirmed','assigned','reassign_requested','on_delivery','delivered','accepted','ready_for_pickup','picked_up','cancelled') NOT NULL DEFAULT 'pending',
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
    logger_debug("Table 'orders' checked/created");
    
    // Migrate: add 'reassign_requested' to orders.status ENUM if not present
    try {
        $col = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
        if ($col && strpos($col['Type'], 'reassign_requested') === false) {
            $pdo->exec("ALTER TABLE orders MODIFY status ENUM('pending','confirmed','assigned','reassign_requested','on_delivery','delivered','accepted','ready_for_pickup','picked_up','cancelled') NOT NULL DEFAULT 'pending'");
            logger_debug("Migrated orders.status ENUM to include reassign_requested");
        }
    } catch (Exception $e) {
        logger_warning("Could not migrate orders.status ENUM: " . $e->getMessage());
    }
    
    // Table 6: order_items
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
    logger_debug("Table 'order_items' checked/created");
    
    // Table 7: notifications
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
    logger_debug("Table 'notifications' checked/created");
    
    // Table 8: session_logs
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
    logger_debug("Table 'session_logs' checked/created");
    
    // Table 9: settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    logger_debug("Table 'settings' checked/created");
    
    // Table 10: default_items
    $pdo->exec("CREATE TABLE IF NOT EXISTS default_items (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        item_name VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    logger_debug("Table 'default_items' checked/created");
    
    // Table 11: cancellation_appeals
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
    logger_debug("Table 'cancellation_appeals' checked/created");
    
    // Table 12: password_resets
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
    logger_debug("Table 'password_resets' checked/created");
    
    // Table 13: delivery_priority
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
    logger_debug("Table 'delivery_priority' checked/created");
    
    logger_info("All tables created/verified successfully");
}

/**
 * Seed initial data
 */
function seed_initial_data() {
    global $pdo;
    
    // Seed settings
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $defaultSettings = [
            'station_name' => 'Azeu Water Station',
            'station_address' => '',
            'max_cancellation' => '5',
            'pending_expiry_days' => '7',
            'low_stock_threshold' => '10',
            'maintenance_mode' => '0',
            'encrypt_passwords' => '1',
            'auto_assign_orders' => '0',
            'timezone' => 'Asia/Manila',
            'force_dark_mode' => '0',
            'primary_color' => '#1565C0',
            'secondary_color' => '#1E88E5',
            'accent_color' => '#42A5F5',
            'surface_color' => '#F5F7FA',
            'max_login_attempts' => '10',
            'delivery_fee' => '50.00',
            'login_lockout_minutes' => '15'
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaultSettings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        logger_info("Default settings seeded");
    }
    
    // Seed super admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'");
    if ($stmt->fetchColumn() == 0) {
        // Check if encryption is enabled
        $encryptPasswordsSetting = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'encrypt_passwords'")->fetchColumn();
        $encryptPasswords = $encryptPasswordsSetting == '1';
        
        $password = 'admin';
        if ($encryptPasswords) {
            $password = encrypt($password, ENCRYPTION_KEY);
        }
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $password, 'System Administrator', 'admin@azeu.com', '0000000000', 'super_admin', 'active']);
        
        $adminId = $pdo->lastInsertId();
        
        // Create user preferences for super admin
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, ?)");
        $stmt->execute([$adminId, 0]);
        
        logger_info("Super admin account seeded (username: admin, password: admin)");
    }
    
    // Seed default items
    $stmt = $pdo->query("SELECT COUNT(*) FROM default_items");
    if ($stmt->fetchColumn() == 0) {
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
        logger_info("Default item names seeded");
    }
}

/**
 * Run schema migrations for existing installations
 */
function run_migrations() {
    global $pdo;

    // Add flag_reason column to users if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN flag_reason TEXT NULL AFTER status");
        logger_info("Migration: added flag_reason column to users");
    } catch (PDOException $e) {
        // Column already exists — safe to ignore
    }

    // Add address column to users if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT NULL AFTER phone");
        logger_info("Migration: added address column to users");
    } catch (PDOException $e) {
        // Column already exists — safe to ignore
    }
}

/**
 * Execute a query with logging
 */
function db_query($sql, $params = []) {
    global $pdo;
    
    $start = microtime(true);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $duration = round((microtime(true) - $start) * 1000, 2);
        logger_query($sql, $params, $duration);
        
        return $stmt;
    } catch (PDOException $e) {
        logger_error("Database query failed: " . $e->getMessage(), ['sql' => $sql]);
        return false;
    }
}

/**
 * Fetch single row
 */
function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
}

/**
 * Fetch all rows
 */
function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
}

/**
 * Insert and return last insert ID
 */
function db_insert($sql, $params = []) {
    global $pdo;
    $stmt = db_query($sql, $params);
    return $stmt ? $pdo->lastInsertId() : false;
}

/**
 * Update and return affected rows
 */
function db_update($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->rowCount() : false;
}

/**
 * Delete and return affected rows
 */
function db_delete($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->rowCount() : false;
}
