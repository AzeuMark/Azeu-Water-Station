<?php
/**
 * Azeu Water Station - Logout API
 */
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('auth/logout');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

if (isset($_SESSION['user_id'])) {
    // Log logout
    db_insert("INSERT INTO session_logs (user_id, username, role, action, ip_address) VALUES (?, ?, ?, ?, ?)",
        [$_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], 'logout', get_client_ip()]);
    
    logger_info("User logged out", ['user_id' => $_SESSION['user_id']]);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: ../../login.php');
exit;
