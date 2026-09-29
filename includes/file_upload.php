<?php
/**
 * File Upload Helper
 * Handles secure file uploads with validation
 */

// Define upload directory if not already defined
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/');
}

// Expose the human-readable PHP upload size limit for error messages
if (!defined('PHP_UPLOAD_MAX')) {
    define('PHP_UPLOAD_MAX', ini_get('upload_max_filesize'));
}

// Ensure upload directory exists and is secure
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    // Create .htaccess to block script execution
    file_put_contents(UPLOAD_DIR . '.htaccess', 
        "Options -Indexes\n" .
        "<FilesMatch \\.(php|phar|phtml|shtml|sh|pl|cgi|exe|bin|cmd|bat|js|jsp|asp|aspx|html|htm)$>\n" .
        "  Order Deny,Allow\n" .
        "  Deny from all\n" .
        "</FilesMatch>"
    );
}

/**
 * Secure file upload with validation
 * 
 * @param array $file $_FILES array element
 * @param string $subdir Subdirectory under uploads/ (e.g., 'profiles', 'documents')
 * @param array $allowedMimeTypes Array of allowed MIME types
 * @param int $maxSize Maximum file size in bytes (default: 5MB)
 * @return array ['success' => bool, 'file_path' => string, 'error' => string]
 */
function secure_upload_file(array $file, string $subdir, array $allowedMimeTypes, int $maxSize = 5242880): array {
    $uploadDir = rtrim(UPLOAD_DIR . $subdir, '/') . '/';
    
    // Validate file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File is too large — PHP server limit is ' . PHP_UPLOAD_MAX . '. Reduce the image size and try again.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload size limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was selected for upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: missing temporary upload folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write file to disk. Check folder permissions.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error (code ' . $file['error'] . ')'];
    }

    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    // Verify file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',  // Images
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'  // Documents
    ];
    
    if (!in_array($ext, $allowedExtensions, true)) {
        return ['success' => false, 'error' => 'Invalid file extension'];
    }

    // Verify file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Max ' . 
                round($maxSize / 1024 / 1024, 1) . 'MB allowed'];
    }
    
    // Check for potential malware (basic check)
    if (!is_file_clean($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File appears to be malicious'];
    }

    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory: ' . $uploadDir];
        }
        // Add .htaccess protection
        file_put_contents($uploadDir . '.htaccess',
            "Options -Indexes\n" .
            "<FilesMatch \\.(php|phar|phtml|shtml|sh|pl|cgi|exe|bin|cmd|bat|js|jsp|asp|aspx|html|htm)$>\n" .
            "  Order Deny,Allow\n" .
            "  Deny from all\n" .
            "</FilesMatch>"
        );
    }

    // Verify the directory is actually writable before attempting move
    if (!is_writable($uploadDir)) {
        return ['success' => false, 'error' => 'Upload directory is not writable: ' . $uploadDir . '. Check folder permissions.'];
    }

    // Generate a secure filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    // Set secure permissions
    chmod($filepath, 0640);

    return [
        'success' => true,
        'file_path' => $subdir . '/' . $filename,
        'full_path' => $filepath,
        'mime_type' => $mimeType,
        'size' => $file['size'],
        'original_name' => $file['name'],
        'error' => null
    ];
}

/**
 * Check if a file appears to be clean
 * @param string $filepath Path to the file
 * @return bool True if file appears clean
 */
function is_file_clean(string $filepath): bool {
    // Skip check for empty files
    if (filesize($filepath) === 0) {
        return true;
    }
    
    $contents = file_get_contents($filepath);
    
    // Check for common PHP/JS injection patterns
    $suspiciousPatterns = [
        '/<\?php/i',
        '/<script.*?>.*?<\/script>/is',
        '/base64_decode\(/i',
        '/eval\(/i',
        '/system\(/i',
        '/shell_exec\(/i',
        '/passthru\(/i',
        '/exec\(/i',
        '/popen\(/i',
        '/proc_open\(/i',
        '/`.*`/',
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER)/i'
    ];
    
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $contents)) {
            error_log("Suspicious file detected: " . basename($filepath) . " - Pattern: $pattern");
            return false;
        }
    }
    
    return true;
}

/**
 * Upload profile photo (compatibility wrapper)
 * @deprecated Use secure_upload_file() instead
 */
function upload_profile_photo(array $file, int $userId): array {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $result = secure_upload_file($file, 'profiles', $allowedTypes, 5 * 1024 * 1024);
    
    if ($result['success']) {
        $result['file_path'] = 'uploads/' . $result['file_path'];
    }
    
    return $result;
}

/**
 * Delete profile photo
 * 
 * @param string $filePath Relative file path
 * @return bool Success status
 */
/**
 * Delete an uploaded file
 * @param string $filePath Relative file path (e.g., 'uploads/profiles/filename.jpg')
 * @return bool True if deletion was successful or file didn't exist
 */
function delete_uploaded_file(string $filePath): bool {
    if (empty($filePath)) {
        return true;
    }
    
    // Prevent directory traversal
    $filePath = str_replace(['../', '..\\'], '', $filePath);
    $fullPath = realpath(__DIR__ . '/../' . ltrim($filePath, '/'));
    
    // Ensure we're only deleting files within the uploads directory
    $uploadsPath = realpath(UPLOAD_DIR);
    if (strpos($fullPath, $uploadsPath) !== 0) {
        error_log("Attempted to delete file outside uploads directory: $filePath");
        return false;
    }
    
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    
    return true;
}

/**
 * @deprecated Use delete_uploaded_file() instead
 */
function delete_profile_photo(string $filePath): bool {
    return delete_uploaded_file($filePath);
}

/**
 * Upload CMS image (compatibility wrapper)
 * @deprecated Use secure_upload_file() instead
 */
function upload_cms_image(array $file, string $subdir, string $prefix = 'img_'): array {
    $baseDir = __DIR__ . '/../assets/images/';
    $subdir = trim($subdir, '/');
    $uploadDir = $baseDir . $subdir . '/';

    $allowedTypes = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
    $maxFileSize = 10 * 1024 * 1024;

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'file_path' => null, 'error' => 'No file uploaded'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $phpErrors = [
            UPLOAD_ERR_INI_SIZE   => 'File is too large — PHP server limit is ' . ini_get('upload_max_filesize') . '. Compress the image and try again.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload size limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: missing temporary upload folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: cannot write to disk. Check folder permissions.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
        ];
        return ['success' => false, 'file_path' => null, 'error' => $phpErrors[$file['error']] ?? 'Upload error code: ' . $file['error']];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, $allowedTypes, true)) {
        return ['success' => false, 'file_path' => null, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed'];
    }
    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'file_path' => null, 'error' => 'File size exceeds 10MB limit'];
    }

    if (!file_exists($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        return ['success' => false, 'file_path' => null, 'error' => 'Failed to create upload directory: ' . $uploadDir];
    }
    if (!is_writable($uploadDir)) {
        return ['success' => false, 'file_path' => null, 'error' => 'Upload directory is not writable: ' . $uploadDir . '. Check folder permissions.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . uniqid('', true) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'file_path' => null, 'error' => 'Failed to move uploaded file'];
    }
    @chmod($filepath, 0644);

    $relativePath = 'images/' . $subdir . '/' . $filename;

    return [
        'success' => true,
        'file_path' => $relativePath,
        'error' => null,
    ];
}

/**
 * Get profile photo URL
 * 
 * @param string|null $filePath Relative file path from database
 * @param bool $absolute Return absolute URL
 * @return string Photo URL or default avatar
 */
function get_profile_photo_url(?string $filePath, bool $absolute = false): string {
    // Include functions if not already loaded
    if (!function_exists('base_url')) {
        require_once __DIR__ . '/functions.php';
    }
    
    $defaultAvatar = 'assets/images/default-avatar.svg';
    
    if (empty($filePath)) {
        return $absolute ? base_url($defaultAvatar) : base_url($defaultAvatar);
    }
    
    // Check if file exists
    $fullPath = __DIR__ . '/../' . $filePath;
    if (!file_exists($fullPath)) {
        return $absolute ? base_url($defaultAvatar) : base_url($defaultAvatar);
    }
    
    return $absolute ? base_url($filePath) : base_url($filePath);
}

/**
 * Resize and optimize image
 * 
 * @param string $filepath Full file path
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @return bool Success status
 */
function resize_image(string $filepath, int $maxWidth = 400, int $maxHeight = 400): bool {
    if (!file_exists($filepath)) {
        return false;
    }
    
    // Get image info
    $imageInfo = getimagesize($filepath);
    if ($imageInfo === false) {
        return false;
    }
    
    list($width, $height, $type) = $imageInfo;
    
    // Check if resize is needed
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return true;
    }
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Create source image
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return false;
    }
    
    if ($source === false) {
        return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($destination, $filepath, 90);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($destination, $filepath, 9);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($destination, $filepath);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($destination, $filepath, 90);
            break;
    }
    
    // Clean up
    imagedestroy($source);
    imagedestroy($destination);
    
    return $success;
}

/**
 * Validate image dimensions
 * 
 * @param string $filepath Full file path
 * @param int $minWidth Minimum width
 * @param int $minHeight Minimum height
 * @return array ['valid' => bool, 'width' => int, 'height' => int, 'error' => string]
 */
function validate_image_dimensions(string $filepath, int $minWidth = 100, int $minHeight = 100): array {
    $imageInfo = getimagesize($filepath);
    
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'Invalid image file'];
    }
    
    list($width, $height) = $imageInfo;
    
    if ($width < $minWidth || $height < $minHeight) {
        return [
            'valid' => false,
            'width' => $width,
            'height' => $height,
            'error' => "Image must be at least {$minWidth}x{$minHeight} pixels"
        ];
    }
    
    return [
        'valid' => true,
        'width' => $width,
        'height' => $height,
        'error' => null
    ];
}
