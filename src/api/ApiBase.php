<?php
/**
 * API Base Class
 * Standard base class for all API endpoints
 * Reduces code duplication and standardizes API responses
 */

abstract class ApiBase {
    protected $db;
    protected $userId;
    protected $user;
    protected $method;
    protected $data;
    protected $requireAuth = true;

    public function __construct() {
        // Set JSON header
        header('Content-Type: application/json');

        // Get HTTP method
        $this->method = $_SERVER['REQUEST_METHOD'];

        // Initialize database
        $this->db = Database::getInstance();

        // Parse request data
        $this->parseRequestData();

        // Handle authentication
        if ($this->requireAuth) {
            $this->handleAuthentication();
        }

        // Set CORS headers if needed
        $this->handleCors();
    }

    /**
     * Parse incoming request data
     */
    private function parseRequestData() {
        $this->data = [];

        if ($this->method === 'GET') {
            $this->data = $_GET;
        } elseif ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'PATCH' || $this->method === 'DELETE') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') !== false) {
                $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                $this->data = $_POST;

                // For PUT/PATCH/DELETE, also try to parse php://input
                if (in_array($this->method, ['PUT', 'PATCH', 'DELETE'])) {
                    parse_str(file_get_contents('php://input'), $parsedData);
                    if (is_array($parsedData)) {
                        $this->data = array_merge($this->data, $parsedData);
                    }
                }
            }
        }
    }

    /**
     * Handle authentication
     */
    private function handleAuthentication() {
        // Check for JWT token
        $token = $this->getBearerToken();

        if ($token) {
            // JWT authentication
            $payload = $this->verifyJWT($token);

            if (!$payload) {
                $this->errorResponse('Invalid or expired token', 401);
            }

            $this->userId = $payload['user_id'];
            $this->user = User::find($this->userId);

            if (!$this->user) {
                $this->errorResponse('User not found', 401);
            }
        } else {
            // Session authentication
            if (!function_exists('isLoggedIn')) {
                // Load auth helper if not already loaded
                require_once dirname(__DIR__) . '/includes/auth.php';
            }

            if (!isLoggedIn()) {
                $this->errorResponse('Unauthorized - Authentication required', 401);
            }

            $this->userId = currentUserId();
            $this->user = User::find($this->userId);
        }
    }

    /**
     * Handle CORS
     */
    private function handleCors() {
        // CORRECTION: Use getenv() instead of undefined constant APP_URL
        $allowedOrigins = [
            'http://localhost:3000', 
            'http://localhost:8000',
            getenv('APP_URL'),
            'https://edutrackzambia.com',
            'https://www.edutrackzambia.com'
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // If origin is in allowed list, or we are in debug mode allowing all
        if (in_array($origin, $allowedOrigins) || (getenv('APP_DEBUG') === 'true' && $origin)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        }

        // Handle preflight
        if ($this->method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Get bearer token from Authorization header
     */
    private function getBearerToken() {
        // CORRECTION: Polyfill for getallheaders() if missing on server
        if (!function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }

        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Verify JWT token (simple implementation)
     */
    private function verifyJWT($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        $secret = getenv('JWT_SECRET');
        if (!$secret) {
            // Log error if secret is missing
            error_log('JWT_SECRET is missing in .env');
            return false;
        }

        $validSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true)), '+/', '-_'), '=');

        if ($signature !== $validSignature) {
            return false;
        }

        $payload = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Success response
     */
    protected function successResponse($data = [], $message = '', $code = 200) {
        http_response_code($code);

        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        $response = array_merge($response, $data);

        echo json_encode($response);
        exit;
    }

    /**
     * Error response
     */
    protected function errorResponse($error, $code = 400, $details = []) {
        http_response_code($code);

        $response = [
            'success' => false,
            'error' => $error
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors) {
        $this->errorResponse('Validation failed', 422, ['validation_errors' => $errors]);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse($message = 'Resource not found') {
        $this->errorResponse($message, 404);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse($message = 'Access denied') {
        $this->errorResponse($message, 403);
    }

    /**
     * Method not allowed response
     */
    protected function methodNotAllowedResponse() {
        $this->errorResponse('Method not allowed', 405);
    }

    /**
     * Get request data
     */
    protected function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if key exists in request data
     */
    protected function has($key) {
        return isset($this->data[$key]);
    }

    /**
     * Validate required fields
     */
    protected function validate($rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->get($field);

            foreach (explode('|', $fieldRules) as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field][] = ucfirst($field) . ' is required';
                }

                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = ucfirst($field) . ' must be a valid email address';
                }

                if (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if (!empty($value) && strlen($value) < $min) {
                        $errors[$field][] = ucfirst($field) . " must be at least $min characters";
                    }
                }

                if (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if (!empty($value) && strlen($value) > $max) {
                        $errors[$field][] = ucfirst($field) . " must not exceed $max characters";
                    }
                }

                if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field][] = ucfirst($field) . ' must be a number';
                }
            }
        }

        if (!empty($errors)) {
            $this->validationErrorResponse($errors);
        }

        return true;
    }

    /**
     * Check if user has role
     */
    protected function requireRole($roles) {
        if (!$this->user || !$this->user->hasRole($roles)) {
            $this->forbiddenResponse('You do not have permission to perform this action');
        }
    }

    /**
     * Check if user is admin
     */
    protected function requireAdmin() {
        $this->requireRole('admin');
    }

    /**
     * Check if user is instructor or admin
     */
    protected function requireInstructor() {
        $this->requireRole(['instructor', 'admin']);
    }

/**
     * Log API activity
     */
    protected function logActivity($action, $details = '') {
        if (function_exists('logActivity')) {
            // Prepare the context data
            $context = [
                'user_id' => $this->userId,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ];

            logActivity($action, json_encode($context));
        }
    }
    /**
     * Main handler - must be implemented by child classes
     */
    abstract public function handle();
}