<?php

namespace Eduardoks98\MicrosoftAuth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;
use Eduardoks98\MicrosoftAuth\Http\Middleware\EnsureMicrosoftTokenIsValid;

class MicrosoftAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/microsoft.php', 'microsoft');

        // Register services as singletons
        $this->app->singleton(MicrosoftAuthService::class, function ($app) {
            return new MicrosoftAuthService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/microsoft.php' => config_path('microsoft.php'),
        ], 'microsoft-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'microsoft-migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('microsoft.token', EnsureMicrosoftTokenIsValid::class);

        // Register routes
        $this->registerRoutes();

        // Register model relationship
        $this->registerModelRelationship();
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register microsoftUser relationship on User model.
     */
    protected function registerModelRelationship(): void
    {
        $userModel = config('microsoft.user_model', 'App\\Models\\User');

        if (class_exists($userModel)) {
            // Add hasOne relationship to User model dynamically
            $userModel::resolveRelationUsing('microsoftUser', function ($userModel) {
                return $userModel->hasOne(
                    \Eduardoks98\MicrosoftAuth\Models\MicrosoftUser::class,
                    'user_id'
                );
            });
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            MicrosoftAuthService::class,
        ];
    }
}
