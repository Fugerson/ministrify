<?php

/**
 * Security Configuration
 *
 * IMPORTANT: Review these settings before deploying to production!
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for various endpoints to prevent abuse.
    |
    */
    'rate_limits' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'register' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'password_reset' => [
            'max_attempts' => 3,
            'decay_minutes' => 1,
        ],
        'api' => [
            'max_attempts' => 120,
            'decay_minutes' => 1,
        ],
        'public_forms' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Minimum requirements for user passwords.
    |
    */
    'password' => [
        'min_length' => 10,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special' => false, // Optional: enable for higher security
        'check_common_passwords' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Session-related security settings.
    |
    */
    'session' => [
        'regenerate_on_login' => true,
        'invalidate_on_logout' => true,
        'lifetime_minutes' => env('SESSION_LIFETIME', 120),
        'expire_on_close' => false,
        'encrypt' => true,
        'same_site' => 'lax', // Options: 'lax', 'strict', 'none'
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | HTTP security headers configuration.
    |
    */
    'headers' => [
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'hsts_max_age' => 31536000, // 1 year
        'hsts_include_subdomains' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Allowed file types and size limits for uploads.
    |
    */
    'uploads' => [
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'],
        'max_image_size_kb' => 2048, // 2MB
        'max_document_size_kb' => 10240, // 10MB
        'sanitize_filenames' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Encryption
    |--------------------------------------------------------------------------
    |
    | Fields that should be encrypted in the database.
    |
    */
    'encrypted_fields' => [
        'churches' => ['telegram_bot_token'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Actions that should be logged for security audit.
    |
    */
    'audit' => [
        'log_logins' => true,
        'log_logouts' => true,
        'log_failed_logins' => true,
        'log_password_changes' => true,
        'log_permission_changes' => true,
        'log_sensitive_data_access' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Hosts
    |--------------------------------------------------------------------------
    |
    | List of allowed hosts/domains (for production).
    | Set in APP_URL environment variable.
    |
    */
    'allowed_hosts' => [
        env('APP_URL', 'http://localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode Warning
    |--------------------------------------------------------------------------
    |
    | NEVER enable debug mode in production!
    |
    */
    'debug_warning' => env('APP_ENV') === 'production' && env('APP_DEBUG', false)
        ? 'WARNING: Debug mode is enabled in production!'
        : null,
];
