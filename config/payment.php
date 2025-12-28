<?php
/**
 * Payment Configuration
 * Payment gateway settings
 */

return [
    // Default currency
    'currency' => 'ZMW',
    'currency_symbol' => 'K',

    // Payment Method IDs (from payment_methods database table)
    // Use these constants instead of hard-coding IDs throughout the codebase
    'method_ids' => [
        'credit_card' => 1,    // Credit Card (Visa, Mastercard, American Express)
        'mobile_money' => 2,   // Mobile Money (MTN, Airtel)
        'bank_transfer' => 3,  // Bank Transfer (Direct bank transfer)
        'paypal' => 4,         // PayPal payment gateway
        'cash' => 5,           // Cash payment at office
    ],
    
    // Tax settings
    'tax_enabled' => false,
    'tax_rate' => 0, // 16% VAT
    'tax_label' => 'VAT',
    
    // MTN Mobile Money
    'mtn' => [
        'enabled' => true,
        'name' => 'MTN Mobile Money',
        'api_url' => env('MTN_API_URL', 'https://api.mtn.com/collection'),
        'api_key' => env('MTN_API_KEY', ''),
        'api_secret' => env('MTN_API_SECRET', ''),
        'subscription_key' => env('MTN_SUBSCRIPTION_KEY', ''),
        'callback_url' => env('APP_URL') . '/api/payment-callback.php?provider=mtn'
    ],
    
    // Airtel Money
    'airtel' => [
        'enabled' => true,
        'name' => 'Airtel Money',
        'api_url' => env('AIRTEL_API_URL', 'https://openapi.airtel.africa'),
        'client_id' => env('AIRTEL_CLIENT_ID', ''),
        'client_secret' => env('AIRTEL_CLIENT_SECRET', ''),
        'callback_url' => env('APP_URL') . '/api/payment-callback.php?provider=airtel'
    ],
    
    // Zamtel Kwacha
    'zamtel' => [
        'enabled' => true,
        'name' => 'Zamtel Kwacha',
        'api_url' => env('ZAMTEL_API_URL', ''),
        'merchant_id' => env('ZAMTEL_MERCHANT_ID', ''),
        'api_key' => env('ZAMTEL_API_KEY', ''),
        'callback_url' => env('APP_URL') . '/api/payment-callback.php?provider=zamtel'
    ],

    // Lenco Payment Gateway (Bank Transfer)
    'lenco' => [
        'enabled' => true,
        'name' => 'Lenco Bank Transfer',
        'description' => 'Pay via bank transfer using Lenco virtual accounts',
        'live_mode' => env('LENCO_LIVE_MODE', false),
        'sandbox' => [
            'api_url' => 'https://api.sandbox.lenco.co',
            'api_key' => env('LENCO_SANDBOX_API_KEY', ''),
            'secret_key' => env('LENCO_SANDBOX_SECRET_KEY', ''),
        ],
        'live' => [
            'api_url' => 'https://api.lenco.co',
            'api_key' => env('LENCO_LIVE_API_KEY', ''),
            'secret_key' => env('LENCO_LIVE_SECRET_KEY', ''),
        ],
        'webhook_secret' => env('LENCO_WEBHOOK_SECRET', ''),
        'callback_url' => env('APP_URL') . '/api/lenco-webhook.php',
        'transaction_expiry' => 86400, // 24 hours in seconds
        'supported_currencies' => ['ZMW', 'NGN', 'USD'],
        'min_amount' => 10, // Minimum transaction amount
        'max_amount' => 1000000, // Maximum transaction amount
    ],

    // Bank Transfer
    'bank_transfer' => [
        'enabled' => true,
        'name' => 'Bank Transfer',
        'banks' => [
            [
                'name' => 'Zanaco',
                'account_name' => 'Edutrack computer training college',
                'account_number' => '1234567890',
                'branch' => 'Cairo Road Branch',
                'swift_code' => 'ZANAZMLX'
            ],
            [
                'name' => 'FNB',
                'account_name' => 'Edutrack computer training college',
                'account_number' => '62345678901',
                'branch' => 'Lusaka Main',
                'swift_code' => 'FIRNZMLX'
            ]
        ],
        'instructions' => 'Please use your payment reference number as the transaction reference when making the transfer.'
    ],
    
    // Payment verification
    'verification' => [
        'auto_verify' => false, // Set to true to auto-verify payments
        'manual_approval_required' => true,
        'verification_timeout' => 600, // 10 minutes
    ],
    
    // Refund settings
    'refunds' => [
        'enabled' => true,
        'refund_period_days' => 7, // Days within which refunds are allowed
        'partial_refunds' => true,
        'refund_fee_percentage' => 0
    ],
    
    // Invoice settings
    'invoice' => [
        'prefix' => 'INV',
        'auto_generate' => true,
        'send_email' => true,
        'include_logo' => true,
        'footer_text' => 'Thank you for choosing Edutrack computer training college'
    ],
    
    // Security
    'security' => [
        'require_verification_code' => false,
        'max_payment_attempts' => 3,
        'payment_timeout' => 1800, // 30 minutes
        'encrypt_sensitive_data' => true
    ],
    
    // Notifications
    'notifications' => [
        'email_on_success' => true,
        'email_on_failure' => true,
        'sms_on_success' => false,
        'admin_notification' => true
    ]
];