<?php

namespace Eduardoks98\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\MediaLibrary\Services\MediaService;
use Eduardoks98\MediaLibrary\Services\ImageService;

/**
 * Media Library Service Provider
 */
class MediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/media-library.php' => config_path('media-library.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/media-library.php',
            'media-library'
        );

        // Register ImageService singleton
        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService();
        });

        // Register MediaService singleton
        $this->app->singleton(MediaService::class, function ($app) {
            return new MediaService(
                $app->make(\Eduardoks98\StorageS3\Services\S3Service::class),
                $app->make(ImageService::class)
            );
        });

        // Register aliases
        $this->app->alias(MediaService::class, 'media-library');
        $this->app->alias(ImageService::class, 'image-service');
    }
}
