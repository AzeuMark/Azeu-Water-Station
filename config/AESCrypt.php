<?php
/**
 * Azeu Water Station - AES Encryption/Decryption
 * AES-256-CBC encryption with IV prepending
 */

/**
 * Encrypt a plain text string using AES-256-CBC
 *  
 * @param string $plainText The text to encrypt
 * @param string $key The encryption key
 * @return string Base64 encoded encrypted string with IV prepended
 */
function encrypt($plainText, $key) {
    if (empty($plainText)) {
        return '';
    }
    
    $method = 'AES-256-CBC';
    
    // Generate a random IV for this encryption
    $ivLength = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLength);
    
    // Encrypt the data
    $encrypted = openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv);
    
    // Prepend IV to encrypted data and base64 encode
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt an encrypted string using AES-256-CBC
 * 
 * @param string $encryptedText Base64 encoded encrypted string with IV prepended
 * @param string $key The encryption key
 * @return string Decrypted plain text
 */
function decrypt($encryptedText, $key) {
    if (empty($encryptedText)) {
        return '';
    }
    
    $method = 'AES-256-CBC';
    
    // Decode from base64
    $decoded = base64_decode($encryptedText);
    
    if ($decoded === false) {
        return '';
    }
    
    // Extract IV and encrypted data
    $ivLength = openssl_cipher_iv_length($method);
    $iv = substr($decoded, 0, $ivLength);
    $encrypted = substr($decoded, $ivLength);
    
    // Decrypt
    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
    
    return $decrypted !== false ? $decrypted : '';
}
