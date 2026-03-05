<?php
/**
 * Azeu Water Station - Reset Password API
 */
header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('auth/reset_password');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/AESCrypt.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$token = sanitize($input['token'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($token) || empty($password)) {
    json_response(['success' => false, 'message' => 'Token and password are required'], 400);
}

// Validate password length
if (strlen($password) < 6) {
    json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
}

try {
    // Verify token
    $reset = db_fetch("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()", [$token]);
    
    if (!$reset) {
        json_response(['success' => false, 'message' => 'Invalid or expired reset token'], 400);
    }
    
    // Get user
    $user = db_fetch("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL", [$reset['email']]);
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Encrypt password if setting is enabled
    $encrypt_passwords = get_setting('encrypt_passwords') == 1;
    $stored_password = $encrypt_passwords ? encrypt($password, ENCRYPTION_KEY) : $password;
    
    // Update password
    db_update("UPDATE users SET password = ?, login_attempts = 0, login_locked_until = NULL WHERE id = ?",
        [$stored_password, $user['id']]);
    
    // Mark token as used
    db_update("UPDATE password_resets SET used = 1 WHERE id = ?", [$reset['id']]);
    
    logger_info("Password reset successful", ['user_id' => $user['id']]);
    
    json_response(['success' => true, 'message' => 'Password reset successful']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred. Please try again'], 500);
}
