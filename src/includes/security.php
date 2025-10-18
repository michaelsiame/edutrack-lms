<?php
/**
 * Edutrack Computer Training College
 * Security Functions
 */

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token
 * 
 * @return string
 */
function csrfToken() {
    return generateCsrfToken();
}

/**
 * Get CSRF token HTML input
 * 
 * @return string
 */
function csrfField() {
    $token = csrfToken();
    $name = config('security.csrf_token_name', 'csrf_token');
    return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
}

/**
 * Verify CSRF token
 * 
 * @param string|null $token Token to verify
 * @return bool
 */
function verifyCsrfToken($token = null) {
    if ($token === null) {
        $tokenName = config('security.csrf_token_name', 'csrf_token');
        $token = $_POST[$tokenName] ?? $_GET[$tokenName] ?? '';
    }
    
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token or die
 */
function requireCsrfToken() {
    if (!verifyCsrfToken()) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
}

/**
 * Hash password
 * 
 * @param string $password Plain text password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if password needs rehash
 * 
 * @param string $hash Hashed password
 * @return bool
 */
function needsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}

/**
 * Sanitize input
 * 
 * @param mixed $input Input to sanitize
 * @return mixed
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Clean string for database
 * 
 * @param string $string Input string
 * @return string
 */
function cleanString($string) {
    return trim(strip_tags($string));
}

/**
 * Prevent XSS
 * 
 * @param string $string Input string
 * @return string
 */
function xssClean($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Rate limiting check
 * 
 * @param string $key Unique key for the action
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if allowed, false if rate limited
 */
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 900) {
    if (!config('rate_limit.enabled', true)) {
        return true;
    }
    
    $storageKey = 'rate_limit_' . md5($key);
    
    // Get current attempts
    $attempts = $_SESSION[$storageKey] ?? [
        'count' => 0,
        'reset_at' => time() + $timeWindow
    ];
    
    // Reset if time window expired
    if (time() > $attempts['reset_at']) {
        $attempts = [
            'count' => 0,
            'reset_at' => time() + $timeWindow
        ];
    }
    
    // Increment attempts
    $attempts['count']++;
    $_SESSION[$storageKey] = $attempts;
    
    // Check if exceeded
    return $attempts['count'] <= $maxAttempts;
}

/**
 * Get rate limit remaining attempts
 * 
 * @param string $key Unique key for the action
 * @param int $maxAttempts Maximum attempts allowed
 * @return int
 */
function getRateLimitRemaining($key, $maxAttempts = 5) {
    $storageKey = 'rate_limit_' . md5($key);
    $attempts = $_SESSION[$storageKey] ?? ['count' => 0];
    
    return max(0, $maxAttempts - $attempts['count']);
}

/**
 * Reset rate limit
 * 
 * @param string $key Unique key for the action
 */
function resetRateLimit($key) {
    $storageKey = 'rate_limit_' . md5($key);
    unset($_SESSION[$storageKey]);
}

/**
 * Check login attempts for rate limiting
 * 
 * @param string $identifier Email or username
 * @return bool
 */
function checkLoginAttempts($identifier) {
    $key = 'login_' . $identifier . '_' . getClientIp();
    $maxAttempts = config('rate_limit.login_attempts_max', 5);
    $timeout = config('rate_limit.login_attempts_timeout', 900);
    
    return checkRateLimit($key, $maxAttempts, $timeout);
}

/**
 * Reset login attempts
 * 
 * @param string $identifier Email or username
 */
function resetLoginAttempts($identifier) {
    $key = 'login_' . $identifier . '_' . getClientIp();
    resetRateLimit($key);
}

/**
 * Encrypt data
 * 
 * @param string $data Data to encrypt
 * @return string
 */
function encryptData($data) {
    $key = config('security.encryption_key');
    
    if (empty($key)) {
        throw new Exception('Encryption key not set');
    }
    
    // Remove base64: prefix if present
    $key = str_replace('base64:', '', $key);
    $key = base64_decode($key);
    
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt data
 * 
 * @param string $data Encrypted data
 * @return string
 */
function decryptData($data) {
    $key = config('security.encryption_key');
    
    if (empty($key)) {
        throw new Exception('Encryption key not set');
    }
    
    // Remove base64: prefix if present
    $key = str_replace('base64:', '', $key);
    $key = base64_decode($key);
    
    $data = base64_decode($data);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

/**
 * Generate secure random token
 * 
 * @param int $length Token length
 * @return string
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePasswordStrength($password) {
    $errors = [];
    $minLength = config('security.password_min_length', 8);
    
    // Check length
    if (strlen($password) < $minLength) {
        $errors[] = "Password must be at least {$minLength} characters long";
    }
    
    // Check uppercase
    if (config('security.password_require_uppercase', true) && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    // Check number
    if (config('security.password_require_number', true) && !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    // Check special character
    if (config('security.password_require_special', true) && !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Prevent SQL injection by escaping string
 * Note: Use prepared statements instead when possible
 * 
 * @param string $string String to escape
 * @return string
 */
function escapeString($string) {
    global $pdo;
    return $pdo->quote($string);
}

/**
 * Check if request is AJAX
 * 
 * @return bool
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Check if request method matches
 * 
 * @param string $method HTTP method
 * @return bool
 */
function isMethod($method) {
    return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
}

/**
 * Require POST method
 */
function requirePost() {
    if (!isMethod('POST')) {
        http_response_code(405);
        die('Method Not Allowed');
    }
}

/**
 * Require GET method
 */
function requireGet() {
    if (!isMethod('GET')) {
        http_response_code(405);
        die('Method Not Allowed');
    }
}

/**
 * Clean filename for upload
 * 
 * @param string $filename Original filename
 * @return string
 */
function cleanFilename($filename) {
    // Remove path information
    $filename = basename($filename);
    
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    // Add timestamp to prevent overwrite
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    return $name . '_' . time() . '.' . $extension;
}

/**
 * Validate file upload
 * 
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed file extensions
 * @param int $maxSize Maximum file size in bytes
 * @return array ['valid' => bool, 'error' => string]
 */
function validateFileUpload($file, $allowedTypes, $maxSize = null) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        
        return ['valid' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error'];
    }
    
    // Check file size
    $maxSize = $maxSize ?? config('upload.max_size');
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size exceeds maximum allowed: ' . formatFileSize($maxSize)];
    }
    
    // Check file type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['valid' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Secure session initialization
 */
function secureSession() {
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Store fingerprint
    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = md5(
            $_SERVER['HTTP_USER_AGENT'] ?? '' . 
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''
        );
    } else {
        // Verify fingerprint
        $currentFingerprint = md5(
            $_SERVER['HTTP_USER_AGENT'] ?? '' . 
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''
        );
        
        if ($_SESSION['fingerprint'] !== $currentFingerprint) {
            session_destroy();
            session_start();
        }
    }
}

// Initialize secure session
secureSession();