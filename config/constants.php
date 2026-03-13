<?php
/**
 * Azeu Water Station - System Constants
 * All system-wide constants, enums, and configuration values
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'station_azeu');
define('DB_USER', 'root');
define('DB_PASS', '');

// Encryption
define('ENCRYPTION_KEY', 'azeu_water_station_2025_key');

// Debugging & Logging
define('DEBUG_MODE', true);  // Set to false in production
define('LOG_FILE_PATH', __DIR__ . '/../logs/log.txt');
define('ERROR_LOG_FILE_PATH', __DIR__ . '/../logs/error.txt');
define('LOG_MAX_SIZE', 10485760);  // 10MB in bytes
define('LOG_DATE_FORMAT', 'Y-m-d H:i:s');
define('LOG_TIMEZONE', 'Asia/Manila');

// Log Levels
define('LOG_LEVEL_TRACE', 0);
define('LOG_LEVEL_DEBUG', 1);
define('LOG_LEVEL_INFO', 2);
define('LOG_LEVEL_WARNING', 3);
define('LOG_LEVEL_ERROR', 4);
define('LOG_LEVEL_CRITICAL', 5);

// Roles
define('ROLE_CUSTOMER', 'customer');
define('ROLE_RIDER', 'rider');
define('ROLE_STAFF', 'staff');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// Order Statuses
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_ASSIGNED', 'assigned');
define('STATUS_REASSIGN_REQUESTED', 'reassign_requested');
define('STATUS_ON_DELIVERY', 'on_delivery');
define('STATUS_DELIVERED', 'delivered');
define('STATUS_ACCEPTED', 'accepted');
define('STATUS_READY_FOR_PICKUP', 'ready_for_pickup');
define('STATUS_PICKED_UP', 'picked_up');
define('STATUS_CANCELLED', 'cancelled');

// Account Statuses
define('ACCOUNT_PENDING', 'pending');
define('ACCOUNT_ACTIVE', 'active');
define('ACCOUNT_FLAGGED', 'flagged');
define('ACCOUNT_DELETED', 'deleted');

// Payment Types
define('PAY_COD', 'cod');
define('PAY_PICKUP', 'pickup');
define('PAY_ONLINE', 'online');

// Delivery Types
define('DEL_DELIVERY', 'delivery');
define('DEL_PICKUP', 'pickup');

// Inventory Statuses
define('INV_ACTIVE', 'active');
define('INV_INACTIVE', 'inactive');
define('INV_OUT_OF_STOCK', 'out_of_stock');
define('INV_LOW_STOCK', 'low_stock');

// Pagination
define('ITEMS_PER_PAGE', 50);
define('ORDER_ITEMS_PER_PAGE', 20);

// CSRF Token
define('CSRF_TOKEN_NAME', 'csrf_token');
