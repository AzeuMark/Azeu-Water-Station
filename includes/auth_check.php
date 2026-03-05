<?php
/**
 * Azeu Water Station - Authentication Check
 * Include this at the top of every protected page
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/request_logger.php';

// Log page access
$currentPage = basename($_SERVER['PHP_SELF']);
log_page_view($currentPage);

// Ensure user is logged in
require_login();

// Check maintenance mode
check_maintenance();
