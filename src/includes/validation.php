<?php
/**
 * Edutrack computer training college
 * Input Validation Functions
 */

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Zambian format)
 * 
 * @param string $phone Phone number
 * @return bool
 */
function validatePhone($phone) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-]/', '', $phone);
    
    // Zambian phone format: 09XXXXXXXX or +260XXXXXXXXX
    return preg_match('/^(09\d{8}|\+2609\d{8}|2609\d{8})$/', $phone);
}

/**
 * Validate URL
 * 
 * @param string $url URL to validate
 * @return bool
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate required field
 * 
 * @param mixed $value Value to validate
 * @return bool
 */
function validateRequired($value) {
    if (is_string($value)) {
        return trim($value) !== '';
    }
    return !empty($value);
}

/**
 * Validate minimum length
 * 
 * @param string $value Value to validate
 * @param int $min Minimum length
 * @return bool
 */
function validateMinLength($value, $min) {
    return strlen($value) >= $min;
}

/**
 * Validate maximum length
 * 
 * @param string $value Value to validate
 * @param int $max Maximum length
 * @return bool
 */
function validateMaxLength($value, $max) {
    return strlen($value) <= $max;
}

/**
 * Validate number
 * 
 * @param mixed $value Value to validate
 * @return bool
 */
function validateNumber($value) {
    return is_numeric($value);
}

/**
 * Validate integer
 * 
 * @param mixed $value Value to validate
 * @return bool
 */
function validateInteger($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

/**
 * Validate float
 * 
 * @param mixed $value Value to validate
 * @return bool
 */
function validateFloat($value) {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

/**
 * Validate minimum value
 * 
 * @param numeric $value Value to validate
 * @param numeric $min Minimum value
 * @return bool
 */
function validateMin($value, $min) {
    return is_numeric($value) && $value >= $min;
}

/**
 * Validate maximum value
 * 
 * @param numeric $value Value to validate
 * @param numeric $max Maximum value
 * @return bool
 */
function validateMax($value, $max) {
    return is_numeric($value) && $value <= $max;
}

/**
 * Validate between range
 * 
 * @param numeric $value Value to validate
 * @param numeric $min Minimum value
 * @param numeric $max Maximum value
 * @return bool
 */
function validateBetween($value, $min, $max) {
    return is_numeric($value) && $value >= $min && $value <= $max;
}

/**
 * Validate date format
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return bool
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate date is in the past
 * 
 * @param string $date Date string
 * @return bool
 */
function validatePastDate($date) {
    return strtotime($date) < time();
}

/**
 * Validate date is in the future
 * 
 * @param string $date Date string
 * @return bool
 */
function validateFutureDate($date) {
    return strtotime($date) > time();
}

/**
 * Validate alphanumeric
 * 
 * @param string $value Value to validate
 * @return bool
 */
function validateAlphanumeric($value) {
    return ctype_alnum($value);
}

/**
 * Validate alphabetic only
 * 
 * @param string $value Value to validate
 * @return bool
 */
function validateAlpha($value) {
    return ctype_alpha($value);
}

/**
 * Validate matches pattern
 * 
 * @param string $value Value to validate
 * @param string $pattern Regex pattern
 * @return bool
 */
function validatePattern($value, $pattern) {
    return preg_match($pattern, $value) === 1;
}

/**
 * Validate value is in array
 * 
 * @param mixed $value Value to validate
 * @param array $array Array of valid values
 * @return bool
 */
function validateIn($value, array $array) {
    return in_array($value, $array, true);
}

/**
 * Validate NRC number (Zambian National Registration Card)
 * Format: 123456/78/9
 * 
 * @param string $nrc NRC number
 * @return bool
 */
function validateNrc($nrc) {
    return preg_match('/^\d{6}\/\d{2}\/\d{1}$/', $nrc);
}

/**
 * Validate username
 * 
 * @param string $username Username
 * @return bool
 */
function validateUsername($username) {
    // 3-20 characters, alphanumeric, underscore, hyphen
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username);
}

/**
 * Validate matches another field
 * 
 * @param mixed $value Value to validate
 * @param mixed $other Other value to match
 * @return bool
 */
function validateMatches($value, $other) {
    return $value === $other;
}

/**
 * Validate unique value in database
 * 
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @param int $excludeId ID to exclude (for updates)
 * @return bool
 */
function validateUnique($table, $column, $value, $excludeId = null) {
    global $db;
    
    $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
    $params = [$value];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $count = $db->fetchColumn($sql, $params);
    return $count == 0;
}

/**
 * Validate exists in database
 * 
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @return bool
 */
function validateExists($table, $column, $value) {
    global $db;
    
    $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
    $count = $db->fetchColumn($sql, [$value]);
    
    return $count > 0;
}

/**
 * Comprehensive form validator
 * 
 * @param array $data Form data
 * @param array $rules Validation rules
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate(array $data, array $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;
        $fieldErrors = [];
        
        // Split rules
        $rulesArray = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
        
        foreach ($rulesArray as $rule) {
            // Parse rule and parameters
            if (strpos($rule, ':') !== false) {
                list($ruleName, $params) = explode(':', $rule, 2);
                $params = explode(',', $params);
            } else {
                $ruleName = $rule;
                $params = [];
            }
            
            // Validate based on rule
            switch ($ruleName) {
                case 'required':
                    if (!validateRequired($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                    break;
                    
                case 'email':
                    if ($value && !validateEmail($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email';
                    }
                    break;
                    
                case 'phone':
                    if ($value && !validatePhone($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid phone number';
                    }
                    break;
                    
                case 'url':
                    if ($value && !validateUrl($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid URL';
                    }
                    break;
                    
                case 'min':
                    if ($value && !validateMinLength($value, $params[0])) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $params[0] . ' characters';
                    }
                    break;
                    
                case 'max':
                    if ($value && !validateMaxLength($value, $params[0])) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must not exceed ' . $params[0] . ' characters';
                    }
                    break;
                    
                case 'numeric':
                    if ($value && !validateNumber($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number';
                    }
                    break;
                    
                case 'integer':
                    if ($value && !validateInteger($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be an integer';
                    }
                    break;
                    
                case 'matches':
                    if ($value && !validateMatches($value, $data[$params[0]] ?? null)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must match ' . str_replace('_', ' ', $params[0]);
                    }
                    break;
                    
                case 'unique':
                    $excludeId = isset($params[2]) ? $params[2] : null;
                    if ($value && !validateUnique($params[0], $params[1], $value, $excludeId)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' already exists';
                    }
                    break;
                    
                case 'exists':
                    if ($value && !validateExists($params[0], $params[1], $value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' does not exist';
                    }
                    break;
                    
                case 'date':
                    if ($value && !validateDate($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date';
                    }
                    break;
                    
                case 'alpha':
                    if ($value && !validateAlpha($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters';
                    }
                    break;
                    
                case 'alphanumeric':
                    if ($value && !validateAlphanumeric($value)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters and numbers';
                    }
                    break;
                    
                case 'in':
                    if ($value && !validateIn($value, $params)) {
                        $fieldErrors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $params);
                    }
                    break;
            }
        }
        
        if (!empty($fieldErrors)) {
            $errors[$field] = $fieldErrors;
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Get validation error message for field
 * 
 * @param array $errors Errors array
 * @param string $field Field name
 * @return string
 */
function validationError($errors, $field) {
    if (isset($errors[$field])) {
        $fieldErrors = is_array($errors[$field]) ? $errors[$field] : [$errors[$field]];
        return '<span class="text-red-500 text-sm">' . implode('<br>', $fieldErrors) . '</span>';
    }
    return '';
}

/**
 * Check if field has error
 *
 * @param array $errors Errors array
 * @param string $field Field name
 * @return bool
 */
function hasError($errors, $field) {
    return isset($errors[$field]);
}

/**
 * Validate password strength
 *
 * Requirements:
 * - Minimum 8 characters
 * - At least one uppercase letter
 * - At least one lowercase letter
 * - At least one number
 * - At least one special character
 *
 * @param string $password Password to validate
 * @param bool $requireComplexity Whether to enforce complexity rules
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePassword($password, $requireComplexity = true) {
    $errors = [];

    // Minimum length
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if ($requireComplexity) {
        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        // Check for special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
    }

    // Check for common weak passwords
    $commonPasswords = [
        'password', '12345678', 'qwerty', 'abc123', 'password123',
        'admin', 'letmein', 'welcome', 'monkey', '1234567890'
    ];

    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = 'Password is too common. Please choose a stronger password';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate file upload
 *
 * @param array $file $_FILES array element
 * @param array $options Validation options
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateFileUpload($file, array $options = []) {
    $errors = [];

    // Default options
    $maxSize = $options['max_size'] ?? 10485760; // 10MB default
    $allowedTypes = $options['allowed_types'] ?? [];
    $allowedMimes = $options['allowed_mimes'] ?? [];

    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        $errors[] = 'Invalid file upload';
        return ['valid' => false, 'errors' => $errors];
    }

    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errors[] = 'File size exceeds limit';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errors[] = 'File upload was not completed';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errors[] = 'No file was uploaded';
            break;
        default:
            $errors[] = 'File upload error occurred';
            break;
    }

    if (!empty($errors)) {
        return ['valid' => false, 'errors' => $errors];
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size must not exceed ' . formatFileSize($maxSize);
    }

    // Check file extension
    if (!empty($allowedTypes)) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
        }
    }

    // CRITICAL: Check actual MIME type, not just extension
    if (!empty($allowedMimes)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'File type not allowed (MIME type validation failed)';
        }
    }

    // Additional security: Check for PHP files disguised as other types
    $fileContents = file_get_contents($file['tmp_name'], false, null, 0, 256);
    if (preg_match('/<\?php/i', $fileContents)) {
        $errors[] = 'File contains potentially malicious content';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Sanitize filename for safe storage
 *
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitizeFilename($filename) {
    // Get extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);

    // Remove any special characters, keep only alphanumeric, dash, underscore
    $basename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $basename);

    // Limit length
    $basename = substr($basename, 0, 100);

    // Add timestamp to make it unique
    $basename .= '_' . time();

    return $basename . '.' . $extension;
}