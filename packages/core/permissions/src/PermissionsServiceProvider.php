<?php

namespace Eduardoks98\Permissions;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\Permissions\Services\PermissionService;
use Eduardoks98\Permissions\Commands\SyncPermissionsCommand;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permissions.php', 'permissions');

        $this->app->singleton(PermissionService::class, function ($app) {
            return new PermissionService();
        });

        $this->app->alias(PermissionService::class, 'permissions');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/permissions.php' => config_path('permissions.php'),
        ], 'permissions-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'permissions-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->registerMiddleware();
        $this->registerCommands();
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware(
            'permission',
            \Eduardoks98\Permissions\Http\Middleware\CheckPermission::class
        );

        $this->app['router']->aliasMiddleware(
            'any-permission',
            \Eduardoks98\Permissions\Http\Middleware\CheckAnyPermission::class
        );
    }

    /**
     * Register artisan commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPermissionsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PermissionService::class,
            'permissions',
        ];
    }
}
