<?php
/**
 * DEPRECATED: Secure File Upload Handler
 * 
 * This file has been replaced by the new secure file upload system in file_upload.php.
 * All functionality has been moved to file_upload.php with enhanced security features.
 * 
 * @deprecated 1.0.0 Use file_upload.php functions instead
 * @see file_upload.php
 */

trigger_error(
    'enhanced_file_upload.php is deprecated. Use file_upload.php instead.',
    E_USER_DEPRECATED
);

// For backward compatibility, require the new implementation
require_once __DIR__ . '/file_upload.php';

/**
 * @deprecated Use secure_upload_file() from file_upload.php instead
 */
function secure_file_upload(array $file, array $options = []): array {
    trigger_error(
        'secure_file_upload() is deprecated. Use secure_upload_file() from file_upload.php instead.',
        E_USER_DEPRECATED
    );
    
    $defaults = [
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'upload_dir' => 'uploads/',
        'subdir' => 'files',
    ];
    
    $options = array_merge($defaults, $options);
    
    return secure_upload_file(
        $file,
        $options['subdir'],
        $options['allowed_types'],
        $options['max_size']
    );
}

/**
 * @deprecated Function moved to file_upload.php
 */
function scan_file_for_malware(string $filepath): bool {
    trigger_error(
        'scan_file_for_malware() is deprecated. Use is_file_clean() from file_upload.php instead.',
        E_USER_DEPRECATED
    );
    
    if (!function_exists('is_file_clean')) {
        require_once __DIR__ . '/file_upload.php';
    }
    
    return is_file_clean($filepath);
}

/**
 * @deprecated Function moved to file_upload.php
 */
function process_uploaded_image(string $filepath, array $options = []): bool {
    trigger_error(
        'process_uploaded_image() is deprecated. Use the image processing functions in file_upload.php instead.',
        E_USER_DEPRECATED
    );
    
    if (!function_exists('resize_image')) {
        require_once __DIR__ . '/file_upload.php';
    }
    
    $defaults = [
        'max_width' => 1200,
        'max_height' => 1200,
        'quality' => 85
    ];
    
    $options = array_merge($defaults, $options);
    
    return resize_image($filepath, $options['max_width'], $options['max_height'], $options['quality']);
}