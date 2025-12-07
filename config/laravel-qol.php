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
];
