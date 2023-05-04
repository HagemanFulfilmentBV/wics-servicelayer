<?php

namespace Hageman\Wics\ServiceLayer;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/wics.php', 'wics');
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        if($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/wics.php' => config_path('wics.php'),
            ], 'wics-config');
        }
    }
}
