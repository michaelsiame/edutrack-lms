<?php
/**
 * Edutrack Computer Training College
 * Application Configuration
 */
define('SITE_NAME', 'EduTrack Zambia'); // Or your preferred site name
// Google Drive Integration
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_DRIVE_FOLDER_ID', '1Qk9LOW92Niv_lci_AM4LFAiK-y2iXbiW'); 

return [
    
    // Application Information
    'name' => getenv('APP_NAME') ?: 'Edutrack Computer Training College',
    'url' => getenv('APP_URL') ?: 'https://edutrackzambia.com',
    
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Lusaka',
    
    // Brand Colors
    'colors' => [
        'primary' => '#2E70DA',      // Edutrack Blue
        'secondary' => '#F6B745',    // Edutrack Gold
        'white' => '#FFFFFF',
        'dark_blue' => '#1E4A8A',
        'light_blue' => '#EBF4FF',
        'dark_gold' => '#D89E2E',
        'light_gold' => '#FDF5E6',
        'success' => '#10B981',
        'danger' => '#EF4444',
        'warning' => '#F59E0B',
        'info' => '#3B82F6',
        'dark' => '#111827',
        'gray' => '#6B7280',
        'light' => '#F9FAFB',
    ],
    
    // TEVETA Configuration
    'teveta' => [
        'enabled' => true,
        'institution_code' => getenv('TEVETA_INSTITUTION_CODE') ?: 'TEVETA/XXX/2024',
        'institution_name' => getenv('TEVETA_INSTITUTION_NAME') ?: 'Edutrack Computer Training College',
        'registration_url' => getenv('TEVETA_REGISTRATION_URL') ?: 'https://www.teveta.org.zm',
        'verified' => filter_var(getenv('TEVETA_VERIFIED'), FILTER_VALIDATE_BOOLEAN),
    ],
    
    // Site Information
    'site' => [
        'email' => getenv('SITE_EMAIL') ?: 'info@edutrackzambia.com', // Updated to match domain
        'phone' => getenv('SITE_PHONE') ?: '+260771216339',
        'phone2' => getenv('SITE_PHONE2'),
        'address' => getenv('SITE_ADDRESS') ?: 'Kalomo, Zambia',
        'currency' => getenv('CURRENCY') ?: 'ZMW',
        'currency_symbol' => getenv('CURRENCY_SYMBOL') ?: 'K',
    ],
    
    // Social Media Links
    'social' => [
        'facebook' => getenv('FACEBOOK_URL') ?: '',
        'twitter' => getenv('TWITTER_URL') ?: '',
        'instagram' => getenv('INSTAGRAM_URL') ?: '',
        'linkedin' => getenv('LINKEDIN_URL') ?: '',
        'youtube' => getenv('YOUTUBE_URL') ?: '',
    ],
    
    // Course Settings
    'courses' => [
        'per_page' => (int) (getenv('COURSES_PER_PAGE') ?: 12),
        'free_preview_lessons' => (int) (getenv('FREE_PREVIEW_LESSONS') ?: 2),
        'auto_enroll_free' => filter_var(getenv('AUTO_ENROLL_FREE_COURSES'), FILTER_VALIDATE_BOOLEAN),
        'certificate_auto_issue' => filter_var(getenv('CERTIFICATE_AUTO_ISSUE'), FILTER_VALIDATE_BOOLEAN),
        'min_completion_percentage' => (int) (getenv('MIN_COMPLETION_PERCENTAGE') ?: 80),
        'min_passing_score' => (int) (getenv('MIN_PASSING_SCORE') ?: 70),
    ],
    
    // File Upload Settings
    'upload' => [
        'max_size' => (int) (getenv('MAX_UPLOAD_SIZE') ?: 52428800), // 50MB
        'max_size_mb' => (int) (getenv('MAX_UPLOAD_SIZE_MB') ?: 50),
        'allowed_images' => explode(',', getenv('ALLOWED_IMAGE_TYPES') ?: 'jpg,jpeg,png,gif,webp'),
        'allowed_documents' => explode(',', getenv('ALLOWED_DOC_TYPES') ?: 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt'),
        'allowed_videos' => explode(',', getenv('ALLOWED_VIDEO_TYPES') ?: 'mp4,avi,mov,wmv'),
        'path' => getenv('UPLOAD_PATH') ?: 'uploads/',
    ],
    
    // Video Platform Settings
    'video' => [
        'default_platform' => getenv('DEFAULT_VIDEO_PLATFORM') ?: 'youtube',
        'youtube_api_key' => getenv('YOUTUBE_API_KEY') ?: '',
        'vimeo_access_token' => getenv('VIMEO_ACCESS_TOKEN') ?: '',
        'bunny_cdn_url' => getenv('BUNNY_CDN_URL') ?: '',
        'bunny_api_key' => getenv('BUNNY_API_KEY') ?: '',
    ],

    // Jitsi Meet Configuration
    'jitsi' => [
        'enabled' => filter_var(getenv('JITSI_ENABLED'), FILTER_VALIDATE_BOOLEAN) !== false,
        'domain' => getenv('JITSI_DOMAIN') ?: 'meet.jit.si', 
        'app_id' => getenv('JITSI_APP_ID') ?: '',
        'app_secret' => getenv('JITSI_APP_SECRET') ?: '',
        'room_prefix' => getenv('JITSI_ROOM_PREFIX') ?: 'edutrack_zm', // Shortened prefix
        'options' => [
            'enableWelcomePage' => false,
            'enableClosePage' => false,
            'prejoinPageEnabled' => true,
            'hideConferenceSubject' => false,
            'hideConferenceTimer' => false,
            'enableNoisyMicDetection' => true,
            'startWithAudioMuted' => false,
            'startWithVideoMuted' => false,
            'enableRecording' => true,
            'liveStreamingEnabled' => false,
            'fileRecordingsEnabled' => true,
            'requireDisplayName' => true,
            'enableInsecureRoomNameWarning' => true,
        ],
        'interface' => [
            'TOOLBAR_BUTTONS' => [
                'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone'
            ],
            'SETTINGS_SECTIONS' => ['devices', 'language', 'moderator', 'profile', 'calendar'],
            'APP_NAME' => 'Edutrack Live Lesson',
            'BRAND_WATERMARK_LINK' => '',
            'SHOW_JITSI_WATERMARK' => false,
            'DEFAULT_BACKGROUND' => '#2E70DA',
        ],
    ],

    // Google OAuth
    'google_oauth' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: '',
    ],

    // Session Configuration
    'session' => [
        'lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 7200),
        'name' => getenv('SESSION_NAME') ?: 'edutrack_session',
        'secure' => filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOLEAN),
        'httponly' => filter_var(getenv('SESSION_HTTPONLY'), FILTER_VALIDATE_BOOLEAN),
        'samesite' => getenv('SESSION_SAMESITE') ?: 'Lax',
    ],
    
    // Security Settings
    'security' => [
        'encryption_key' => getenv('ENCRYPTION_KEY') ?: '',
        'jwt_secret' => getenv('JWT_SECRET') ?: '',
        'csrf_token_name' => getenv('CSRF_TOKEN_NAME') ?: 'csrf_token',
        'password_min_length' => (int) (getenv('PASSWORD_MIN_LENGTH') ?: 8),
        'password_require_uppercase' => filter_var(getenv('PASSWORD_REQUIRE_UPPERCASE'), FILTER_VALIDATE_BOOLEAN),
        'password_require_number' => filter_var(getenv('PASSWORD_REQUIRE_NUMBER'), FILTER_VALIDATE_BOOLEAN),
        'password_require_special' => filter_var(getenv('PASSWORD_REQUIRE_SPECIAL'), FILTER_VALIDATE_BOOLEAN),
    ],
    
    // Rate Limiting
    'rate_limit' => [
        'enabled' => filter_var(getenv('RATE_LIMIT_ENABLED'), FILTER_VALIDATE_BOOLEAN),
        'login_attempts_max' => (int) (getenv('LOGIN_ATTEMPTS_MAX') ?: 5),
        'login_attempts_timeout' => (int) (getenv('LOGIN_ATTEMPTS_TIMEOUT') ?: 900),
    ],
    
    // Maintenance Mode
    'maintenance' => [
        'enabled' => filter_var(getenv('MAINTENANCE_MODE'), FILTER_VALIDATE_BOOLEAN),
        'message' => getenv('MAINTENANCE_MESSAGE') ?: 'We are currently performing scheduled maintenance. Please check back soon.',
    ],
    
    // Analytics
    'analytics' => [
        'google_analytics_id' => getenv('GOOGLE_ANALYTICS_ID') ?: '',
        'facebook_pixel_id' => getenv('FACEBOOK_PIXEL_ID') ?: '',
    ],
    
    // Logging
    'logging' => [
        'level' => getenv('LOG_LEVEL') ?: 'debug',
        'channel' => getenv('LOG_CHANNEL') ?: 'file',
    ],
    
    // Paths
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'storage' => dirname(__DIR__) . '/storage',
        'uploads' => dirname(__DIR__) . '/public/uploads',
        'logs' => dirname(__DIR__) . '/storage/logs',
        'cache' => dirname(__DIR__) . '/storage/cache',
        'sessions' => dirname(__DIR__) . '/storage/sessions',
    ],
    
    // User Roles
    'roles' => [
        'student' => 'student',
        'instructor' => 'instructor',
        'admin' => 'admin',
    ],
    
    // Pagination
    'pagination' => [
        'courses' => 12,
        'students' => 20,
        'instructors' => 20,
        'payments' => 25,
        'certificates' => 20,
    ],
    
];