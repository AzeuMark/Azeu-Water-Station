<?php
/**
 * Azeu Water Station - Request/Response Logger
 * Automatically logs all incoming requests and outgoing responses
 */

require_once __DIR__ . '/logger.php';

/**
 * Log incoming request details
 */
function log_request_start() {
    $requestData = [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'script' => basename($_SERVER['PHP_SELF'] ?? ''),
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referer' => $_SERVER['HTTP_REFERER'] ?? '',
    ];
    
    // Log POST data (excluding files)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postData = $_POST;
        if (!empty($postData)) {
            $requestData['post_data'] = filter_sensitive_data($postData);
        }
        
        // Check for JSON payload
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput) && isJson($rawInput)) {
            $jsonData = json_decode($rawInput, true);
            if ($jsonData) {
                $requestData['json_payload'] = filter_sensitive_data($jsonData);
            }
        }
    }
    
    // Log GET parameters
    if (!empty($_GET)) {
        $requestData['get_params'] = filter_sensitive_data($_GET);
    }
    
    logger_info("REQUEST STARTED", $requestData);
    
    // Store start time for duration calculation
    $GLOBALS['request_start_time'] = microtime(true);
}

/**
 * Log response
 */
function log_response($statusCode = 200, $responseData = null) {
    $duration = isset($GLOBALS['request_start_time']) 
        ? round((microtime(true) - $GLOBALS['request_start_time']) * 1000, 2) 
        : 0;
    
    $logData = [
        'status_code' => $statusCode,
        'duration_ms' => $duration,
    ];
    
    if ($responseData !== null) {
        if (is_string($responseData) && isJson($responseData)) {
            $logData['response'] = filter_sensitive_data(json_decode($responseData, true));
        } else if (is_array($responseData) || is_object($responseData)) {
            $logData['response'] = filter_sensitive_data($responseData);
        }
    }
    
    logger_info("RESPONSE SENT", $logData);
}

/**
 * Check if string is valid JSON
 */
function isJson($string) {
    if (!is_string($string)) return false;
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Log page view (for frontend pages)
 */
function log_page_view($pageName) {
    logger_info("PAGE VIEW: {$pageName}", [
        'session_active' => session_status() === PHP_SESSION_ACTIVE,
        'logged_in' => isset($_SESSION['user_id']),
    ]);
}

/**
 * Log API endpoint entry
 */
function log_api_entry($endpoint) {
    logger_info("API ENDPOINT: {$endpoint}");
}

/**
 * Register shutdown function to log script completion
 */
register_shutdown_function(function() {
    if (isset($GLOBALS['request_start_time'])) {
        $duration = round((microtime(true) - $GLOBALS['request_start_time']) * 1000, 2);
        logger_debug("REQUEST COMPLETED", ['total_duration_ms' => $duration]);
    }
});

// Automatically log request start if this file is included
log_request_start();
