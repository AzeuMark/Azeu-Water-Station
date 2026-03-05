<?php
/**
 * Direct Login Test - Bypass JavaScript
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate the API call
    $_POST_DATA = json_encode($_POST);
    
    // Set up for API
    header('Content-Type: application/json');
    
    ob_start();
    
    try {
        require_once 'config/database.php';
        require_once 'config/functions.php';
        require_once 'config/AESCrypt.php';
        
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password required']);
            exit;
        }
        
        // Fetch user
        $user = db_fetch("SELECT * FROM users WHERE username = ? AND deleted_at IS NULL", [$username]);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        // Check password
        $encrypt_passwords = get_setting('encrypt_passwords') == 1;
        $passwordMatch = false;
        
        if ($encrypt_passwords) {
            $decrypted = decrypt($user['password'], ENCRYPTION_KEY);
            $passwordMatch = ($password === $decrypted);
            echo json_encode(['success' => false, 'message' => "Password check failed. Expected: $decrypted, Got: $password"]);
        } else {
            $passwordMatch = ($password === $user['password']);
            if (!$passwordMatch) {
                echo json_encode(['success' => false, 'message' => "Password mismatch. Expected: {$user['password']}, Got: $password"]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Login successful!', 'role' => $user['role']]);
            }
        }
        
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'output' => $output]);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct Login Test</title>
</head>
<body>
    <h2>Direct Login Test</h2>
    <form method="POST">
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="admin" required>
        </div>
        <div>
            <label>Password:</label>
            <input type="text" name="password" value="admin" required>
        </div>
        <button type="submit">Test Login</button>
    </form>
    <hr>
    <p><a href="test_db.php">Test Database</a> | <a href="index.php">Back to Login</a></p>
</body>
</html>
