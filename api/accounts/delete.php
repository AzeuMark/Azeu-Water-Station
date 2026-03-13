<?php
/**
 * ============================================================================
 * AZEU WATER STATION - DELETE ACCOUNT API
 * ============================================================================
 *
 * Purpose: Soft-delete a user account (sets deleted_at + status = deleted)
 * Method: DELETE
 * Role: STAFF (customer/rider only), ADMIN (not super_admin), SUPER_ADMIN (all except self)
 *
 * Request Body (JSON):
 * {
 *   "user_id": 123,
 *   "csrf_token": "..."
 * }
 *
 * Response:
 * {
 *   "success": true,
 *   "message": "Account deleted successfully"
 * }
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();

$input = json_decode(file_get_contents('php://input'), true);
$user_id = intval($input['user_id'] ?? 0);

if ($user_id <= 0) {
    json_response(['success' => false, 'message' => 'User ID is required'], 400);
}

try {
    $current_user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Cannot delete your own account
    if ($user_id === $current_user_id) {
        json_response(['success' => false, 'message' => 'You cannot delete your own account'], 403);
    }

    // Fetch target user
    $user = db_fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$user_id]);

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    $target_role = $user['role'];

    // Super admin can never be deleted
    if ($target_role === 'super_admin') {
        json_response(['success' => false, 'message' => 'Super admin accounts cannot be deleted'], 403);
    }

    // Staff can only delete customer and rider accounts
    if ($role === 'staff' && !in_array($target_role, ['customer', 'rider'])) {
        json_response(['success' => false, 'message' => 'Staff can only delete customer and rider accounts'], 403);
    }

    // Admin cannot delete other admin accounts (only super_admin can)
    if ($role === 'admin' && $target_role === 'admin') {
        json_response(['success' => false, 'message' => 'Admins cannot delete other admin accounts'], 403);
    }

    // Perform soft-delete
    db_update(
        "UPDATE users SET status = 'deleted', deleted_at = NOW() WHERE id = ?",
        [$user_id]
    );

    logger_info("Account deleted (soft)", [
        'target_user_id' => $user_id,
        'target_role'    => $target_role,
        'deleted_by'     => $current_user_id,
        'deleted_by_role'=> $role
    ]);

    json_response(['success' => true, 'message' => 'Account deleted successfully']);

} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred'], 500);
}
