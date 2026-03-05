<?php
/**
 * Azeu Water Station - Login API
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/AESCrypt.php';
require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('auth/login');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$username = sanitize($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    json_response(['success' => false, 'message' => 'Username and password are required'], 400);
}

try {
    // Fetch user
    $user = db_fetch("SELECT * FROM users WHERE username = ? AND deleted_at IS NULL", [$username]);
    
    if (!$user) {
        // Log failed login attempt
        db_insert("INSERT INTO session_logs (username, role, action, ip_address) VALUES (?, ?, ?, ?)",
            [$username, 'unknown', 'failed_login', get_client_ip()]);
        
        logger_warning("Failed login attempt for username: $username");
        json_response(['success' => false, 'message' => 'Invalid username or password'], 401);
    }
    
    // Check if account is locked
    if ($user['login_locked_until'] && strtotime($user['login_locked_until']) > time()) {
        $lockTime = date('h:i A', strtotime($user['login_locked_until']));
        json_response(['success' => false, 'message' => "Account is locked. Try again at $lockTime"], 403);
    }
    
    // Reset lock if expired
    if ($user['login_locked_until'] && strtotime($user['login_locked_until']) <= time()) {
        db_update("UPDATE users SET login_attempts = 0, login_locked_until = NULL WHERE id = ?", [$user['id']]);
        $user['login_attempts'] = 0;
    }
    
    // Check password
    $encrypt_passwords = get_setting('encrypt_passwords') == 1;
    $passwordMatch = false;
    
    if ($encrypt_passwords) {
        $decrypted = decrypt($user['password'], ENCRYPTION_KEY);
        $passwordMatch = ($password === $decrypted);
    } else {
        $passwordMatch = ($password === $user['password']);
    }
    
    if (!$passwordMatch) {
        // Increment login attempts
        $attempts = $user['login_attempts'] + 1;
        $maxAttempts = get_setting('max_login_attempts') ?? 10;
        
        if ($attempts >= $maxAttempts) {
            $lockoutMinutes = get_setting('login_lockout_minutes') ?? 15;
            $lockUntil = date('Y-m-d H:i:s', strtotime("+$lockoutMinutes minutes"));
            db_update("UPDATE users SET login_attempts = ?, login_locked_until = ? WHERE id = ?",
                [$attempts, $lockUntil, $user['id']]);
            
            logger_warning("Account locked due to multiple failed attempts", ['user_id' => $user['id']]);
            json_response(['success' => false, 'message' => "Too many failed attempts. Account locked for $lockoutMinutes minutes"], 403);
        } else {
            db_update("UPDATE users SET login_attempts = ? WHERE id = ?", [$attempts, $user['id']]);
        }
        
        // Log failed attempt
        db_insert("INSERT INTO session_logs (user_id, username, role, action, ip_address) VALUES (?, ?, ?, ?, ?)",
            [$user['id'], $username, $user['role'], 'failed_login', get_client_ip()]);
        
        logger_warning("Failed login attempt", ['user_id' => $user['id'], 'attempts' => $attempts]);
        json_response(['success' => false, 'message' => 'Invalid username or password'], 401);
    }
    
    // Check account status
    if ($user['status'] === 'pending') {
        json_response(['success' => false, 'message' => 'Your account is pending approval'], 403);
    }
    
    if ($user['status'] === 'flagged') {
        json_response(['success' => false, 'message' => 'Your account has been flagged. Please contact support'], 403);
    }
    
    if ($user['status'] === 'deleted') {
        json_response(['success' => false, 'message' => 'Account not found'], 404);
    }
    
    // Reset login attempts on successful login
    db_update("UPDATE users SET login_attempts = 0, login_locked_until = NULL WHERE id = ?", [$user['id']]);
    
    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['phone'] = $user['phone'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['status'] = $user['status'];
    
    // Log successful login
    db_insert("INSERT INTO session_logs (user_id, username, role, action, ip_address) VALUES (?, ?, ?, ?, ?)",
        [$user['id'], $username, $user['role'], 'login', get_client_ip()]);
    
    logger_info("User logged in successfully", ['user_id' => $user['id'], 'role' => $user['role']]);
    
    json_response([
        'success' => true,
        'message' => 'Login successful',
        'role' => $user['role'],
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred. Please try again'], 500);
}
