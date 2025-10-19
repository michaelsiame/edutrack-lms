<?php
/**
 * Additional Helper Functions
 * Utility functions for the application
 */

/**
 * Format file size
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
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
 * Time ago helper
 * 
 * @param string $datetime Datetime string
 * @return string Time ago format
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Get course thumbnail URL
 * 
 * @param string|null $thumbnail Thumbnail path
 * @return string Thumbnail URL
 */
function courseThumbnail($thumbnail) {
    if ($thumbnail && file_exists(PUBLIC_PATH . '/uploads/' . $thumbnail)) {
        return url('uploads/' . $thumbnail);
    }
    return url('assets/images/default-course.jpg');
}

/**
 * Get user avatar URL (Gravatar)
 * 
 * @param string $email User email
 * @param int $size Avatar size
 * @return string Avatar URL
 */
function getGravatar($email, $size = 80) {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
}

/**
 * CSRF Token Field
 * 
 * @return string CSRF input field HTML
 */
function csrfField() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Validate CSRF Token
 * 
 * @return bool
 */
function validateCSRF() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    
    if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
    
    return true;
}

/**
 * Truncate text
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to append
 * @return string Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Slugify text
 * 
 * @param string $text Text to slugify
 * @return string Slugified text
 */
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Format duration (minutes to hours:minutes)
 * 
 * @param int $minutes Duration in minutes
 * @return string Formatted duration
 */
function formatDuration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . 'h ' . $mins . 'm';
}

/**
 * Check if video URL is YouTube
 * 
 * @param string $url Video URL
 * @return bool
 */
function isYouTube($url) {
    return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;
}

/**
 * Get YouTube video ID from URL
 * 
 * @param string $url YouTube URL
 * @return string|null Video ID
 */
function getYouTubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches);
    return $matches[1] ?? null;
}

/**
 * Check if video URL is Vimeo
 * 
 * @param string $url Video URL
 * @return bool
 */
function isVimeo($url) {
    return strpos($url, 'vimeo.com') !== false;
}

/**
 * Get Vimeo video ID from URL
 * 
 * @param string $url Vimeo URL
 * @return string|null Video ID
 */
function getVimeoId($url) {
    preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
    return $matches[1] ?? null;
}

/**
 * Get video embed HTML
 * 
 * @param string $url Video URL
 * @return string Embed HTML
 */
function getVideoEmbed($url) {
    if (isYouTube($url)) {
        $id = getYouTubeId($url);
        return '<iframe width="100%" height="500" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
    } elseif (isVimeo($url)) {
        $id = getVimeoId($url);
        return '<iframe width="100%" height="500" src="https://player.vimeo.com/video/' . $id . '" frameborder="0" allowfullscreen></iframe>';
    } else {
        return '<video width="100%" height="500" controls><source src="' . $url . '" type="video/mp4"></video>';
    }
}

/**
 * Calculate reading time
 * 
 * @param string $text Text content
 * @return int Reading time in minutes
 */
function readingTime($text) {
    $wordCount = str_word_count(strip_tags($text));
    $minutes = ceil($wordCount / 200); // Average reading speed: 200 words/min
    return max(1, $minutes);
}

/**
 * Generate random string
 * 
 * @param int $length String length
 * @return string Random string
 */
function randomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if user owns resource
 * 
 * @param int $resourceUserId Resource owner ID
 * @return bool
 */
function ownsResource($resourceUserId) {
    return isLoggedIn() && currentUserId() == $resourceUserId;
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Check if date is past
 * 
 * @param string $date Date string
 * @return bool
 */
function isPast($date) {
    return strtotime($date) < time();
}

/**
 * Check if date is future
 * 
 * @param string $date Date string
 * @return bool
 */
function isFuture($date) {
    return strtotime($date) > time();
}

/**
 * Get percentage
 * 
 * @param float $value Current value
 * @param float $total Total value
 * @param int $decimals Decimal places
 * @return float Percentage
 */
function percentage($value, $total, $decimals = 0) {
    if ($total == 0) return 0;
    return round(($value / $total) * 100, $decimals);
}

/**
 * Array get with default
 * 
 * @param array $array Array
 * @param string $key Key
 * @param mixed $default Default value
 * @return mixed Value or default
 */
function array_get($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

/**
 * Pluralize word
 * 
 * @param int $count Count
 * @param string $singular Singular form
 * @param string|null $plural Plural form
 * @return string Pluralized word
 */
function pluralize($count, $singular, $plural = null) {
    if ($count == 1) {
        return $singular;
    }
    return $plural ?? $singular . 's';
}

/**
 * JSON response
 * 
 * @param array $data Response data
 * @param int $code HTTP status code
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Success JSON response
 * 
 * @param mixed $data Response data
 * @param string $message Success message
 */
function jsonSuccess($data = null, $message = 'Success') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Error JSON response
 * 
 * @param string $message Error message
 * @param int $code HTTP status code
 */
function jsonError($message, $code = 400) {
    jsonResponse([
        'success' => false,
        'error' => $message
    ], $code);
}

/**
 * Dump and die (for debugging)
 * 
 * @param mixed $var Variable to dump
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function getClientIP() {
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }
    return 'UNKNOWN';
}

/**
 * Check if request is mobile
 * 
 * @return bool
 */
function isMobile() {
    return preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
}