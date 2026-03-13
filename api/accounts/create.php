<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CREATE ACCOUNT API
 * ============================================================================
 * 
 * Purpose: Create new account (staff/rider/admin) - Admin only
 * Method: POST
 * Role: ADMIN, SUPER_ADMIN
 * 
 * Request Body (JSON):
 * {
 *   "username": "newuser",
 *   "password": "password123",
 *   "full_name": "John Doe",
 *   "email": "john@example.com",
 *   "phone": "1234567890",
 *   "role": "staff" | "rider" | "admin"
 * }
 * 
 * Notes:
 * - Only admins can create staff/rider/admin accounts
 * - Super admin can create other super admins
 * - Account is active by default
 * 
 * Response:
 * {
 *   "success": true,
 *   "user_id": 123
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('accounts/create');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/AESCrypt.php';

// Auth check
require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$username = sanitize($input['username'] ?? '');
$password = $input['password'] ?? '';
$full_name = sanitize($input['full_name'] ?? '');
$email = sanitize($input['email'] ?? '');
$phone = sanitize($input['phone'] ?? '');
$role = sanitize($input['role'] ?? '');

// Validate input
if (empty($username) || empty($password) || empty($full_name) || empty($email) || empty($phone) || empty($role)) {
    json_response(['success' => false, 'message' => 'All fields are required'], 400);
}

// Validate email
if (!validate_email($email)) {
    json_response(['success' => false, 'message' => 'Invalid email address'], 400);
}

// Validate role
$allowed_roles = [ROLE_CUSTOMER, ROLE_STAFF, ROLE_RIDER, ROLE_ADMIN];

// Only super admin can create super admin
if ($_SESSION['role'] === ROLE_SUPER_ADMIN) {
    $allowed_roles[] = ROLE_SUPER_ADMIN;
}

if (!in_array($role, $allowed_roles)) {
    json_response(['success' => false, 'message' => 'Invalid role'], 400);
}

try {
    // Check if username exists
    $existing = db_fetch("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existing) {
        json_response(['success' => false, 'message' => 'Username already exists'], 409);
    }
    
    // Check if email exists
    $existing = db_fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        json_response(['success' => false, 'message' => 'Email already registered'], 409);
    }
    
    // Encrypt password if setting is enabled
    $encrypt_passwords = get_setting('encrypt_passwords') == 1;
    $stored_password = $encrypt_passwords ? encrypt($password, ENCRYPTION_KEY) : $password;
    
    // Set cancellation reset date for customers (N/A for staff/rider/admin)
    $reset_date = ($role === ROLE_CUSTOMER) ? "DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)" : "NULL";
    
    // Insert new user
    $user_id = db_insert(
        "INSERT INTO users (username, password, full_name, email, phone, role, status, cancellation_reset_date) 
         VALUES (?, ?, ?, ?, ?, ?, 'active', $reset_date)",
        [$username, $stored_password, $full_name, $email, $phone, $role]
    );
    
    if (!$user_id) {
        json_response(['success' => false, 'message' => 'Failed to create account'], 500);
    }
    
    // Create user preferences
    db_insert("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, 0)", [$user_id]);
    
    logger_info("Account created by admin", [
        'new_user_id' => $user_id,
        'username' => $username,
        'role' => $role,
        'created_by' => $_SESSION['user_id']
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Account created successfully',
        'user_id' => $user_id
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
