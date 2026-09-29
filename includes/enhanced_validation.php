<?php
/**
 * Enhanced Form Validation and Data Handling
 * Production-ready validation for all user inputs
 */

/**
 * Comprehensive form validator
 */
class FormValidator {
    private array $errors = [];
    private array $data = [];
    private array $rules = [];
    
    public function __construct(array $data = []) {
        $this->data = $data;
    }
    
    public function addRule(string $field, string $rule, array $params = []): self {
        $this->rules[$field][] = ['rule' => $rule, 'params' => $params];
        return $this;
    }
    
    public function validate(): bool {
        $this->errors = [];
        
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            
            foreach ($fieldRules as $ruleData) {
                $rule = $ruleData['rule'];
                $params = $ruleData['params'];
                
                if (!$this->applyRule($field, $value, $rule, $params)) {
                    break; // Stop on first error for this field
                }
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule(string $field, $value, string $rule, array $params): bool {
        switch ($rule) {
            case 'required':
                if (empty($value) || trim($value) === '') {
                    $this->errors[$field] = $params['message'] ?? "{$field} is required";
                    return false;
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = $params['message'] ?? "Invalid email format";
                    return false;
                }
                break;
                
            case 'min_length':
                if (!empty($value) && strlen($value) < $params['length']) {
                    $this->errors[$field] = $params['message'] ?? "Minimum {$params['length']} characters required";
                    return false;
                }
                break;
                
            case 'max_length':
                if (!empty($value) && strlen($value) > $params['length']) {
                    $this->errors[$field] = $params['message'] ?? "Maximum {$params['length']} characters allowed";
                    return false;
                }
                break;
                
            case 'phone':
                if (!empty($value)) {
                    $phone = preg_replace('/[^\d\+\-\s\(\)]/', '', $value);
                    if (!preg_match('/^[\+]?[\d\s\-\(\)]{10,}$/', $phone)) {
                        $this->errors[$field] = $params['message'] ?? "Invalid phone number format";
                        return false;
                    }
                    $this->data[$field] = $phone; // Clean the phone number
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field] = $params['message'] ?? "Must be a numeric value";
                    return false;
                }
                break;
                
            case 'positive':
                if (!empty($value) && (!is_numeric($value) || $value <= 0)) {
                    $this->errors[$field] = $params['message'] ?? "Must be a positive number";
                    return false;
                }
                break;
                
            case 'date':
                if (!empty($value)) {
                    $date = DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        $this->errors[$field] = $params['message'] ?? "Invalid date format (YYYY-MM-DD)";
                        return false;
                    }
                }
                break;
                
            case 'date_after':
                if (!empty($value)) {
                    $date = new DateTime($value);
                    $afterDate = new DateTime($params['date']);
                    if ($date <= $afterDate) {
                        $this->errors[$field] = $params['message'] ?? "Date must be after {$params['date']}";
                        return false;
                    }
                }
                break;
                
            case 'in':
                if (!empty($value) && !in_array($value, $params['options'])) {
                    $this->errors[$field] = $params['message'] ?? "Invalid option selected";
                    return false;
                }
                break;
                
            case 'regex':
                if (!empty($value) && !preg_match($params['pattern'], $value)) {
                    $this->errors[$field] = $params['message'] ?? "Invalid format";
                    return false;
                }
                break;
                
            case 'file':
                if (!empty($_FILES[$field]['name'])) {
                    $file = $_FILES[$field];
                    $maxSize = $params['max_size'] ?? 5 * 1024 * 1024; // 5MB default
                    $allowedTypes = $params['allowed_types'] ?? ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $this->errors[$field] = $this->getUploadErrorMessage($file['error']);
                        return false;
                    }
                    
                    if ($file['size'] > $maxSize) {
                        $this->errors[$field] = "File size exceeds limit";
                        return false;
                    }
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    if (!in_array($mimeType, $allowedTypes)) {
                        $this->errors[$field] = "Invalid file type";
                        return false;
                    }
                }
                break;
        }
        
        return true;
    }
    
    private function getUploadErrorMessage(int $errorCode): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function getFirstError(): ?string {
        return empty($this->errors) ? null : reset($this->errors);
    }
    
    public function getCleanData(): array {
        $clean = [];
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $clean[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }
    
    public function getField(string $field, $default = '') {
        return $this->data[$field] ?? $default;
    }
}

/**
 * Booking form validation
 */
function validate_booking_form(array $data): array {
    $validator = new FormValidator($data);
    
    // Guest information
    $validator->addRule('guest_name', 'required')
              ->addRule('guest_name', 'min_length', ['length' => 2])
              ->addRule('guest_name', 'max_length', ['length' => 100]);
              
    $validator->addRule('guest_email', 'required')
              ->addRule('guest_email', 'email');
              
    $validator->addRule('guest_phone', 'phone');
    
    // Booking details
    $validator->addRule('check_in', 'required')
              ->addRule('check_in', 'date')
              ->addRule('check_in', 'date_after', ['date' => date('Y-m-d', strtotime('-1 day'))]);
              
    $validator->addRule('check_out', 'required')
              ->addRule('check_out', 'date');
              
    $validator->addRule('adults', 'required')
              ->addRule('adults', 'positive');
              
    $validator->addRule('children', 'numeric');
    
    $validator->addRule('room_type', 'required')
              ->addRule('room_type', 'in', ['options' => ['standard', 'deluxe', 'suite', 'chalet', 'glamping']]);
    
    // Validate date logic
    if (!empty($data['check_in']) && !empty($data['check_out'])) {
        $checkIn = new DateTime($data['check_in']);
        $checkOut = new DateTime($data['check_out']);
        
        if ($checkOut <= $checkIn) {
            return ['valid' => false, 'errors' => ['check_out' => 'Check-out date must be after check-in date']];
        }
        
        $nights = $checkIn->diff($checkOut)->days;
        if ($nights > 30) {
            return ['valid' => false, 'errors' => ['check_out' => 'Maximum stay is 30 nights']];
        }
    }
    
    $valid = $validator->validate();
    
    return [
        'valid' => $valid,
        'errors' => $validator->getErrors(),
        'clean_data' => $validator->getCleanData()
    ];
}

/**
 * Contact form validation
 */
function validate_contact_form(array $data): array {
    $validator = new FormValidator($data);
    
    $validator->addRule('name', 'required')
              ->addRule('name', 'min_length', ['length' => 2])
              ->addRule('name', 'max_length', ['length' => 100]);
              
    $validator->addRule('email', 'required')
              ->addRule('email', 'email');
              
    $validator->addRule('phone', 'phone');
    
    $validator->addRule('subject', 'required')
              ->addRule('subject', 'min_length', ['length' => 5])
              ->addRule('subject', 'max_length', ['length' => 200]);
              
    $validator->addRule('message', 'required')
              ->addRule('message', 'min_length', ['length' => 10])
              ->addRule('message', 'max_length', ['length' => 5000]);
    
    $valid = $validator->validate();
    
    return [
        'valid' => $valid,
        'errors' => $validator->getErrors(),
        'clean_data' => $validator->getCleanData()
    ];
}

/**
 * Login form validation
 */
function validate_login_form(array $data): array {
    $validator = new FormValidator($data);
    
    $validator->addRule('email', 'required')
              ->addRule('email', 'email');
              
    $validator->addRule('password', 'required')
              ->addRule('password', 'min_length', ['length' => 8]);
    
    $valid = $validator->validate();
    
    return [
        'valid' => $valid,
        'errors' => $validator->getErrors(),
        'clean_data' => $validator->getCleanData()
    ];
}

/**
 * User registration validation
 */
function validate_registration_form(array $data): array {
    $validator = new FormValidator($data);
    
    $validator->addRule('username', 'required')
              ->addRule('username', 'min_length', ['length' => 3])
              ->addRule('username', 'max_length', ['length' => 50])
              ->addRule('username', 'regex', ['pattern' => '/^[a-zA-Z0-9_]+$/']);
              
    $validator->addRule('email', 'required')
              ->addRule('email', 'email');
              
    $validator->addRule('password', 'required')
              ->addRule('password', 'min_length', ['length' => 8]);
    
    $validator->addRule('confirm_password', 'required');
    
    $valid = $validator->validate();
    
    // Check password confirmation
    if ($valid && $data['password'] !== $data['confirm_password']) {
        return [
            'valid' => false,
            'errors' => ['confirm_password' => 'Passwords do not match'],
            'clean_data' => []
        ];
    }
    
    return [
        'valid' => $valid,
        'errors' => $validator->getErrors(),
        'clean_data' => $validator->getCleanData()
    ];
}

/**
 * Newsletter subscription validation
 */
function validate_newsletter_form(array $data): array {
    $validator = new FormValidator($data);
    
    $validator->addRule('email', 'required')
              ->addRule('email', 'email');
    
    $valid = $validator->validate();
    
    return [
        'valid' => $valid,
        'errors' => $validator->getErrors(),
        'clean_data' => $validator->getCleanData()
    ];
}

/**
 * Testimonial submission validation
 */
function validate_testimonial_form(array $data): array {
    $validator = new FormValidator($data);
    
    $validator->addRule('guest_name', 'required')
              ->addRule('guest_name', 'min_length', ['length' => 2])
              ->addRule('guest_name', 'max_length', ['length' => 100]);
              
    $validator->addRule('guest_email', 'required')
              ->addRule('guest_email', 'email');
              
    $validator->addRule('title', 'required')
              ->addRule('title', 'min_length', ['length' => 5])
              ->addRule('title', 'max_length', ['length' => 200]);
              
    $validator->addRule('content', 'required')
              ->addRule('content', 'min_length', ['length' => 20])
              ->addRule('content', 'max_length', ['length' => 5000]);
              
    $validator->addRule('rating', 'required')
              ->addRule('rating', 'in', ['options' => ['1', '2', '3', '4', '5']]);
    
    $valid = $validator->validate();
    
    return [
        'valid' => $valid,
        'errors' => $validator->getErrors(),
        'clean_data' => $validator->getCleanData()
    ];
}

/**
 * Sanitize and validate file uploads
 */
function validate_file_upload(array $file, array $options = []): array {
    $defaults = [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'required' => false
    ];
    
    $options = array_merge($defaults, $options);
    
    $result = ['valid' => true, 'error' => null, 'file' => null];
    
    // Check if file is required
    if ($options['required'] && empty($file['name'])) {
        $result['valid'] = false;
        $result['error'] = 'File is required';
        return $result;
    }
    
    // If no file uploaded and not required, return valid
    if (empty($file['name'])) {
        return $result;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['valid'] = false;
        $result['error'] = 'Upload error: ' . $file['error'];
        return $result;
    }
    
    // Check file size
    if ($file['size'] > $options['max_size']) {
        $result['valid'] = false;
        $result['error'] = 'File size exceeds limit';
        return $result;
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $options['allowed_extensions'])) {
        $result['valid'] = false;
        $result['error'] = 'File type not allowed';
        return $result;
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $options['allowed_types'])) {
        $result['valid'] = false;
        $result['error'] = 'Invalid file type';
        return $result;
    }
    
    // Verify file contents match extension
    if (!@getimagesize($file['tmp_name'])) {
        $result['valid'] = false;
        $result['error'] = 'Invalid image file';
        return $result;
    }
    
    $result['file'] = $file;
    return $result;
}
