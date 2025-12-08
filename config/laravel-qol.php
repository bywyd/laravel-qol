<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default History Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the default behavior of the HasHistory trait.
    |
    */
    'history' => [
        // Default attributes to exclude from history logging
        'excluded_attributes' => [
            'password',
            'remember_token',
            'updated_at',
        ],

        // Whether to delete history records when the parent model is deleted
        'delete_on_model_delete' => true,

        // Events to track by default
        'tracked_events' => ['created', 'updated', 'deleted'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Settings
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for the HasImages trait.
    |
    */
    'images' => [
        // Default storage disk
        'disk' => env('LARAVEL_QOL_IMAGE_DISK', 'public'),

        // Default storage path
        'path' => env('LARAVEL_QOL_IMAGE_PATH', 'images'),

        // Allowed mime types
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],

        // Maximum file size in kilobytes
        'max_size' => 10240, // 10MB
    ],

    /*
    |--------------------------------------------------------------------------
    | File Settings
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for the HasFiles trait.
    |
    */
    'files' => [
        // Default storage disk
        'disk' => env('LARAVEL_QOL_FILE_DISK', 'public'),

        // Default storage path
        'path' => env('LARAVEL_QOL_FILE_PATH', 'files'),

        // Allowed mime types (null = allow all)
        'allowed_mimes' => null,

        // Maximum file size in kilobytes
        'max_size' => 51200, // 50MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Settings
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for the HasVideos trait.
    |
    */
    'videos' => [
        // Default storage disk
        'disk' => env('LARAVEL_QOL_VIDEO_DISK', 'public'),

        // Default storage path
        'path' => env('LARAVEL_QOL_VIDEO_PATH', 'videos'),

        // Allowed mime types
        'allowed_mimes' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/webm'],

        // Maximum file size in kilobytes
        'max_size' => 102400, // 100MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions & Roles Settings
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for the roles and permissions system.
    |
    */
    'permissions' => [
        // Automatically register permissions as Laravel gates
        'auto_register_permissions_as_gates' => true,

        // Enable Blade directives (@role, @permission, etc.)
        'enable_blade_directives' => true,

        // Cache permissions for performance
        'cache_permissions' => true,

        // Cache TTL in seconds
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization Configuration
    |--------------------------------------------------------------------------
    */

    'localization' => [
        'supported_locales' => ['en', 'es', 'fr', 'de', 'ar', 'zh', 'ja', 'pt', 'ru', 'it'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Restriction Configuration
    |--------------------------------------------------------------------------
    */

    'access_restriction' => [
        'enabled' => env('ACCESS_RESTRICTION_ENABLED', false),
        'allowed_ips' => env('ALLOWED_IPS') ? explode(',', env('ALLOWED_IPS')) : [],
        'allowed_roles' => ['super-admin', 'admin'],
        'bypass_token' => env('BYPASS_TOKEN'),
        'message' => 'Service temporarily unavailable. We will be back soon!',
        'view' => 'laravel-qol::maintenance',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    */

    'security_headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'enable_hsts' => true,
        'hsts_max_age' => 31536000, // 1 year
        'content_security_policy' => null, // e.g., "default-src 'self'"
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'channel' => env('LOG_CHANNEL', 'stack'),
        'log_requests' => true,
        'log_responses' => true,
        'log_request_body' => false,
        'log_response_body' => false,
        'log_headers' => false,
        'sensitive_keys' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'api_secret',
            'secret',
            'credit_card',
            'cvv',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trim Strings Configuration
    |--------------------------------------------------------------------------
    */

    'trim_strings' => [
        'except' => [
            'current_password',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versioning Configuration
    |--------------------------------------------------------------------------
    */

    'api_versioning' => [
        'default_version' => 'v1',
        'supported_versions' => ['v1', 'v2'],
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    */

    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'X-API-Version'],
        'exposed_headers' => [],
        'max_age' => 3600,
        'allow_credentials' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Configuration
    |--------------------------------------------------------------------------
    */

    'settings' => [
        // Cache TTL in seconds
        'cache_ttl' => 3600,
    ],
];
