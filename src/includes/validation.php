<?php
/**
 * Edutrack Computer Training College
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