<?php
/**
 * Azeu Water Station - Forgot Password API
 * Note: Email sending requires PHPMailer to be configured
 */
header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('auth/forgot_password');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$email = sanitize($input['email'] ?? '');

// Validate input
if (empty($email)) {
    json_response(['success' => false, 'message' => 'Email is required'], 400);
}

if (!validate_email($email)) {
    json_response(['success' => false, 'message' => 'Invalid email address'], 400);
}

try {
    // Check if email exists and belongs to a customer
    $user = db_fetch("SELECT * FROM users WHERE email = ? AND role = 'customer' AND deleted_at IS NULL", [$email]);
    
    // Always return success to prevent email enumeration
    if (!$user) {
        logger_info("Password reset requested for non-existent email", ['email' => $email]);
        json_response(['success' => true, 'message' => 'If the email exists, a reset link has been sent']);
    }
    
    // Generate reset token
    $token = generate_token(64);
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store reset token
    db_insert("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)",
        [$email, $token, $expires_at]);
    
    // Generate reset link
    $reset_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] 
                . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/reset_password.php?token=' . $token;
    
    // TODO: Send email using PHPMailer
    // For now, log the reset link (in production, this should send an actual email)
    logger_info("Password reset link generated", [
        'user_id' => $user['id'],
        'reset_link' => $reset_link
    ]);
    
    // In development, you can display the link in the response (REMOVE IN PRODUCTION!)
    if (DEBUG_MODE) {
        json_response([
            'success' => true,
            'message' => 'Password reset link generated',
            'reset_link' => $reset_link // REMOVE THIS IN PRODUCTION
        ]);
    }
    
    json_response(['success' => true, 'message' => 'If the email exists, a reset link has been sent']);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred. Please try again'], 500);
}
