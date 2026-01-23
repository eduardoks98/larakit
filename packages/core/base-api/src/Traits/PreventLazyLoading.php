<?php

namespace Eduardoks98\BaseApi\Traits;

use Illuminate\Database\Eloquent\Model;

trait PreventLazyLoading
{
    /**
     * Enable lazy loading prevention (Laravel 11/12 feature).
     *
     * @return void
     */
    protected function enableLazyLoadingPrevention(): void
    {
        if (class_exists(Model::class) && method_exists(Model::class, 'preventLazyLoading')) {
            Model::preventLazyLoading(!app()->isProduction());
        }
    }

    /**
     * Disable lazy loading prevention.
     *
     * @return void
     */
    protected function disableLazyLoadingPrevention(): void
    {
        if (class_exists(Model::class) && method_exists(Model::class, 'preventLazyLoading')) {
            Model::preventLazyLoading(false);
        }
    }
}
