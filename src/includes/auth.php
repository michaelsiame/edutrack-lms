<?php
/**
 * Edutrack computer training college
 * Authentication Functions
 */

/**
 * Register a new user
 * 
 * @param array $data User data
 * @return array ['success' => bool, 'message' => string, 'user_id' => int]
 */
function registerUser($data) {
    global $db;
    
    try {
        // Check if email already exists
        if ($db->exists('users', 'email = ?', [$data['email']])) {
            return [
                'success' => false,
                'message' => 'Email address already registered'
            ];
        }
        
        // Hash password
        $passwordHash = hashPassword($data['password']);
        
        // Generate verification token
        $verificationToken = generateToken();
        
        // Prepare user data
        $userData = [
            'email' => $data['email'],
            'password_hash' => $passwordHash,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'student',
            'status' => 'active',
            'email_verified' => false,
            'email_verification_token' => $verificationToken
        ];
        
        // Insert user
        $userId = $db->insert('users', $userData);
        
        // Create user profile
        $db->insert('user_profiles', [
            'user_id' => $userId,
            'bio' => null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'country' => 'Zambia'
        ]);
        
        // Send verification email
        sendVerificationEmail($data['email'], $data['first_name'], $verificationToken);
        
        // Log activity
        logActivity("New user registered: {$data['email']}", 'info');
        
        return [
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user_id' => $userId
        ];
        
    } catch (Exception $e) {
        logActivity("Registration error: " . $e->getMessage(), 'error');
        return [
            'success' => false,
            'message' => 'An error occurred during registration. Please try again.'
        ];
    }
}

/**
 * Login user
 * 
 * @param string $email Email address
 * @param string $password Password
 * @param bool $remember Remember me
 * @return array ['success' => bool, 'message' => string]
 */
function loginUser($email, $password, $remember = false) {
    global $db;

    if (APP_DEBUG) {
        error_log("=== LOGIN ATTEMPT START ===");
        error_log("Email: " . $email);
    }

    // Check rate limiting
    if (!checkLoginAttempts($email)) {
        if (APP_DEBUG) error_log("Rate limit exceeded for: " . $email);
        return [
            'success' => false,
            'message' => 'Too many failed login attempts. Please try again in 15 minutes.'
        ];
    }
    
    try {
        // Get user by email
        if (APP_DEBUG) error_log("Querying database for user...");
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if (APP_DEBUG) error_log("User found: " . ($user ? 'YES' : 'NO'));

        if ($user && APP_DEBUG) {
            error_log("User ID: " . $user['id']);
            error_log("User status: " . $user['status']);
            error_log("Password hash exists: " . (!empty($user['password_hash']) ? 'YES' : 'NO'));
        }

        if (!$user) {
            if (APP_DEBUG) error_log("User NOT found in database");
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }

        // Verify password
        if (APP_DEBUG) error_log("Verifying password...");
        $passwordValid = verifyPassword($password, $user['password_hash']);
        if (APP_DEBUG) error_log("Password valid: " . ($passwordValid ? 'YES' : 'NO'));

        if (!$passwordValid) {
            if (APP_DEBUG) error_log("Password verification FAILED");
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            if (APP_DEBUG) error_log("Account not active: " . $user['status']);
            return [
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.'
            ];
        }

        if (APP_DEBUG) error_log("All checks passed, creating session...");
        
        // Check if password needs rehashing
        if (needsRehash($user['password_hash'])) {
            $newHash = hashPassword($password);
            $db->update('users', ['password_hash' => $newHash], 'id = ?', [$user['id']]);
        }
        
        // Reset login attempts
        resetLoginAttempts($email);
        
        // Create session
        createUserSession($user, $remember);
        if (APP_DEBUG) error_log("Session created successfully");

        // Update last login
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // Log activity
        logActivity("User logged in: {$email}", 'info');

        if (APP_DEBUG) error_log("=== LOGIN SUCCESS ===");

        return [
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => getRedirectUrl($user['role'])
        ];

    } catch (Exception $e) {
        if (APP_DEBUG) {
            error_log("LOGIN EXCEPTION: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
        logActivity("Login error: " . $e->getMessage(), 'error');
        return [
            'success' => false,
            'message' => 'An error occurred during login. Please try again.'
        ];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    global $db;
    
    if (isLoggedIn()) {
        $userId = currentUserId();
        
        // Delete user sessions
        try {
            $db->delete('user_sessions', 'user_id = ?', [$userId]);
        } catch (Exception $e) {
            // Continue with logout even if session deletion fails
        }
        
        // Log activity
        logActivity("User logged out", 'info');
    }
    
    // Destroy session
    session_destroy();
    session_start();
}

/**
 * Create user session
 * 
 * @param array $user User data
 * @param bool $remember Remember me
 */
function createUserSession($user, $remember = false) {
    global $db;
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_status'] = $user['status'];
    $_SESSION['email_verified'] = $user['email_verified'];
    
    // Get user avatar
    $profile = $db->fetchOne("SELECT avatar FROM user_profiles WHERE user_id = ?", [$user['id']]);
    $_SESSION['user_avatar'] = $profile['avatar'] ?? null;
    
    // Set session timeout
    $lifetime = $remember ? (30 * 24 * 60 * 60) : config('session.lifetime', 7200);
    $_SESSION['session_lifetime'] = time() + $lifetime;
    
    // Store session in database
    try {
        $sessionToken = generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + $lifetime);
        
        $db->insert('user_sessions', [
            'user_id' => $user['id'],
            'session_token' => $sessionToken,
            'ip_address' => getClientIp(),
            'user_agent' => getUserAgent(),
            'expires_at' => $expiresAt
        ]);
        
        $_SESSION['session_token'] = $sessionToken;
    } catch (Exception $e) {
        logActivity("Session storage error: " . $e->getMessage(), 'error');
    }
}

/**
 * Get redirect URL based on user role
 * 
 * @param string $role User role
 * @return string
 */
function getRedirectUrl($role) {
    // Check if there's a redirect after login
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        return $redirect;
    }
    
    // Default redirects by role
    switch ($role) {
        case 'admin':
            return url('admin/index.php');
        case 'instructor':
            return url('instructor/index.php');
        case 'student':
        default:
            return url('dashboard.php');
    }
}

/**
 * Verify email with token
 * 
 * @param string $token Verification token
 * @return array ['success' => bool, 'message' => string]
 */
function verifyEmail($token) {
    global $db;
    
    try {
        $user = $db->fetchOne(
            "SELECT id, email, first_name FROM users WHERE email_verification_token = ? AND email_verified = 0",
            [$token]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired verification token'
            ];
        }
        
        // Update user
        $db->update('users', [
            'email_verified' => 1,
            'email_verification_token' => null
        ], 'id = ?', [$user['id']]);
        
        // Log activity
        logActivity("Email verified: {$user['email']}", 'info');
        
        return [
            'success' => true,
            'message' => 'Email verified successfully! You can now login.'
        ];
        
    } catch (Exception $e) {
        logActivity("Email verification error: " . $e->getMessage(), 'error');
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ];
    }
}

/**
 * Request password reset
 * 
 * @param string $email Email address
 * @return array ['success' => bool, 'message' => string]
 */
function requestPasswordReset($email) {
    global $db;
    
    try {
        $user = $db->fetchOne("SELECT id, email, first_name FROM users WHERE email = ?", [$email]);
        
        if (!$user) {
            // Return success anyway to prevent email enumeration
            return [
                'success' => true,
                'message' => 'If an account exists with this email, you will receive password reset instructions.'
            ];
        }
        
        // Generate reset token
        $resetToken = generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        // Update user
        $db->update('users', [
            'password_reset_token' => $resetToken,
            'password_reset_expires' => $expiresAt
        ], 'id = ?', [$user['id']]);
        
        // Send reset email (uses email.php function)
        sendPasswordResetEmail($user, $resetToken);
        
        // Log activity
        logActivity("Password reset requested: {$email}", 'info');
        
        return [
            'success' => true,
            'message' => 'If an account exists with this email, you will receive password reset instructions.'
        ];
        
    } catch (Exception $e) {
        logActivity("Password reset request error: " . $e->getMessage(), 'error');
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ];
    }
}

/**
 * Reset password with token
 * 
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return array ['success' => bool, 'message' => string]
 */
function resetPassword($token, $newPassword) {
    global $db;
    
    try {
        $user = $db->fetchOne(
            "SELECT id, email FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$token]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset token'
            ];
        }
        
        // Hash new password
        $passwordHash = hashPassword($newPassword);
        
        // Update user
        $db->update('users', [
            'password_hash' => $passwordHash,
            'password_reset_token' => null,
            'password_reset_expires' => null
        ], 'id = ?', [$user['id']]);
        
        // Log activity
        logActivity("Password reset completed: {$user['email']}", 'info');
        
        return [
            'success' => true,
            'message' => 'Password reset successful! You can now login with your new password.'
        ];
        
    } catch (Exception $e) {
        logActivity("Password reset error: " . $e->getMessage(), 'error');
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ];
    }
}

/**
 * Check if session is valid
 * 
 * @return bool
 */
function validateSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['session_lifetime']) && time() > $_SESSION['session_lifetime']) {
        logoutUser();
        return false;
    }
    
    // Check if user still exists and is active
    global $db;
    $user = $db->fetchOne("SELECT status FROM users WHERE id = ?", [currentUserId()]);
    
    if (!$user || $user['status'] !== 'active') {
        logoutUser();
        return false;
    }
    
    return true;
}

/**
 * Send verification email
 * 
 * @param string $email Email address
 * @param string $name User name
 * @param string $token Verification token
 */
function sendVerificationEmail($email, $name, $token) {
    $verifyUrl = url("verify-email.php?token={$token}");
    
    $subject = "Verify Your Email - " . APP_NAME;
    
    ob_start();
    include SRC_PATH . '/mail/verify-email.php';
    $message = ob_get_clean();
    
    sendEmail($email, $subject, $message);
}

/**
 * NOTE: sendEmail(), sendWelcomeEmail(), and sendPasswordResetEmail()
 * are now provided by email.php which uses the Email class with PHPMailer.
 * These duplicate functions have been removed to prevent fatal errors.
 */