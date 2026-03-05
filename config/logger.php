<?php
/**
 * Azeu Water Station - Logging Engine
 * Complete logging system with automatic enrichment and rotation
 */

require_once __DIR__ . '/constants.php';

// Set timezone for logs
date_default_timezone_set(LOG_TIMEZONE);

/**
 * Main logging function
 */
function logger_write($level, $message, $context = []) {
    // Skip low-priority logs if DEBUG_MODE is off
    if (!DEBUG_MODE && $level < LOG_LEVEL_WARNING) {
        return;
    }
    
    // Level names
    $levelNames = [
        LOG_LEVEL_TRACE => 'TRACE',
        LOG_LEVEL_DEBUG => 'DEBUG',
        LOG_LEVEL_INFO => 'INFO',
        LOG_LEVEL_WARNING => 'WARNING',
        LOG_LEVEL_ERROR => 'ERROR',
        LOG_LEVEL_CRITICAL => 'CRITICAL'
    ];
    
    $levelName = $levelNames[$level] ?? 'UNKNOWN';
    
    // Get caller information
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    $caller = $backtrace[1] ?? $backtrace[0];
    $file = basename($caller['file'] ?? 'unknown');
    $function = $caller['function'] ?? 'global';
    
    // Enrich with session data
    $enrichment = [];
    
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
        $enrichment['user_id'] = $_SESSION['user_id'];
        $enrichment['username'] = $_SESSION['username'] ?? 'unknown';
        $enrichment['role'] = $_SESSION['role'] ?? 'unknown';
    }
    
    $enrichment['ip'] = get_client_ip_for_log();
    $enrichment['uri'] = $_SERVER['REQUEST_URI'] ?? '';
    $enrichment['method'] = $_SERVER['REQUEST_METHOD'] ?? '';
    
    // Merge context with enrichment
    $allContext = array_merge($enrichment, $context);
    
    // Format context as key=value pairs
    $contextStr = '';
    foreach ($allContext as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }
        $contextStr .= " | {$key}=" . var_export($value, true);
    }
    
    // Build log entry
    $timestamp = date(LOG_DATE_FORMAT);
    $logEntry = "[{$timestamp}] [{$levelName}] [{$file}] [{$function}] — {$message}{$contextStr}\n";
    
    // Write to main log file
    logger_write_to_file(LOG_FILE_PATH, $logEntry);
    
    // Also write to error log if ERROR or CRITICAL
    if ($level >= LOG_LEVEL_ERROR) {
        logger_write_to_file(ERROR_LOG_FILE_PATH, $logEntry);
    }
}

/**
 * Write to log file with rotation
 */
function logger_write_to_file($filePath, $entry) {
    // Create logs directory if it doesn't exist
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Check file size and rotate if needed
    if (file_exists($filePath) && filesize($filePath) > LOG_MAX_SIZE) {
        $rotatedName = $dir . '/log_' . date('Ymd_His') . '.txt';
        rename($filePath, $rotatedName);
    }
    
    // Append to log file
    file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Get client IP (helper for logging)
 */
function get_client_ip_for_log() {
    $ip = 'unknown';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

// Convenience functions for each log level
function logger_trace($message, $context = []) {
    logger_write(LOG_LEVEL_TRACE, $message, $context);
}

function logger_debug($message, $context = []) {
    logger_write(LOG_LEVEL_DEBUG, $message, $context);
}

function logger_info($message, $context = []) {
    logger_write(LOG_LEVEL_INFO, $message, $context);
}

function logger_warning($message, $context = []) {
    logger_write(LOG_LEVEL_WARNING, $message, $context);
}

function logger_error($message, $context = []) {
    logger_write(LOG_LEVEL_ERROR, $message, $context);
}

function logger_critical($message, $context = []) {
    logger_write(LOG_LEVEL_CRITICAL, $message, $context);
}

/**
 * Log function entry
 */
function logger_function_entry($params = []) {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = $backtrace[1] ?? $backtrace[0];
    $function = $caller['function'] ?? 'unknown';
    
    // Filter sensitive params
    $safeParams = filter_sensitive_data($params);
    
    logger_trace("ENTERED {$function}", ['params' => $safeParams]);
}

/**
 * Log function exit
 */
function logger_function_exit($result = null) {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = $backtrace[1] ?? $backtrace[0];
    $function = $caller['function'] ?? 'unknown';
    
    // Filter sensitive result
    $safeResult = filter_sensitive_data($result);
    
    logger_trace("EXITED {$function}", ['result' => $safeResult]);
}

/**
 * Log SQL query
 */
function logger_query($sql, $params = [], $duration_ms = null) {
    $context = [
        'query' => $sql,
        'params' => filter_sensitive_data($params)
    ];
    
    if ($duration_ms !== null) {
        $context['duration'] = $duration_ms . 'ms';
    }
    
    logger_debug('SQL QUERY', $context);
}

/**
 * Log exception
 */
function logger_exception($exception) {
    logger_error('EXCEPTION', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
}

/**
 * Filter sensitive data from logs
 */
function filter_sensitive_data($data) {
    if (is_array($data)) {
        $filtered = [];
        foreach ($data as $key => $value) {
            // Hide passwords, tokens, and sensitive keys
            if (preg_match('/(password|token|secret|key|csrf)/i', $key)) {
                $filtered[$key] = '[REDACTED]';
            } else {
                $filtered[$key] = filter_sensitive_data($value);
            }
        }
        return $filtered;
    }
    return $data;
}

// Register global error handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE'
    ];
    
    $type = $errorTypes[$errno] ?? 'UNKNOWN';
    
    logger_error("PHP {$type}: {$errstr}", [
        'file' => basename($errfile),
        'line' => $errline
    ]);
    
    // Don't execute PHP's internal error handler
    return false;
});

set_exception_handler(function($exception) {
    logger_exception($exception);
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logger_critical('FATAL ERROR: ' . $error['message'], [
            'file' => basename($error['file']),
            'line' => $error['line']
        ]);
    }
});
