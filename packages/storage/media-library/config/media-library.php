<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The storage disk to use for media files. Use 's3' for cloud storage
    | or 'local' for local filesystem.
    |
    */
    'disk' => env('MEDIA_LIBRARY_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | The image processing driver to use: 'gd' or 'imagick'.
    | Imagick provides better quality but requires the extension.
    |
    */
    'image_driver' => env('MEDIA_LIBRARY_IMAGE_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Image Conversions
    |--------------------------------------------------------------------------
    |
    | Default image conversion sizes. Each conversion will be generated
    | automatically when an image is uploaded.
    |
    */
    'conversions' => [
        'thumb' => [
            'width' => 150,
            'height' => 150,
            'fit' => 'crop', // crop, contain, fill, stretch
            'quality' => 80,
        ],
        'small' => [
            'width' => 320,
            'height' => null, // null = maintain aspect ratio
            'fit' => 'contain',
            'quality' => 85,
        ],
        'medium' => [
            'width' => 640,
            'height' => null,
            'fit' => 'contain',
            'quality' => 85,
        ],
        'large' => [
            'width' => 1024,
            'height' => null,
            'fit' => 'contain',
            'quality' => 85,
        ],
        'xl' => [
            'width' => 1920,
            'height' => null,
            'fit' => 'contain',
            'quality' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Formats
    |--------------------------------------------------------------------------
    |
    | Output formats for image conversions.
    |
    */
    'formats' => [
        'default' => env('MEDIA_LIBRARY_DEFAULT_FORMAT', 'webp'),
        'fallback' => env('MEDIA_LIBRARY_FALLBACK_FORMAT', 'jpg'),
        'preserve_original' => env('MEDIA_LIBRARY_PRESERVE_ORIGINAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Optimization
    |--------------------------------------------------------------------------
    |
    | Image optimization settings.
    |
    */
    'optimization' => [
        'enabled' => env('MEDIA_LIBRARY_OPTIMIZE', true),
        'quality' => [
            'jpg' => 85,
            'png' => 85,
            'webp' => 85,
            'avif' => 80,
        ],
        'strip_metadata' => env('MEDIA_LIBRARY_STRIP_METADATA', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark
    |--------------------------------------------------------------------------
    |
    | Default watermark settings for images.
    |
    */
    'watermark' => [
        'enabled' => env('MEDIA_LIBRARY_WATERMARK_ENABLED', false),
        'path' => env('MEDIA_LIBRARY_WATERMARK_PATH'),
        'position' => env('MEDIA_LIBRARY_WATERMARK_POSITION', 'bottom-right'),
        'opacity' => env('MEDIA_LIBRARY_WATERMARK_OPACITY', 50),
        'padding' => env('MEDIA_LIBRARY_WATERMARK_PADDING', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Settings
    |--------------------------------------------------------------------------
    |
    | Video processing settings (requires FFmpeg).
    |
    */
    'video' => [
        'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
        'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
        'thumbnail_time' => env('MEDIA_LIBRARY_VIDEO_THUMBNAIL_TIME', '00:00:01'),
        'thumbnail_width' => env('MEDIA_LIBRARY_VIDEO_THUMBNAIL_WIDTH', 640),
    ],

    /*
    |--------------------------------------------------------------------------
    | Collections
    |--------------------------------------------------------------------------
    |
    | Define media collections with specific settings.
    |
    */
    'collections' => [
        'default' => [
            'disk' => null, // Use default disk
            'conversions' => ['thumb', 'medium'], // Conversions to generate
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'accepted_mimes' => ['image/*', 'video/*', 'application/pdf'],
        ],
        'avatars' => [
            'disk' => null,
            'conversions' => ['thumb', 'small'],
            'max_file_size' => 2 * 1024 * 1024, // 2MB
            'accepted_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'single' => true, // Only one file per model
        ],
        'documents' => [
            'disk' => null,
            'conversions' => [],
            'max_file_size' => 50 * 1024 * 1024, // 50MB
            'accepted_mimes' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.*'],
        ],
        'gallery' => [
            'disk' => null,
            'conversions' => ['thumb', 'medium', 'large'],
            'max_file_size' => 20 * 1024 * 1024, // 20MB
            'accepted_mimes' => ['image/*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Queue settings for background processing.
    |
    */
    'queue' => [
        'enabled' => env('MEDIA_LIBRARY_QUEUE_ENABLED', true),
        'connection' => env('MEDIA_LIBRARY_QUEUE_CONNECTION', 'default'),
        'queue' => env('MEDIA_LIBRARY_QUEUE_NAME', 'media'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    |
    | Automatic cleanup settings.
    |
    */
    'cleanup' => [
        'delete_conversions_on_delete' => true,
        'delete_original_on_delete' => true,
    ],
];
