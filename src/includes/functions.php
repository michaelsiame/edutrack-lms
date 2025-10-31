<?php
/**
 * Edutrack Computer Training College
 * Helper Functions
 */

/**
 * Sanitize output to prevent XSS
 * 
 * @param string $string Input string
 * @return string Sanitized string
 */
function sanitize($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape HTML
 * Alias for sanitize
 */
function e($string) {
    return sanitize($string);
}

/**
 * Format currency
 * 
 * @param float $amount Amount to format
 * @param bool $includeSymbol Include currency symbol
 * @return string
 */
function formatCurrency($amount, $includeSymbol = true) {
    $formatted = number_format($amount, 2);
    return $includeSymbol ? CURRENCY_SYMBOL . $formatted : $formatted;
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string
 */
function formatDate($date, $format = 'F j, Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Format date and time
 * 
 * @param string $datetime Datetime string
 * @param string $format Datetime format
 * @return string
 */
function formatDateTime($datetime, $format = 'F j, Y \a\t g:i A') {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

/**
 * Time ago helper
 * 
 * @param string $datetime Datetime string
 * @return string
 */
function timeAgo($datetime) {
    if (empty($datetime)) {
        return 'N/A';
    }
    
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $mins = floor($difference / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 2592000) {
        $weeks = floor($difference / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 31536000) {
        $months = floor($difference / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($difference / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Generate slug from string
 * 
 * @param string $string Input string
 * @return string
 */
function slugify($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Truncate string
 * 
 * @param string $string Input string
 * @param int $length Max length
 * @param string $append String to append
 * @return string
 */
function truncate($string, $length = 100, $append = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    
    $string = substr($string, 0, $length);
    $string = substr($string, 0, strrpos($string, ' '));
    
    return $string . $append;
}

/**
 * Get file size in human readable format
 * 
 * @param int $bytes File size in bytes
 * @return string
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Generate random string
 * 
 * @param int $length String length
 * @return string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate verification token
 * 
 * @return string
 */
function generateToken() {
    return generateRandomString(64);
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null
 */
function currentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if current user has role
 * 
 * @param string|array $roles Role(s) to check
 * @return bool
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = currentUserRole();
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return $userRole === $roles;
}

/**
 * Require authentication
 * Redirect to login if not logged in
 * 
 * @param string $redirectUrl URL to redirect after login
 */
function requireAuth($redirectUrl = null) {
    if (!isLoggedIn()) {
        $redirect = $redirectUrl ?? $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $redirect;
        redirect(url('login.php'));
    }
}

/**
 * Require specific role
 * 
 * @param string|array $roles Required role(s)
 */
function requireRole($roles) {
    requireAuth();
    
    if (!hasRole($roles)) {
        http_response_code(403);
        die('Access Denied: You do not have permission to access this page.');
    }
}

/**
 * Flash message helper
 * 
 * @param string $key Message key
 * @param string $message Message text
 * @param string $type Message type (success, error, warning, info)
 */
function flash($key, $message = null, $type = 'info') {
    if ($message === null) {
        // Get flash message
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    } else {
        // Set flash message
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }
}

/**
 * Get flash message and display HTML
 * 
 * @param string $key Message key
 * @return string
 */
function getFlash($key = 'message') {
    $flash = flash($key);
    
    if ($flash) {
        $alertClass = [
            'success' => 'bg-green-50 border-green-500 text-green-800',
            'error' => 'bg-red-50 border-red-500 text-red-800',
            'warning' => 'bg-yellow-50 border-yellow-500 text-yellow-800',
            'info' => 'bg-blue-50 border-blue-500 text-blue-800',
        ];
        
        $iconClass = [
            'success' => 'fa-check-circle',
            'error' => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle',
        ];
        
        $class = $alertClass[$flash['type']] ?? $alertClass['info'];
        $icon = $iconClass[$flash['type']] ?? $iconClass['info'];
        
        return '<div class="' . $class . ' border-l-4 p-4 mb-4 rounded">
                    <div class="flex items-center">
                        <i class="fas ' . $icon . ' mr-3"></i>
                        <p>' . sanitize($flash['message']) . '</p>
                    </div>
                </div>';
    }
    
    return '';
}

/**
 * Debug dump
 * 
 * @param mixed $data Data to dump
 * @param bool $die Die after dump
 */
function dd($data, $die = true) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Print formatted array
 * 
 * @param mixed $data Data to print
 * @param bool $die Die after print
 */
function pr($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Get client IP address
 * 
 * @return string
 */
function getClientIp() {
    $ip = '';
    
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

/**
 * Get user agent
 * 
 * @return string
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Generate gravatar URL
 * 
 * @param string $email Email address
 * @param int $size Image size
 * @return string
 */
function gravatar($email, $size = 80) {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
}

/**
 * Get user avatar URL
 * 
 * @param string|null $avatar Avatar filename
 * @param string $email Email for gravatar fallback
 * @return string
 */
function userAvatar($avatar = null, $email = '') {
    if ($avatar && file_exists(UPLOAD_PATH . '/users/avatars/' . $avatar)) {
        return uploadUrl('users/avatars/' . $avatar);
    }
    
    if ($email) {
        return gravatar($email);
    }
    
    return asset('images/default-avatar.png');
}

/**
 * Get course thumbnail URL
 * 
 * @param string|null $thumbnail Thumbnail filename
 * @return string
 */
function courseThumbnail($thumbnail = null) {
    if ($thumbnail && file_exists(UPLOAD_PATH . '/courses/thumbnails/' . $thumbnail)) {
        return uploadUrl('courses/thumbnails/' . $thumbnail);
    }
    
    return asset('images/default-course.jpg');
}

/**
 * Parse YouTube video ID from URL
 * 
 * @param string $url YouTube URL
 * @return string|null
 */
function parseYoutubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Parse Vimeo video ID from URL
 * 
 * @param string $url Vimeo URL
 * @return string|null
 */
function parseVimeoId($url) {
    $pattern = '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|)(\d+)(?:$|\/|\?)/';
    
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Get video embed HTML
 * 
 * @param string $url Video URL
 * @param string $platform Platform (youtube, vimeo)
 * @return string
 */
function videoEmbed($url, $platform = 'youtube') {
    if ($platform === 'youtube') {
        $videoId = parseYoutubeId($url);
        if ($videoId) {
            return '<iframe width="100%" height="500" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
    } elseif ($platform === 'vimeo') {
        $videoId = parseVimeoId($url);
        if ($videoId) {
            return '<iframe src="https://player.vimeo.com/video/' . $videoId . '" width="100%" height="500" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }
    }
    
    return '<p class="text-red-500">Invalid video URL</p>';
}

/**
 * Log activity
 * 
 * @param string $message Log message
 * @param string $level Log level (info, warning, error)
 */
function logActivity($message, $level = 'info') {
    $logDir = STORAGE_PATH . '/logs';
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $userId = currentUserId() ?? 'guest';
    $logMessage = "[{$timestamp}] [{$level}] [User: {$userId}] {$message}\n";
    
    error_log($logMessage, 3, $logFile);
}