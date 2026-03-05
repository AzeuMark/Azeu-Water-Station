<?php
/**
 * Database Test - Check if DB is working
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

require_once 'config/database.php';

echo "<p>✅ Database file loaded</p>";

if (isset($pdo)) {
    echo "<p>✅ PDO connection exists</p>";
    
    // Check if users table exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        echo "<p>✅ Users table exists with $count users</p>";
        
        // Check for admin user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "<p>✅ Admin user found</p>";
            echo "<pre>";
            print_r([
                'id' => $admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role'],
                'status' => $admin['status'],
                'password_encrypted' => substr($admin['password'], 0, 20) . '...'
            ]);
            echo "</pre>";
            
            // Check encrypt_passwords setting
            $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'encrypt_passwords'");
            $encryptSetting = $stmt->fetchColumn();
            echo "<p>Encrypt passwords setting: " . ($encryptSetting == '1' ? 'YES' : 'NO') . "</p>";
            
        } else {
            echo "<p>❌ Admin user NOT found</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ No PDO connection</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to Login</a></p>";
