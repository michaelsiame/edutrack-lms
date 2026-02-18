<?php
/**
 * Edutrack computer training college
 * Authentication Functions
 */

/**
 * Register a new user
 * 
 * @param array $data Form data
 * @return array Result with success boolean and message
 */
function registerUser($data) {
    $db = Database::getInstance();
    
    // 1. Sanitize basic data
    $firstName = trim($data['first_name']);
    $lastName = trim($data['last_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'];

    // 2. Auto-generate Username (since form doesn't provide it)
    // Example: john.doe@email.com -> john.doe
    $baseUsername = explode('@', $email)[0];
    // Remove invalid characters
    $baseUsername = preg_replace('/[^a-zA-Z0-9._-]/', '', $baseUsername);
    if (empty($baseUsername)) $baseUsername = 'user';
    
    $username = $baseUsername;
    $counter = 1;

    // Ensure username is unique
    while ($db->fetchColumn("SELECT COUNT(*) FROM users WHERE username = ?", [$username]) > 0) {
        $username = $baseUsername . $counter;
        $counter++;
    }

    // 3. Hash Password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $db->beginTransaction();

        // 4. Insert User
        $sql = "INSERT INTO users (
            username, email, password_hash, first_name, last_name, 
            phone, status, email_verified, created_at, updated_at
        ) VALUES (
            :username, :email, :password_hash, :first_name, :last_name, 
            :phone, 'active', 0, NOW(), NOW()
        )";
        
        $params = [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ];

        if (!$db->query($sql, $params)) {
            // Retrieve specific SQL error for logging
            throw new Exception("Database insert failed.");
        }
        
        $userId = $db->lastInsertId();

        // 5. Assign 'Student' Role (Role ID 4 based on your dump)
        $roleSql = "INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, 4, NOW())";
        $db->query($roleSql, [$userId]);

        // 6. Create Student Profile Record
        // (Assuming you have a students table that links to users)
        $studentSql = "INSERT INTO students (user_id, enrollment_date, created_at) VALUES (?, CURDATE(), NOW())";
        $db->query($studentSql, [$userId]);

        // 7. Create User Profile
        $profileSql = "INSERT INTO user_profiles (user_id, created_at) VALUES (?, NOW())";
        $db->query($profileSql, [$userId]);

        $db->commit();

        // Log activity (using the string format we fixed earlier)
        if (function_exists('logActivity')) {
            logActivity("New user registration: $email ($username)", 'info');
        }

        // Send welcome email
        try {
            $mailer = new Email();
            $mailer->sendWelcome([
                'first_name' => $firstName,
                'email' => $email
            ]);
        } catch (Exception $emailEx) {
            // Don't fail registration if email fails
            error_log("Welcome email failed: " . $emailEx->getMessage());
        }

        return ['success' => true, 'message' => 'Account created successfully. Please login.'];

    } catch (Exception $e) {
        $db->rollBack();
        // Log the actual error to your php error log
        error_log("Registration SQL Error: " . $e->getMessage()); 
        
        // Return a safe message to the user
        return ['success' => false, 'message' => 'Registration failed. Please try again or contact support.'];
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
    $db = Database::getInstance();

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

        // Fetch user's role from User_Roles and Roles tables
        $roleData = $db->fetchOne("
            SELECT r.role_name
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ?
            LIMIT 1
        ", [$user['id']]);

        // Convert role name to lowercase simple format (Admin -> admin, Instructor -> instructor, etc.)
        if ($roleData) {
            $roleName = strtolower(str_replace(' ', '_', $roleData['role_name']));
            // Map role names to expected values
            if (strpos($roleName, 'admin') !== false) {
                $user['role'] = 'admin';
            } elseif (strpos($roleName, 'instructor') !== false) {
                $user['role'] = 'instructor';
            } elseif (strpos($roleName, 'student') !== false) {
                $user['role'] = 'student';
            } else {
                $user['role'] = 'student'; // Default to student
            }
        } else {
            // No role found, default to student
            $user['role'] = 'student';
        }

        if (APP_DEBUG) error_log("User role determined: " . $user['role']);

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
    if (isLoggedIn()) {
        $userId = currentUserId();

        // Delete user sessions from database
        try {
            $db = Database::getInstance();
            $db->delete('user_sessions', 'user_id = ?', [$userId]);
        } catch (Exception $e) {
            // Continue with logout even if session deletion fails
            error_log("Session deletion error during logout: " . $e->getMessage());
        }

        // Log activity
        logActivity("User logged out", 'info');
    }

    // Clear all session variables
    $_SESSION = [];

    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();

    // Start fresh session for flash messages
    session_start();
}

/**
 * Create user session
 * 
 * @param array $user User data
 * @param bool $remember Remember me
 */
function createUserSession($user, $remember = false) {
    $db = Database::getInstance();

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    // Use the role already determined in loginUser() if available, otherwise query
    $_SESSION['user_role'] = $user['role'] ?? getUserRole($user['id']);
    $_SESSION['user_status'] = $user['status'];
    $_SESSION['email_verified'] = $user['email_verified'];

    // Get user avatar
    $profile = $db->fetchOne("SELECT avatar FROM user_profiles WHERE user_id = ?", [$user['id']]);
    $_SESSION['user_avatar'] = $profile['avatar'] ?? null;

    // Set session timeout (2 hours for regular login, 30 days for "remember me")
    $lifetime = $remember ? (30 * 24 * 60 * 60) : config('session.lifetime', 7200);
    $_SESSION['session_lifetime'] = time() + $lifetime;
    $_SESSION['last_activity'] = time();

    // Clean up expired sessions from database
    cleanupExpiredSessions();

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
 * Validate redirect URL to prevent open redirect attacks
 *
 * @param string $url URL to validate
 * @return bool True if valid relative URL, false otherwise
 */
function isValidRedirectUrl($url) {
    if (empty($url)) {
        return false;
    }

    // Only allow relative URLs (no absolute URLs or protocol-relative URLs)
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '//') === 0) {
        return false;
    }

    // Reject URLs with @ (can be used for auth bypass)
    if (strpos($url, '@') !== false) {
        return false;
    }

    // Reject JavaScript protocol
    if (stripos($url, 'javascript:') === 0) {
        return false;
    }

    // Reject data protocol
    if (stripos($url, 'data:') === 0) {
        return false;
    }

    return true;
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

        // SECURITY: Validate redirect URL to prevent open redirect attacks
        if (isValidRedirectUrl($redirect)) {
            return $redirect;
        }
        // If invalid, fall through to default redirect
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
    $db = Database::getInstance();

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
    $db = Database::getInstance();

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
    $db = Database::getInstance();

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
 * Clean up expired sessions from database
 */
function cleanupExpiredSessions() {
    try {
        $db = Database::getInstance();
        // Delete sessions that have expired
        $db->query("DELETE FROM user_sessions WHERE expires_at < NOW()");
    } catch (Exception $e) {
        // Log error but don't break the flow
        error_log("Session cleanup error: " . $e->getMessage());
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

    // Check absolute session timeout (max session lifetime)
    if (isset($_SESSION['session_lifetime']) && time() > $_SESSION['session_lifetime']) {
        logoutUser();
        flash('error', 'Your session has expired. Please login again.', 'warning');
        return false;
    }

    // Check inactivity timeout (30 minutes of inactivity)
    $inactivityTimeout = config('session.inactivity_timeout', 1800); // 30 minutes default
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactivityTimeout) {
        logoutUser();
        flash('error', 'Your session has expired due to inactivity.', 'warning');
        return false;
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();

    // Check if user still exists and is active
    $db = Database::getInstance();
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

/**
 * Login or register a user via Google OAuth
 *
 * If the Google account is already linked, log in directly.
 * If the email exists but isn't linked, link the Google ID and log in.
 * If the email doesn't exist, create a new student account and log in.
 *
 * @param array $googleUser Google user data (id, email, given_name, family_name, picture)
 * @return array ['success' => bool, 'message' => string, 'redirect' => string]
 */
function googleLoginOrRegister($googleUser) {
    $db = Database::getInstance();

    $googleId    = $googleUser['id'];
    $email       = $googleUser['email'];
    $firstName   = $googleUser['given_name'] ?? '';
    $lastName    = $googleUser['family_name'] ?? '';
    $avatarUrl   = $googleUser['picture'] ?? null;

    try {
        // 1. Check if a user is already linked by google_id
        $user = $db->fetchOne("SELECT * FROM users WHERE google_id = ?", [$googleId]);

        if ($user) {
            // Existing Google-linked user — log in
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Your account has been suspended. Please contact support.'];
            }

            $user['role'] = getUserRole($user['id']);
            createUserSession($user, false);
            $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
            logActivity("Google login: {$email}", 'info');

            return [
                'success'  => true,
                'message'  => 'Login successful!',
                'redirect' => getRedirectUrl($user['role'])
            ];
        }

        // 2. Check if a user exists with the same email (registered manually)
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);

        if ($user) {
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Your account has been suspended. Please contact support.'];
            }

            // Link Google ID to existing account
            $db->update('users', [
                'google_id'      => $googleId,
                'email_verified' => 1,
                'last_login'     => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);

            // Update avatar if user doesn't have one
            if ($avatarUrl) {
                $profile = $db->fetchOne("SELECT avatar FROM user_profiles WHERE user_id = ?", [$user['id']]);
                if ($profile && empty($profile['avatar'])) {
                    $db->update('user_profiles', ['avatar' => $avatarUrl], 'user_id = ?', [$user['id']]);
                }
            }

            $user['role'] = getUserRole($user['id']);
            createUserSession($user, false);
            logActivity("Google login (linked existing account): {$email}", 'info');

            return [
                'success'  => true,
                'message'  => 'Login successful! Your Google account has been linked.',
                'redirect' => getRedirectUrl($user['role'])
            ];
        }

        // 3. New user — register with student role
        $db->beginTransaction();

        // Auto-generate username from email
        $baseUsername = explode('@', $email)[0];
        $baseUsername = preg_replace('/[^a-zA-Z0-9._-]/', '', $baseUsername);
        if (empty($baseUsername)) $baseUsername = 'user';

        $username = $baseUsername;
        $counter  = 1;
        while ($db->fetchColumn("SELECT COUNT(*) FROM users WHERE username = ?", [$username]) > 0) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Generate a random password hash (user will sign in via Google, not password)
        $passwordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (
            username, email, google_id, password_hash, first_name, last_name,
            phone, status, email_verified, created_at, updated_at
        ) VALUES (
            :username, :email, :google_id, :password_hash, :first_name, :last_name,
            '', 'active', 1, NOW(), NOW()
        )";

        $params = [
            'username'      => $username,
            'email'         => $email,
            'google_id'     => $googleId,
            'password_hash' => $passwordHash,
            'first_name'    => $firstName,
            'last_name'     => $lastName
        ];

        if (!$db->query($sql, $params)) {
            throw new Exception("Database insert failed.");
        }

        $userId = $db->lastInsertId();

        // Assign Student role (role_id = 4)
        $db->query("INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, 4, NOW())", [$userId]);

        // Create student record
        $db->query("INSERT INTO students (user_id, enrollment_date, created_at) VALUES (?, CURDATE(), NOW())", [$userId]);

        // Create user profile with Google avatar
        $db->query(
            "INSERT INTO user_profiles (user_id, avatar, created_at) VALUES (?, ?, NOW())",
            [$userId, $avatarUrl]
        );

        $db->commit();

        logActivity("New Google registration: $email ($username)", 'info');

        // Send welcome email
        try {
            $mailer = new Email();
            $mailer->sendWelcome([
                'first_name' => $firstName,
                'email'      => $email
            ]);
        } catch (Exception $emailEx) {
            error_log("Welcome email failed for Google signup: " . $emailEx->getMessage());
        }

        // Log the new user in
        $newUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        $newUser['role'] = 'student';
        createUserSession($newUser, false);

        return [
            'success'  => true,
            'message'  => 'Account created successfully! Welcome to Edutrack.',
            'redirect' => getRedirectUrl('student')
        ];

    } catch (Exception $e) {
        try { $db->rollBack(); } catch (Exception $ignored) {}
        error_log("Google Login/Register Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred during Google sign-in. Please try again.'
        ];
    }
}

/**
 * Build the Google OAuth authorization URL
 *
 * @return string The URL to redirect the user to for Google sign-in
 */
function getGoogleAuthUrl() {
    $clientId    = config('google_oauth.client_id');
    $redirectUri = config('google_oauth.redirect_uri');

    if (empty($clientId) || empty($redirectUri)) {
        return '';
    }

    $client = new Google_Client();
    $client->setClientId($clientId);
    $client->setRedirectUri($redirectUri);
    $client->addScope('email');
    $client->addScope('profile');
    $client->setAccessType('online');
    $client->setPrompt('select_account');

    // CSRF protection: store a state token in session
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;
    $client->setState($state);

    return $client->createAuthUrl();
}