<?php

namespace LaravelSendgridWebhooks;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sendgridwebhooks.php', 'sendgridwebhooks');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/sendgridwebhooks.php' => config_path('sendgridwebhooks.php')
        ]);
        $this->loadRoutesFrom(__DIR__ . '/../routes/sendgridwebhooks.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
