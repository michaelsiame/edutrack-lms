<?php
/**
 * Additional Helper Functions
 * Utility functions for the application
 *
 * Note: Duplicate functions that exist in functions.php have been removed
 * to avoid redeclaration errors. This file now only contains unique helpers.
 */

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

if (!function_exists('upload_url')) {
    function upload_url($path = '') {
        // Returns URL to uploads folder
        return APP_URL . '/uploads/' . ltrim($path, '/');
    }
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
 * Format currency for display
 */
function formatCurrency($amount, $currency = null) {
    if (is_null($currency)) {
        $currency = getSystemCurrency();
    }
    
    // Ensure amount is float
    $amount = (float)$amount;
    
    // Format with thousands separators
    $formatted = number_format($amount, 2, '.', ',');
    
    return $currency . ' ' . $formatted;
}

/**
 * Get system currency from settings
 */
function getSystemCurrency() {
    // Default to ZMW for Zambia
    $defaultCurrency = 'ZMW';
    
    // Check if we can access database
    if (defined('DB_HOST') && class_exists('Database')) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'currency'";
            $result = $db->query($sql)->fetch();
            
            if ($result && !empty($result['setting_value'])) {
                return $result['setting_value'];
            }
        } catch (Exception $e) {
            // Silently fail and return default
        }
    }
    
    return $defaultCurrency;
}

/**
 * Safe debug function for production
 */
function debugPrice($value, $label = 'Price') {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<!-- DEBUG $label: ";
        var_dump($value);
        echo " -->\n";
    }
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
 * Check if date is past
 *
 * @param string $date Date string
 * @return bool
 */
function isPast($date) {
    if (empty($date)) return false;
    return strtotime($date) < time();
}

/**
 * Check if date is future
 *
 * @param string $date Date string
 * @return bool
 */
function isFuture($date) {
    if (empty($date)) return false;
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
 * Check if request is mobile
 *
 * @return bool
 */
function isMobile() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/(android|iphone|ipad|mobile)/i', $ua);
}

/**
 * Missing function for course detail page
 * Format time ago (e.g., "2 days ago")
 */
if (!function_exists('timeAgo')) {
    function timeAgo($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        
        // Build the output array
        $output = [];
        foreach ($string as $key => $text) {
            if ($diff->$key) {
                $value = $diff->$key;
                $output[] = $value . ' ' . $text . ($value > 1 ? 's' : '');
            }
        }
        
        // If no time difference, return "just now"
        if (empty($output)) {
            return 'just now';
        }
        
        if (!$full && count($output) > 1) {
            $output = array_slice($output, 0, 1);
        }
        
        return implode(', ', $output) . ' ago';
    }
}

/**
 * Missing function for course detail page
 * Get asset URL
 */
if (!function_exists('asset')) {
    function asset($path) {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

/**
 * Missing function for course detail page
 * Sanitize output
 */
if (!function_exists('sanitize')) {
    function sanitize($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Missing function for course detail page
 * Create URL-friendly slug
 */
if (!function_exists('slugify')) {
    function slugify($text) {
        // Replace non-letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }
}