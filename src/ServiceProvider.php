<?php

namespace BlackSpot\ServiceIntegrationsContainer;

use Illuminate\Support\ServiceProvider;

class ServiceProvider extends ServiceProvider
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

    protected function registerPublishables()
    {
        $this->publishes([
            __DIR__.'/../config/self::PACKAGE_NAME.php' => base_path('config/'.(self::PACKAGE_NAME).'.php'),
        ], [self::PACKAGE_NAME, self::PACKAGE_NAME.':config']);

        $this->publishes([
            __DIR__.'/../database/migrations' => base_path('database/migrations')
        ], [self::PACKAGE_NAME, self::PACKAGE_NAME.':migrations']);
    }
}
