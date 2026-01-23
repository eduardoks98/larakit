<?php

namespace Eduardoks98\Reverb;

use Illuminate\Support\ServiceProvider;

class ReverbServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/reverb.php', 'reverb');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/reverb.php' => config_path('reverb.php'),
        ], 'config');

        // Publish example broadcasting channels
        $this->publishes([
            __DIR__ . '/../routes/channels.php' => base_path('routes/channels.php'),
        ], 'routes');
    }
}
