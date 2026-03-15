<?php
/**
 * ============================================================================
 * AZEU WATER STATION - UPLOAD STATION LOGO API
 * ============================================================================
 * 
 * Purpose: Upload a new station logo (saved to images/system/)
 * Method: POST (multipart/form-data)
 * Role: ADMIN, SUPER_ADMIN
 * Max Size: 1MB
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

$max_size = 1 * 1024 * 1024; // 1MB

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = 'No file uploaded';
    if (isset($_FILES['logo'])) {
        switch ($_FILES['logo']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_msg = 'File is too large. Maximum size is 1MB.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_msg = 'No file was selected';
                break;
            default:
                $error_msg = 'Upload failed. Please try again.';
        }
    }
    json_response(['success' => false, 'message' => $error_msg], 400);
}

$file = $_FILES['logo'];

// Check file size (1MB limit)
if ($file['size'] > $max_size) {
    $size_mb = round($file['size'] / (1024 * 1024), 2);
    json_response([
        'success' => false, 
        'message' => "Logo file is too large ({$size_mb}MB). Maximum allowed size is 1MB. Please choose a smaller image."
    ], 400);
}

// Validate file type
$allowed_types = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    json_response([
        'success' => false, 
        'message' => 'Invalid file type. Only PNG, JPG, GIF, and WEBP are allowed.'
    ], 400);
}

// Determine file extension
$ext_map = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];
$ext = $ext_map[$mime_type] ?? 'png';

// Target directory
$upload_dir = __DIR__ . '/../../images/system/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate filename: station-logo-{timestamp}.{ext}
$filename = 'station-logo-' . time() . '.' . $ext;
$filepath = $upload_dir . $filename;
$setting_value = 'images/system/' . $filename;

try {
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        json_response(['success' => false, 'message' => 'Failed to save the uploaded file'], 500);
    }

    // Delete old logo if it's not the default
    $old_logo = get_setting('station_logo');
    if ($old_logo && $old_logo !== 'images/system/logo-1.png') {
        $old_path = __DIR__ . '/../../' . $old_logo;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }

    // Save to settings
    update_setting('station_logo', $setting_value);

    logger_info("Station logo updated", [
        'updated_by' => $_SESSION['user_id'],
        'filename' => $filename,
        'size' => $file['size']
    ]);

    json_response([
        'success' => true, 
        'message' => 'Logo updated successfully',
        'logo_path' => $setting_value
    ]);

} catch (Exception $e) {
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred while uploading'], 500);
}
