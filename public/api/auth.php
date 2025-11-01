<?php
/**
 * Authentication API
 * Handles JWT/token-based authentication for mobile apps and external integrations
 */

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/auth.php';
require_once '../../src/classes/User.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handlePost();
            break;

        case 'GET':
            handleGet();
            break;

        case 'DELETE':
            handleDelete();
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Auth API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * POST - Login, register, refresh token
 */
function handlePost() {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'login';

    switch ($action) {
        case 'login':
            handleLogin($data);
            break;

        case 'register':
            handleRegister($data);
            break;

        case 'refresh':
            handleRefresh($data);
            break;

        case 'verify_token':
            handleVerifyToken($data);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
}

/**
 * GET - Check authentication status
 */
function handleGet() {
    $token = getBearerToken();

    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No token provided']);
        exit;
    }

    $payload = verifyJWT($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
        exit;
    }

    $user = User::find($payload['user_id']);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'user' => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'role' => $user->getRole(),
            'email_verified' => $user->isEmailVerified()
        ]
    ]);
}

/**
 * DELETE - Logout (invalidate token)
 */
function handleDelete() {
    $token = getBearerToken();

    if (!$token) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No token provided']);
        exit;
    }

    // In a production system, you'd add this token to a blacklist/revocation list
    // For now, we'll just return success as JWT tokens expire naturally

    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

/**
 * Handle login
 */
function handleLogin($data) {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        exit;
    }

    $db = Database::getInstance();
    $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        exit;
    }

    // Check if account is active
    if ($user['status'] !== 'active') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Account is not active']);
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        exit;
    }

    // Generate JWT tokens
    $accessToken = generateJWT($user['id'], $user['role']);
    $refreshToken = generateRefreshToken($user['id']);

    // Update last login
    $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

    // Log activity
    logActivity([
        'user_id' => $user['id'],
        'action' => 'api_login',
        'details' => 'User logged in via API'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'token_type' => 'Bearer',
        'expires_in' => 3600, // 1 hour
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'email_verified' => (bool)$user['email_verified']
        ]
    ]);
}

/**
 * Handle registration
 */
function handleRegister($data) {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');

    // Validation
    $errors = [];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Check if email exists
    $db = Database::getInstance();
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);

    if ($existing) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        exit;
    }

    // Create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $verificationToken = bin2hex(random_bytes(32));

    $sql = "INSERT INTO users (email, password_hash, first_name, last_name, role, email_verification_token, created_at)
            VALUES (?, ?, ?, ?, 'student', ?, NOW())";

    if ($db->query($sql, [$email, $passwordHash, $firstName, $lastName, $verificationToken])) {
        $userId = $db->lastInsertId();

        // Generate tokens
        $accessToken = generateJWT($userId, 'student');
        $refreshToken = generateRefreshToken($userId);

        // Log activity
        logActivity([
            'user_id' => $userId,
            'action' => 'api_register',
            'details' => 'User registered via API'
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => [
                'id' => $userId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'student',
                'email_verified' => false
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Registration failed']);
    }
}

/**
 * Handle token refresh
 */
function handleRefresh($data) {
    $refreshToken = $data['refresh_token'] ?? '';

    if (empty($refreshToken)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Refresh token required']);
        exit;
    }

    $payload = verifyRefreshToken($refreshToken);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired refresh token']);
        exit;
    }

    // Get user
    $user = User::find($payload['user_id']);

    if (!$user || $user->getStatus() !== 'active') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not found or inactive']);
        exit;
    }

    // Generate new tokens
    $newAccessToken = generateJWT($user->getId(), $user->getRole());
    $newRefreshToken = generateRefreshToken($user->getId());

    echo json_encode([
        'success' => true,
        'access_token' => $newAccessToken,
        'refresh_token' => $newRefreshToken,
        'token_type' => 'Bearer',
        'expires_in' => 3600
    ]);
}

/**
 * Handle token verification
 */
function handleVerifyToken($data) {
    $token = $data['token'] ?? '';

    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token required']);
        exit;
    }

    $payload = verifyJWT($token);

    if (!$payload) {
        echo json_encode([
            'success' => true,
            'valid' => false,
            'message' => 'Invalid or expired token'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'valid' => true,
        'user_id' => $payload['user_id'],
        'role' => $payload['role'],
        'expires_at' => $payload['exp']
    ]);
}

/**
 * Generate JWT access token
 */
function generateJWT($userId, $role, $expiresIn = 3600) {
    $header = base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

    $payload = [
        'user_id' => $userId,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + $expiresIn
    ];
    $payload = base64UrlEncode(json_encode($payload));

    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", getJWTSecret(), true));

    return "$header.$payload.$signature";
}

/**
 * Generate refresh token (longer expiry)
 */
function generateRefreshToken($userId, $expiresIn = 2592000) { // 30 days
    $header = base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

    $payload = [
        'user_id' => $userId,
        'type' => 'refresh',
        'iat' => time(),
        'exp' => time() + $expiresIn
    ];
    $payload = base64UrlEncode(json_encode($payload));

    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", getJWTSecret(), true));

    return "$header.$payload.$signature";
}

/**
 * Verify JWT token
 */
function verifyJWT($token) {
    $parts = explode('.', $token);

    if (count($parts) !== 3) {
        return false;
    }

    list($header, $payload, $signature) = $parts;

    $validSignature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", getJWTSecret(), true));

    if ($signature !== $validSignature) {
        return false;
    }

    $payload = json_decode(base64UrlDecode($payload), true);

    // Check expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }

    return $payload;
}

/**
 * Verify refresh token
 */
function verifyRefreshToken($token) {
    $payload = verifyJWT($token);

    if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
        return false;
    }

    return $payload;
}

/**
 * Get bearer token from Authorization header
 */
function getBearerToken() {
    $headers = getallheaders();

    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }

    return null;
}

/**
 * Base64 URL encode
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL decode
 */
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Get JWT secret from environment
 */
function getJWTSecret() {
    $secret = getenv('JWT_SECRET');

    if (!$secret) {
        // Fallback to APP_KEY or generate warning
        $secret = getenv('APP_KEY') ?: 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION';
        error_log('Warning: JWT_SECRET not set in environment. Using fallback.');
    }

    return $secret;
}
