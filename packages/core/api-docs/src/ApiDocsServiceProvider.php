<?php

namespace Eduardoks98\ApiDocs;

use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\InfoObject;

class ApiDocsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/scramble.php', 'scramble');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/scramble.php' => config_path('scramble.php'),
        ], 'config');

        // Configure Scramble
        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                Scramble::securityScheme('sanctum', [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Laravel Sanctum token authentication',
                ])
            );

            $openApi->servers = [
                ['url' => config('app.url') . '/api/v1', 'description' => 'API Server'],
            ];
        });
    }
}
