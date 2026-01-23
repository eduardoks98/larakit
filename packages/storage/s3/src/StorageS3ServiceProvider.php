<?php

namespace Eduardoks98\StorageS3;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\StorageS3\Services\S3Service;

/**
 * Storage S3 Service Provider
 */
class StorageS3ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/storage-s3.php' => config_path('storage-s3.php'),
        ], 'config');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/storage-s3.php',
            'storage-s3'
        );

        // Register S3Service singleton
        $this->app->singleton(S3Service::class, function ($app) {
            return new S3Service();
        });

        // Register alias
        $this->app->alias(S3Service::class, 's3-storage');
    }
}
