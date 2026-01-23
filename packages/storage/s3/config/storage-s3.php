<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AWS Credentials
    |--------------------------------------------------------------------------
    |
    | Your AWS credentials for accessing S3. These can be set via environment
    | variables or directly in this config file.
    |
    */
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS Region
    |--------------------------------------------------------------------------
    |
    | The AWS region where your S3 bucket is located.
    |
    */
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),

    /*
    |--------------------------------------------------------------------------
    | S3 Bucket
    |--------------------------------------------------------------------------
    |
    | The default S3 bucket to use for storage operations.
    |
    */
    'bucket' => env('AWS_BUCKET'),

    /*
    |--------------------------------------------------------------------------
    | CloudFront CDN
    |--------------------------------------------------------------------------
    |
    | CloudFront distribution settings for CDN delivery.
    |
    */
    'cloudfront' => [
        'enabled' => env('AWS_CLOUDFRONT_ENABLED', false),
        'url' => env('AWS_CLOUDFRONT_URL'),
        'key_pair_id' => env('AWS_CLOUDFRONT_KEY_PAIR_ID'),
        'private_key_path' => env('AWS_CLOUDFRONT_PRIVATE_KEY_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Signed URLs
    |--------------------------------------------------------------------------
    |
    | Default settings for generating signed URLs.
    |
    */
    'signed_urls' => [
        'expiration' => env('AWS_SIGNED_URL_EXPIRATION', '+60 minutes'),
        'use_cloudfront' => env('AWS_SIGNED_URL_USE_CLOUDFRONT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for file uploads.
    |
    */
    'upload' => [
        'visibility' => env('AWS_DEFAULT_VISIBILITY', 'private'),
        'acl' => env('AWS_DEFAULT_ACL', 'private'),
        'cache_control' => env('AWS_CACHE_CONTROL', 'max-age=31536000'),
        'multipart_threshold' => env('AWS_MULTIPART_THRESHOLD', 104857600), // 100MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Path Prefixes
    |--------------------------------------------------------------------------
    |
    | Default path prefixes for organizing files by type.
    |
    */
    'paths' => [
        'images' => env('AWS_PATH_IMAGES', 'images'),
        'documents' => env('AWS_PATH_DOCUMENTS', 'documents'),
        'videos' => env('AWS_PATH_VIDEOS', 'videos'),
        'audio' => env('AWS_PATH_AUDIO', 'audio'),
        'temp' => env('AWS_PATH_TEMP', 'temp'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | Allowed MIME types for uploads by category.
    |
    */
    'allowed_types' => [
        'images' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/avif',
            'image/svg+xml',
        ],
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ],
        'videos' => [
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/webm',
            'video/x-msvideo',
        ],
        'audio' => [
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/ogg',
            'audio/webm',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Size Limits
    |--------------------------------------------------------------------------
    |
    | Maximum file sizes in bytes by category.
    |
    */
    'max_size' => [
        'images' => env('AWS_MAX_SIZE_IMAGES', 10485760),      // 10MB
        'documents' => env('AWS_MAX_SIZE_DOCUMENTS', 52428800), // 50MB
        'videos' => env('AWS_MAX_SIZE_VIDEOS', 524288000),      // 500MB
        'audio' => env('AWS_MAX_SIZE_AUDIO', 104857600),        // 100MB
        'default' => env('AWS_MAX_SIZE_DEFAULT', 104857600),    // 100MB
    ],
];
