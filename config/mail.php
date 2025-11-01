<?php
/**
 * Mail Configuration
 * Email delivery settings
 */

return [
    // Default mailer
    'default' => getenv('MAIL_MAILER') ?: 'smtp',

    // Mailers
    'mailers' => [
        'smtp' => [
            'driver' => 'smtp',
            'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
            'port' => getenv('MAIL_PORT') ?: 587,
            'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls', // tls or ssl
            'username' => getenv('MAIL_USERNAME'),
            'password' => getenv('MAIL_PASSWORD'),
            'timeout' => 30,
            'auth_mode' => 'login' // login, plain, cram-md5
        ],

        'sendmail' => [
            'driver' => 'sendmail',
            'path' => getenv('MAIL_SENDMAIL_PATH') ?: '/usr/sbin/sendmail -bs'
        ],

        'log' => [
            'driver' => 'log',
            'channel' => 'mail'
        ]
    ],

    // Global "From" Address
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: getenv('MAIL_USERNAME'),
        'name' => getenv('MAIL_FROM_NAME') ?: APP_NAME
    ],

    // Reply-To Address
    'reply_to' => [
        'address' => getenv('MAIL_REPLY_TO_ADDRESS'),
        'name' => getenv('MAIL_REPLY_TO_NAME')
    ],

    // Markdown Mail Settings
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            SRC_PATH . '/mail'
        ]
    ],

    // Email Templates
    'templates' => [
        'welcome' => SRC_PATH . '/mail/welcome.php',
        'verify_email' => SRC_PATH . '/mail/verify-email.php',
        'reset_password' => SRC_PATH . '/mail/reset-password.php',
        'enrollment_confirm' => SRC_PATH . '/mail/enrollment-confirm.php',
        'payment_received' => SRC_PATH . '/mail/payment-received.php',
        'certificate_issued' => SRC_PATH . '/mail/certificate-issued.php'
    ],

    // Queue settings (if implementing email queue)
    'queue' => [
        'enabled' => false,
        'connection' => 'database',
        'queue' => 'emails'
    ],

    // Rate limiting
    'rate_limit' => [
        'enabled' => true,
        'max_per_minute' => 10,
        'max_per_hour' => 100
    ],

    // Email verification
    'verification' => [
        'enabled' => true,
        'expires_in' => 3600 * 24, // 24 hours
        'send_on_register' => true
    ],

    // Testing
    'disable_delivery' => getenv('MAIL_DISABLE_DELIVERY') === 'true',
    'log_all_emails' => getenv('MAIL_LOG_ALL') === 'true',

    // Preferred domains (for gravatar fallback, etc.)
    'trusted_domains' => [
        'gmail.com',
        'yahoo.com',
        'outlook.com',
        'hotmail.com'
    ]
];
