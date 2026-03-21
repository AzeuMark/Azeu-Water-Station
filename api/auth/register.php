<?php
/**
 * Azeu Water Station - Customer Registration API
 */
header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('auth/register');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/AESCrypt.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$username = sanitize($input['username'] ?? '');
$password = $input['password'] ?? '';
$first_name = sanitize($input['first_name'] ?? '');
$middle_initial = sanitize($input['middle_initial'] ?? '');
$last_name = sanitize($input['last_name'] ?? '');
$full_name = sanitize($input['full_name'] ?? '');
$email = sanitize($input['email'] ?? '');
$phone = sanitize($input['phone'] ?? '');
$address = sanitize($input['address'] ?? '');

// Validate required input fields
if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address)) {
    json_response(['success' => false, 'message' => 'All fields are required (except Middle Initial)'], 400);
}

// Construct full_name if not provided by client (as a fallback)
if (empty($full_name)) {
    $full_name = $first_name;
    if (!empty($middle_initial)) {
        $full_name .= ' ' . $middle_initial . '.';
    }
    $full_name .= ' ' . $last_name;
}

// Validate email
if (!validate_email($email)) {
    json_response(['success' => false, 'message' => 'Invalid email address'], 400);
}

// Validate username (alphanumeric and underscore only)
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    json_response(['success' => false, 'message' => 'Username must be 3-50 characters (letters, numbers, underscore only)'], 400);
}

// Validate password length
if (strlen($password) < 6) {
    json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
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

    // Insert new user with address
    $user_id = db_insert(
        "INSERT INTO users (username, password, full_name, email, phone, address, role, status, cancellation_reset_date)
         VALUES (?, ?, ?, ?, ?, ?, 'customer', 'pending', DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY))",
        [$username, $stored_password, $full_name, $email, $phone, $address]
    );

    if (!$user_id) {
        json_response(['success' => false, 'message' => 'Registration failed. Please try again'], 500);
    }

    // Create user preferences
    db_insert("INSERT INTO user_preferences (user_id, dark_mode) VALUES (?, 0)", [$user_id]);

    // Also create a default customer address entry with this address for delivery purposes
    db_insert(
        "INSERT INTO customer_addresses (customer_id, label, full_address, is_default) VALUES (?, 'Home', ?, 1)",
        [$user_id, $address]
    );

    logger_info("New customer registered", ['user_id' => $user_id, 'username' => $username]);

    // Notify all staff and admins about new registration
    $staff_admins = db_fetch_all("SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'");
    foreach ($staff_admins as $admin) {
        create_notification(
            $admin['id'],
            'New Customer Registration',
            "$full_name ($username) has registered and is pending approval",
            'account_pending',
            $user_id
        );
    }

    json_response([
        'success' => true,
        'message' => 'Registration successful! Your account is pending approval',
        'user_id' => $user_id
    ]);

} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred. Please try again'], 500);
}
