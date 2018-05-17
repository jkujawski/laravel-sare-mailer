<?php

namespace Jkujawski\SareMailer;

use Illuminate\Mail\MailServiceProvider;
/**
 * Class ServiceProvider
 * 
 * @package Jkujawski\SareMailer
 */
class ServiceProvider extends MailServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/saremailer.php' => config_path('saremailer.php'),
        ]);
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/saremailer.php', 'saremailer');

        $this->registerSwiftTransport();

        parent::register();
    }

    public function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new TransportManager($app);
        });
    }

}
