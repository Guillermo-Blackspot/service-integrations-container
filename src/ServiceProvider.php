<?php

namespace BlackSpot\ServiceIntegrationsContainer;

use Illuminate\Support\ServiceProvider as LaravelProvider;

class ServiceProvider extends LaravelProvider
{
    public const PACKAGE_NAME = 'service-integrations-container';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerPublishables();
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/'.self::PACKAGE_NAME.'.php', self::PACKAGE_NAME);
    }

    public static function getFromConfig($keys, $default = null)
    {
        return config(self::PACKAGE_NAME.'.'.$keys, $default);
    }

    protected function registerPublishables()
    {
        $this->publishes([
            __DIR__.'/../config/'.self::PACKAGE_NAME.'.php' => base_path('config/'.(self::PACKAGE_NAME).'.php'),
        ], [self::PACKAGE_NAME, self::PACKAGE_NAME.':config']);

        $this->publishes([
            __DIR__.'/../database/migrations' => base_path('database/migrations')
        ], [self::PACKAGE_NAME, self::PACKAGE_NAME.':migrations']);
    }
}
