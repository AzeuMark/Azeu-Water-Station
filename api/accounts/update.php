<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPDATE ACCOUNT API
 * ============================================================================
 * 
 * Purpose: Update account details
 * Method: POST
 * Role: STAFF, ADMIN (or own account)
 * 
 * Request Body (JSON):
 * {
 *   "user_id": 123,
 *   "full_name": "Updated Name",
 *   "email": "newemail@example.com",
 *   "phone": "9876543210",
 *   "password": "newpassword" (optional)
 * }
 * 
 * Notes:
 * - Users can update their own account
 * - Staff/Admin can update any account
 * - Password is optional
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Account updated"
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('accounts/update');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/AESCrypt.php';

// Auth check
require_login();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$user_id = intval($input['user_id'] ?? 0);
$full_name = sanitize($input['full_name'] ?? '');
$username = sanitize($input['username'] ?? '');
$email = sanitize($input['email'] ?? '');
$phone = sanitize($input['phone'] ?? '');
$password = $input['password'] ?? '';

if ($user_id <= 0) {
    json_response(['success' => false, 'message' => 'User ID is required'], 400);
}

try {
    $current_user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Check permissions
    $can_update = in_array($role, [ROLE_STAFF, ROLE_ADMIN, ROLE_SUPER_ADMIN]) || $user_id == $current_user_id;
    
    if (!$can_update) {
        json_response(['success' => false, 'message' => 'Access denied'], 403);
    }
    
    // Verify user exists
    $user = db_fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$user_id]);
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // SECURITY RULE: Prevent editing super_admin accounts (except by super_admin themselves in settings)
    if ($user['role'] === 'super_admin' && $current_user_id != $user_id) {
        json_response(['success' => false, 'message' => 'Super admin accounts can only be edited in super admin settings'], 403);
    }
    
    // SECURITY RULE: Admins cannot edit other admin accounts (only super_admin can)
    if ($user['role'] === 'admin' && $role === 'admin' && $current_user_id != $user_id) {
        json_response(['success' => false, 'message' => 'Admins cannot edit other admin accounts'], 403);
    }
    
    // SECURITY RULE: Staff cannot edit super_admin, admin, or other staff accounts
    if ($role === 'staff' && in_array($user['role'], ['super_admin', 'admin', 'staff'])) {
        json_response(['success' => false, 'message' => 'Staff cannot edit admin or staff accounts'], 403);
    }
    
    // Build update query
    $update_fields = [];
    $update_params = [];
    
    if (!empty($full_name)) {
        $update_fields[] = "full_name = ?";
        $update_params[] = $full_name;
    }
    
    if (!empty($username)) {
        // Check if username is already taken by another user
        $existing = db_fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $user_id]);
        if ($existing) {
            json_response(['success' => false, 'message' => 'Username already in use'], 409);
        }
        
        $update_fields[] = "username = ?";
        $update_params[] = $username;
    }
    
    if (!empty($email)) {
        // Validate email
        if (!validate_email($email)) {
            json_response(['success' => false, 'message' => 'Invalid email address'], 400);
        }
        
        // Check if email is already taken by another user
        $existing = db_fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
        if ($existing) {
            json_response(['success' => false, 'message' => 'Email already in use'], 409);
        }
        
        $update_fields[] = "email = ?";
        $update_params[] = $email;
    }
    
    if (!empty($phone)) {
        $update_fields[] = "phone = ?";
        $update_params[] = $phone;
    }
    
    if (!empty($password)) {
        // Encrypt password if setting is enabled
        $encrypt_passwords = get_setting('encrypt_passwords') == 1;
        $stored_password = $encrypt_passwords ? encrypt($password, ENCRYPTION_KEY) : $password;
        
        $update_fields[] = "password = ?";
        $update_params[] = $stored_password;
    }
    
    if (empty($update_fields)) {
        json_response(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    // Add user_id to params
    $update_params[] = $user_id;
    
    // Update user
    $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    db_update($sql, $update_params);
    
    logger_info("Account updated", [
        'user_id' => $user_id,
        'updated_by' => $current_user_id,
        'fields' => array_keys($input)
    ]);
    
    json_response([
        'success' => true,
        'message' => 'Account updated successfully'
    ]);
    
} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
